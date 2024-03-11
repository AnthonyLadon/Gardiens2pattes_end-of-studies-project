<?php

namespace App\Controller;

use Stripe\StripeClient;
use App\Entity\Reservation;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaimentsController extends AbstractController
{
    /**
     * @Route("/paiement/{id_reservation}", name="reservation_paiement")
     */
    public function paiementReservation(EntityManagerInterface $entityManager, $id_reservation, Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id_reservation);
        $gardien = $reservation->getGardien();
        $prenom = $gardien->getUtilisateur()->getPrenom();
        $nom = $gardien->getUtilisateur()->getNom();
        $iban = $gardien->getIban();

        // récuperer le stripe-token
        if ($request->isMethod('POST')) {
            $token = $request->request->get('stripeToken');

            // Clé privée récupérée depuis paramétres -> config/services.yaml
            $secretKey = $this->getParameter('stripe_secret_key');

            $stripe = new StripeClient(
                // Clé privée récupérée depuis paramétres -> config/services.yaml
                $secretKey
              );
              \Stripe\Stripe::setApiKey($secretKey);

              // Si le gardien n'a pas encore de compte Stripe, on lui en crée un
              if($gardien->getStripeAccountId() == null){

                //* En production on créera un compte Stripe pour chaque gardien

                 $stripe = new \Stripe\StripeClient($secretKey);
                 $response = $stripe->accounts->create([
                    'type' => 'standard' //! En test on utilise le type standard
                    ]);
                //! En production on utilisera le type custom 
                //! (parametrage supplémentaire nécessaire sur le compte Stripe)
                
                //     'type' => 'custom', 
                //     'country' => 'BE',
                //     'email' => $gardien->getUtilisateur()->getEmail(),
                //     'business_type' => 'individual',
                //     'individual' => [
                //         'first_name' => 'John', // Prénom du prestataire
                //         'last_name' => 'Doe', // Nom de famille du prestataire
                //     ],// Type d'entreprise (individuel)
                //     'capabilities' => [
                //         'card_payments' => ['requested' => true],
                //         'transfers' => ['requested' => true],
                //     ],
                //     'external_account' => [
                //         'object' => 'bank_account',
                //         'country' => 'BE',
                //         'currency' => 'eur',
                //         'account_number' => $iban,
                //         'account_holder_name' => $prenom.' '.$nom,
                //     ]
                // ]);
                $accountId = $response->id;
                $gardien->setStripeAccountId($accountId);
                $entityManager->persist($gardien);
              }

              $accountId = $gardien->getStripeAccountId();

              $connectedAccount = $stripe->accounts->retrieve(
                $accountId,
                []
            );


                $stripe->charges->create([
                    'amount' => $reservation->getPrixTotal() * 100,
                    'currency' => 'eur',
                    'source' => $token,
                    'description' => 'Paiement réservation gardiennage',
                    'destination' => [
                        'account' => $gardien->getStripeAccountId(),
                        //! informations ajoutées pour le compte custom (en production)
                        // 'external_account' => [
                        //     'object' => 'bank_account',
                        //     'country' => 'BE',
                        //     'currency' => 'eur',
                        //     'account_number' => $iban,
                        //     'account_holder_name' => $prenom.' '.$nom,
                        // ],
                    ],
                ]);
        

            $reservation->setPaiementOk(true);
            $reservation->setDatePaiement(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $entityManager->persist($reservation);
            $entityManager->flush();

            // envoi d'un mail de confirmation au maitre
            $email = (new Email())
            ->from('info@gardien2pattes.be')
            ->to($reservation->getMaitre()->getUtilisateur()->getEmail())
            ->subject('Confirmation de votre réservation')
            ->html($this->renderView('emails/reservations/confirmationPaiementReservationMaitre.html.twig', [
                'reservation' => $reservation,
            ]));

            $mailer->send($email);

            // envoi d'un mail de confirmation au gardien
            $email = (new Email())
            ->from('info@gardien2pattes.be')
            ->to($reservation->getGardien()->getUtilisateur()->getEmail())
            ->subject('Confirmation de paiement gardiennage')
            ->html($this->renderView('emails/reservations/confirmationPaiementReservationGardien.html.twig', [
                'reservation' => $reservation,
            ]));

            $mailer->send($email);

            $msg = $translator->trans('Paiement effectué avec succès !');
            $this->addFlash('success',$msg);

            return $this->redirectToRoute('home');
        }


        return $this->render('paiments/paiementReservation.html.twig', [
            'reservation' => $reservation,
            // Clé publique récupérée depuis paramétres -> config/services.yaml
            'stripe_public_key' => $this->getParameter('stripe_public_key'),
        ]);
    }

}

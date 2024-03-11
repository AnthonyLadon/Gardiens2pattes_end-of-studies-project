<?php

namespace App\Controller;

use App\Entity\Maitres;
use App\Entity\Reservation;
use App\Entity\Prestataires;
use App\Form\ReservationType;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationsController extends AbstractController
{


// **********************************************************************************
// *  GARDIEN
// **********************************************************************************

    // ------------------------------------------------------------------------------------
    // Page gestion des reservations (passées, en cours, à venir) -> profil de Prestataire
    // ------------------------------------------------------------------------------------
    /**
     * @Route("/profil/gardien/reservations/{id}",name="reservations_gardien")
     */
    public function reservationsGardien(EntityManagerInterface $entityManager, $id): Response
    {
        $repository = $entityManager->getRepository(Prestataires::class);
        $prestataire = $repository->findOneById($id);

        $reservations = $entityManager->getRepository(Reservation::class)->findBy(['gardien' => $prestataire]);

        return $this->render('reservations/gardienReservations.html.twig', [
            'reservations' => $reservations,
            'id' => $id,
            "prestataire" => $prestataire
        ]);
    }

    // ------------------------------------------------------------------------------------
    // Page detail reservation -> profil de Prestataire
    // ------------------------------------------------------------------------------------
    /**
     * @Route("/profil/gardien/reservations/{id}/detail/{idReservation}",name="reservations_gardien_detail")
     */
    public function reservationsGardienDetail(EntityManagerInterface $entityManager, $id, $idReservation): Response
    {
        $repository = $entityManager->getRepository(Prestataires::class);
        $prestataire = $repository->findOneById($id);

        $repository = $entityManager->getRepository(Reservation::class);
        $reservation = $repository->findOneById($idReservation);

        return $this->render('reservations/gardienReservationDetailProfil.html.twig', [
            'reservation' => $reservation,
            'id' => $id,
            "prestataire" => $prestataire
        ]);
    }


    // -----------------------------------------------------------------------------------------------
    // Passage de la reservation en statut "acceptée" ou anuler acceptation -> profil de Prestataire
    // -----------------------------------------------------------------------------------------------
    /**
     * @Route("/profil/gardien/reservations/accepter/{idReservation}",name="reservations_gardien_accepter")
     */
    public function reservationsGardienAccepter(EntityManagerInterface $entityManager, $idReservation, MailerInterface $mailer, TranslatorInterface $translator): Response
    {

        $repository = $entityManager->getRepository(Reservation::class);
        $reservation = $repository->findOneById($idReservation);

        // calculer nombre de jour de la reservation
        $dateDebut = $reservation->getDateDebut();
        $dateFin = $reservation->getDateFin();
        $nbJours = $dateDebut->diff($dateFin)->format('%a');



        if($reservation->isValidationPrestataire() == false){
            $reservation->setValidationPrestataire(true);
            $entityManager->persist($reservation);

            $msg = $translator->trans('La réservation a bien été acceptée');
            $this->addFlash('success', $msg);

            // envoyer mail de confirmation de reservation
            $email = (new Email())
            ->from('info@gardien2pattes.be')
            ->to($reservation->getMaitre()->getUtilisateur()->getEmail())
            ->subject('Gardiennage de votre animal accepté')
            ->html($this->renderView('emails/reservations/confirmationReservation.html.twig', [
                'reservation' => $reservation,
                'nbJours' => $nbJours,
            ]));
            $mailer->send($email);}

            else{
                $reservation->setValidationPrestataire(false);
                $entityManager->persist($reservation);

                $msg = $translator->trans('La réservation a bien été annulée');
                $this->addFlash('notice', $msg);

                // envoyer mail d'annulation' de reservation
                $email = (new Email())
                ->from('info@gardien2pattes.be')
                ->to($reservation->getMaitre()->getUtilisateur()->getEmail())
                ->subject('Annulation de votre réservation')
                ->html($this->renderView('emails/reservations/annulationReservation.html.twig', [
                    'reservation' => $reservation,
                ]));
                $mailer->send($email);
            }

        $entityManager->flush();

        return $this->redirectToRoute('reservations_gardien_detail', [
            'idReservation' => $idReservation,
            'id' => $reservation->getGardien()->getId()
        ]);
    }



// **********************************************************************************
// *  MAITRE
// **********************************************************************************


    // ----------------------------------------------------------------------------------
    // Nouvelle demande reservation (si ROLE_MAITRE) -> Affichage formulaire & envoi mail
    // ----------------------------------------------------------------------------------
    /**
     * @Route("/detail-gardien/reservation/{id}",name="reservation")
     */
    public function reservation($id, EntityManagerInterface $entityManager, MailerInterface $mailer, Request $request, TranslatorInterface $translator): Response
    {
        $repository = $entityManager->getRepository(Prestataires::class);
        $prestataire = $repository->find($id);

        $maitre = $this->getUser()->getMaitre();

        $reservation = new Reservation();
        // passage en option du maitre de l'animal ($maitre)
        $form = $this->createForm(ReservationType::class, $reservation, [
            'maitre' => $maitre,
            'prestataire' => $prestataire
        ]);
        $form->handleRequest($request);

        // si le formulaire est soumis et valide et qu'un animal est sélectionné
        if ($form->isSubmitted() && $form->isValid()) {

            $dateDebut = $form->get('dateDebut')->getData();
            $dateFin = $form->get('dateFin')->getData();
            $nbJours = $dateDebut->diff($dateFin)->format('%a');

            // si aucun animal n'est sélectionné
            if($form->get('animal')->getData() == null){
                $this->addFlash('error', 'Attention! Vous devez sélectionner un animal');
                return $this->redirectToRoute('reservation', ['id' => $id]);
            }

            // si la date de début est supérieure à la date de fin
            if ($dateDebut > $dateFin) {
                $this->addFlash('error', 'Attention la date de début doit être inférieure à la date de fin');
                return $this->redirectToRoute('reservation', ['id' => $id]);
            }

            // si la date de début est inférieure à la date du jour
            if ($dateDebut < new \DateTime('now', new \DateTimeZone('Europe/Paris'))) {
                $this->addFlash('error', 'La date de début doit être plus tard qu\'aujourd\'hui');
                return $this->redirectToRoute('reservation', ['id' => $id]);
            }


            $prixTotalPassages = 0;
            // si passage à domicile choisi : prix = (prix du passage X le nb de passage par jour) x nb de jours
            if ($form->getData()->getNbPassages() > 0 and $form->getData()->isHebergement() == false) {
                $PrixPassageParJour = $prestataire->getTarifDeplacement();
                $nbPassagesParJour = $form->getData()->getNbPassages();
                $prixTotalPassages = ($PrixPassageParJour * $nbPassagesParJour) * $nbJours;
            }

            $prixHebergement = 0;
            // si l'herbegement a été choisi et que le nombre de passage vaut bien 0
            // -> prix = prix de l'hebergement X le nb de jours
            if ($form->getData()->getNbPassages() == 0 and $form->getData()->isHebergement() == true) {
                $PrixHebergementParJour = $prestataire->getTarif();
                $prixHebergement = $PrixHebergementParJour * $nbJours;
            }

            // si passages à domicile et hébergement ont été choisi
            if ($form->getData()->getNbPassages() > 0 and $form->getData()->isHebergement() == true) {
                $this->addFlash('error', 'Vous ne pouvez pas choisir l\'hébergement et les passages à domicile en même temps');
                return $this->redirectToRoute('reservation', ['id' => $id]);
            }

            // si promenades et hébergement ont été choisi
            if ($form->getData()->getNbPromenades() > 0 and $form->getData()->isHebergement() == true) {
                $this->addFlash('error', 'Vous devez choisir soit l\'hébergement soit les passages à domicile');
                return $this->redirectToRoute('reservation', ['id' => $id]);
            }

            // si promenades et passages à domicile ont été choisi
            if ($form->getData()->getNbPromenades() > 0 and $form->getData()->getNbPassages() > 0) {
                $this->addFlash('error', 'Vous ne pouvez pas choisir les passages à domicile et les promenades en même temps');
                return $this->redirectToRoute('reservation', ['id' => $id]);
            }

            // si aucun service n'a été choisi
            if($prixTotalPassages == 0 and $form->getData()->isHebergement() == false){
                $this->addFlash('error', 'Vous devez choisir au moins un service: passage à domicile, promenades ou hébergement');
                return $this->redirectToRoute('reservation', ['id' => $id]);
            }


            $prixTotal = $prixHebergement + $prixTotalPassages;
            $reservation->setPrixTotal($prixTotal);
            $reservation->setGardien($prestataire);
            $reservation->setIsSentEnquete(false);
            $reservation->setMaitre($this->getUser()->getMaitre());
            $reservation->setValidationPrestataire(false);
            $reservation->setPaiementOk(false);

            $entityManager->persist($reservation);
            $entityManager->flush();


            // envoi d'un mail récapitulatif au prestataire
            $email = (new Email())
                ->from('info@gardien2pattes.be')
                ->to($prestataire->getUtilisateur()->getEmail())
                ->subject('Nouvelle demande de réservation')
                ->html($this->renderView('emails/reservations/nouvelleReservation.html.twig', [
                    'maitre' => $maitre,
                    'reservation' => $reservation,
                    'prestataire' => $prestataire,
                    'nbJours' => $nbJours,
                    'id' => $id,
                ]));

            $mailer->send($email);

            $msg = $translator->trans('Votre demande de réservation a bien été prise en compte');
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('detail_gardien', ['id' => $id]);
        }

        return $this->render('reservations/maitreReservationForm.html.twig', [
            'form' => $form->createView(),
            'prestataire' => $prestataire,
            'maitre' => $maitre,
        ]);
    }
    

    // -----------------------------------------------------------------------
    // Afficher page reservations Maitre
    // -----------------------------------------------------------------------
    /**
     * @Route("/profil/maitre/reservations/{id}", name="reservations_maitre")
     */
    public function reservationsMaitre($id, EntityManagerInterface $entityManager): Response
    {
      $repository = $entityManager->getRepository(Maitres::class);
      $maitre = $repository->find($id);

      $repositoryReservations = $entityManager->getRepository(Reservation::class);
      $reservations = $repositoryReservations->findBy(['maitre' => $maitre]);

      return $this->render('reservations/maitreReservations.html.twig', [
          'reservations' => $reservations,
          'id' => $id,
          'maitre' => $maitre
      ]);
    }



    // ----------------------------------------------------------------------------------
    // Afficher les detail de la reservation (si ROLE_MAITRE)
    // ----------------------------------------------------------------------------------
    /**
     * @Route("/profil/maitre/reservation/{id}/{idReservation}",name="detail_reservation_maitre")
     */
    public function detailReservationMaitre($idReservation, $id, EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Reservation::class);
        $reservation = $repository->find($idReservation);

        $repositoryMaitre = $entityManager->getRepository(Maitres::class);
        $maitre = $repositoryMaitre->find($id);


        return $this->render('reservations/maitreReservationDetail.html.twig', [
            'reservation' => $reservation,
            'maitre' => $maitre,
        ]);
    }
    

}

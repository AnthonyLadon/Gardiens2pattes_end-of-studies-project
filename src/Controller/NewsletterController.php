<?php

namespace App\Controller;

use App\Entity\Maitres;
use App\Entity\Newsletters;
use App\Form\NewslettersType;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Message\SendNewsletterEmail;

class NewsletterController extends AbstractController
{

    // voir les newsletters
    /**
     * @Route("/newsletters", name="newsletters")
     */
    public function index(EntityManagerInterface $entityManagerInterface): Response
    {
        $repository = $entityManagerInterface->getRepository(Newsletters::class);
        $newsletters = $repository->findAll();

        return $this->render('admin_crud/newsletters/newsletters.html.twig', [
            'newsletters' => $newsletters,
        ]);

    }

    // detail newsletter
    /**
     * @Route("/newsletters/{id}", name="detail_newsletter")
     */
    public function newsletter(EntityManagerInterface $entityManagerInterface, $id): Response
    {
        $repository = $entityManagerInterface->getRepository(Newsletters::class);
        $newsletter = $repository->find($id);

        return $this->render('admin_crud/newsletters/detail.html.twig', [
            'newsletter' => $newsletter,
        ]);

    }

    // envoi newsletter par email a tous les maitres abonnés
    /**
     * @Route("/newsletters/{id}/envoyer", name="envoyer_newsletter")
     */
    public function envoyerNewsletter($id, EntityManagerInterface $entityManager, MailerInterface $mailer, TranslatorInterface $translator):Response
    {
        $repository = $entityManager->getRepository(Newsletters::class);
        $newsletter = $repository->find($id);

        // récupérer tous les maitres abonnés à la newsletter
        $repository = $entityManager->getRepository(Maitres::class);
        $maitres = $repository->findBy(['newsletter' => true]);

        foreach ($maitres as $maitre) {
            $email = $maitre->getUtilisateur()->getEmail();

            // envoi d'un mail récapitulatif au prestataire
            $email = (new Email())
                ->from('newsletter@gardien2pattes.be')
                ->to($email)
                ->subject($newsletter->getNom())
                ->html($this->renderView('emails/newsletter.html.twig', [
                    'newsletter' => $newsletter,
                    'maitre' => $maitre
                ]));

            $mailer->send($email);
        }

        $msg = $translator->trans('Newsletter envoyée avec succès !');
        $this->addFlash('success',$msg);

        return $this->redirectToRoute('newsletters');
    }



    //creer nouvelle newsletter
    /**
     * @Route("/newsletter_nouvelle", name="nouvelle_newsletter")
     */
    public function creerNewsletter(EntityManagerInterface $entityManagerInterface, Request $request, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(NewslettersType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newsletter = new Newsletters();
            $newsletter->setContenu($form->get('contenu')->getData());
            $newsletter->setNom($form->get('nom')->getData());
            $newsletter->isIsSent(false);

            $entityManagerInterface->persist($newsletter);
            $entityManagerInterface->flush();

            $msg = $translator->trans('Newsletter créée avec succès !');
            $this->addFlash('success',$msg);

            return $this->redirectToRoute('newsletters');
        }
    
        return $this->render('admin_crud/newsletters/new_newsletter.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //modifier newsletter
    /**
     * @Route("/newsletters/{id}/modifier", name="modifier_newsletter")
     */
    public function modifierNewsletter(EntityManagerInterface $entityManagerInterface,Request $request, $id): Response
    {
        $repository = $entityManagerInterface->getRepository(Newsletters::class);
        $newsletter = $repository->find($id);

        $form = $this->createForm(NewslettersType::class, $newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newsletter = $form->getData();
            
            $entityManagerInterface->persist($newsletter);
            $entityManagerInterface->flush();

            $this->addFlash('success', 'Newsletter modifiée avec succès !');

            return $this->redirectToRoute('detail_newsletter', 
            ['id' => $newsletter->getId()
            ]);
        }
    
        return $this->render('admin_crud/newsletters/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    //suppirmer une newsletter
    /**
     * @Route("/newsletters/{id}/supprimer", name="supprimer_newsletter")
     */
    public function supprimerNewsletter(EntityManagerInterface $entityManagerInterface, $id): Response
    {
        $repository = $entityManagerInterface->getRepository(Newsletters::class);
        $newsletter = $repository->find($id);

        $entityManagerInterface->remove($newsletter);
        $entityManagerInterface->flush();

        $this->addFlash('success', 'Newsletter supprimée avec succès !');
        return $this->redirectToRoute('newsletters');
    }


    // se desabonner de la newsletter (via lien dans email)
    /**
     * @Route("/desabonnement_newsletter/{id}", name="desabonnement_newsletter")
     */
    public function desabonnerNewsletter($id, EntityManagerInterface $entityManagerInterface): Response
    {
        $repository = $entityManagerInterface->getRepository(Maitres::class);
        $maitre = $repository->find($id);

        $maitre->setNewsletter(false);
        $entityManagerInterface->persist($maitre);
        $entityManagerInterface->flush();

        $this->addFlash('success', 'Vous êtes désabonné de la newsletter !');
        return $this->redirectToRoute('home');
    }

}

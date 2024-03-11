<?php

namespace App\Controller;

use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;

class ContactController extends AbstractController
{

    /**
     * @Route("/contact", name="contact")
     */
    public function index(MailerInterface $mailer): Response
    {

        // récupérer les données du formulaire
        if (isset($_POST['objet'])) {
            $objet = $_POST['objet'];
        }else{
            $objet = 'non spécifié';
        }

        if (isset($_POST['message'])) {
            $message = $_POST['message'];
        }else{
            $message = '';
        }

        // si l'ulisateur est loggé, on récupère son nom et son prénom et email
        if ($this->getUser()) {
            $user = $this->getUser();
        }else{ 
            $user = "";
        }

        if (isset($_POST['nonRegistratedUser'])) {
            $nonRegistratedUserMail = $_POST['nonRegistratedUser'];
        }else{
            $nonRegistratedUserMail = null;
        }


        if(isset($_POST['submit'])){
        //envoi d'un mail récapitulatif au prestataire
        $email = (new Email());
        if ($user != "") {
            $email->from($user->getEmail());
        }else{
            $email->from($nonRegistratedUserMail);
        }
        $email->to('admin@gardien2pattes.be')
        ->subject($objet)
        ->html($this->renderView('emails/contact.html.twig', [
            'objet' => $objet,
            'message' => $message,
            'user' => $user,
            'nonRegistratedUserMail' => $nonRegistratedUserMail
        ]));

        $mailer->send($email);

        }

        return $this->render('contact/form.html.twig', [
            'objet' => $objet,
            'message' => $message,
            'user' => $user,
            'nonRegistratedUserMail' => $nonRegistratedUserMail
        ]);

    }

}

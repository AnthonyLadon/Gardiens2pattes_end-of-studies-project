<?php

namespace App\Controller;

use DateTimeZone;
use App\Entity\Messages;
use App\Form\MessageType;
use App\Entity\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessagesController extends AbstractController
{
    // ----------------------------------------------------------------------------------
    // Affichage boite de reception des messages, id utilisateur (receiver) en paramêtre
    // ----------------------------------------------------------------------------------
    /**
     * @Route("/messages/{id}", name="messages")
     */
    public function messages($id, EntityManagerInterface $entityManager): Response
    {
        $respository = $entityManager->getRepository(Utilisateurs::class);
        $utilisateur = $respository->find($id);

        // récupérer les expediteurs des messages reçus par l'utilisateur connecté
        $respository = $entityManager->getRepository(Messages::class);
        $messages = $respository->findBy(['recepient' => $utilisateur]);

        // trier et compter les messages reçus non lus
        $senders = [];
        $newMessages=[];
        foreach($messages as $message){
            if($message->isIsRead() == false and $message->getSender() != $utilisateur){
                $newMessages[] = $message->getSender();
            }
            if(!in_array($message->getSender(), $senders)){
                $senders[] = $message->getSender();
            }
        }

        // creation d'un tableau associatif avec les id et les pseudos des nouveaux messages
        $newMessages = array_map(function($newMessage){
            return [
                'id' => $newMessage->getId(),
                'pseudo' => $newMessage->getPseudo()
            ];
        }, $newMessages);

        // supprimer les doublons dans le tableau $newMessages et compter les doublons
        $newMessages = array_unique($newMessages, SORT_REGULAR);


        // récupérer les destinataire de messages envoyés par l'utilisateur connecté
        $recepient = [];
        $messagesSent = $respository->findBy(['sender' => $utilisateur]);
        // trier et compter les messages envoyés non lus
        $newMessagesSent = [];
        foreach($messagesSent as $messageSent){
            if($messageSent->isIsRead() == false){
                $newMessagesSent[] = $messageSent;
            }
            if(!in_array($messageSent->getRecepient(), $recepient)){
                $recepient[] = $messageSent->getRecepient();
            }
        }

        $interlocuteurs = array_merge($senders, $recepient);
        // supprimer les doublons dans le tableau $conversations
        $interlocuteurs = array_unique($interlocuteurs, SORT_REGULAR);
        

        return $this->render('messages/boiteDeReception.html.twig', [
            'utilisateur' => $utilisateur,
            'newMessages' => $newMessages,
            'interlocuteurs' => $interlocuteurs,
        ]);
    }



    // ----------------------------------------------------------------------------------
    // Determine le nombre de messages non lus, id utilisateur (receiver) en paramêtre
    // Est utilisé en embed controller dans le template base.html.twig
    // ----------------------------------------------------------------------------------
    /**
     * @Route("/messages/new/{id}", name="messages_new")
     */
    public function messagesNonLus($id, EntityManagerInterface $entityManager): Response
    {
        $respository = $entityManager->getRepository(Utilisateurs::class);
        $utilisateur = $respository->find($id);

        // récupérer les expediteur uniques des messages reçus par l'utilisateur connecté
        $respository = $entityManager->getRepository(Messages::class);
        $messages = $respository->findBy(['recepient' => $utilisateur]);

        $newMessages = 0;

        if ($messages != null){
            $newMessages = [];
            foreach($messages as $message){
                if($message->isIsRead() == false and $message->getSender() != $utilisateur){
                    $newMessages[] = $message->getSender();
                }
            }
            // compter le nombre de $newMessages
            $newMessages = count($newMessages);
        }

        // permet de retourner une réponse sans passer par un template
        $response = new Response();
        $response->setContent($newMessages);
    
        return $response;
    }


    // --------------------------------------------------------------
    // Envoi d'un message (id utilisateur destinataire en paramêtre)
    // --------------------------------------------------------------
    /**
     * @Route("/messages/send/{id}", name="messages_send")
     */
    public function send($id, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $respository = $entityManager->getRepository(Utilisateurs::class);
        $recepient = $respository->find($id);

        // récuperer utilisateur connecté
        $sender = $this->getUser();

        $message = new Messages();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $message->setDateCreation(new \DateTime('now', new DateTimeZone('Europe/Paris')));
            $message->setSender($this->getUser());
            $message->setRecepient($recepient);
            $message->setMessage($message->getMessage());

            $entityManager->persist($message);
            $entityManager->flush();

            $msg = $translator->trans('Votre message a bien été envoyé !');

            $this->addFlash('success',$msg);

            return $this->redirectToRoute('messages_conversation', ['id' => $id]);
        }

        return $this->render('messages/send.html.twig', [
            'form' => $form->createView(),
            'recepient' => $recepient,
            'sender' => $sender
        ]);
    }

    // --------------------------------------------------------------------------
    // Affichage conversation avec un utilisateur (id utilisateur en paramêtre)
    // --------------------------------------------------------------------------
    /**
     * @Route("/messages/conversation/{id}", name="messages_conversation")
     */
    public function conversation($id, EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $respository = $entityManager->getRepository(Utilisateurs::class);
        $utilisateur = $respository->find($id);

        // récuperer l'utilisateur connecté
        $sender = $this->getUser();

        
        $respository = $entityManager->getRepository(Messages::class);
        // récupérer les messages reçus de l'utilisateur dont l'id est passé en paramètre
        $messagesReceived = $respository->findBy(['recepient' => $this->getUser(), 'sender' => $utilisateur], ['dateCreation' => 'DESC']);

        $iSmaitre = false;
        
        // recuperer le sender du premier message reçu & verif si Role_maitre
        if($messagesReceived != null){
        $sender = $messagesReceived[0]->getSender();
        $roles = $sender->getRoles();
            if(in_array('ROLE_MAITRE', $roles)){
                $iSmaitre = true;
            }
        }

        // récupérer les messages reçus par l'utilisateur dont l'id est passé en paramètre et classement par date de création
        $messagesSent = $respository->findBy(['recepient' => $utilisateur, 'sender' => $this->getUser()], ['dateCreation' => 'DESC']);

        // Passer les messages reçu à lu
        foreach($messagesReceived as $message){
            $message->setIsRead(true);
            $entityManager->persist($message);
            $entityManager->flush();
        }

        // Stocker les messages et les messagesSent dans un tableau pour les trier par date de création
        $messages = array_merge($messagesReceived, $messagesSent);
        // classer les messages par date de création
        usort($messages, function($a, $b) {
            return $a->getDateCreation() <=> $b->getDateCreation();
        });


        // Utilise le bundle de pagination => https://github.com/KnpLabs/KnpPaginatorBundle
        $pagination = $paginator->paginate(
            $messages, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            6 // Définition de la limite d'items par page
        );

        // récupération du numéro de la dernière page
        $lastPage = ceil($pagination->getTotalItemCount()/$pagination->getItemNumberPerPage());
        if($lastPage == 0){
            $lastPage = 1;
        }

        // réinitialisation de la pagination avec la dernière page
        $pagination = $paginator->paginate(
            $messages, /* query NOT result */
            $request->query->getInt('page', $lastPage), /*page number*/
            6 // Définition de la limite d'items par page
        );

        return $this->render('messages/conversation.html.twig', [
            'pagination' => $pagination,
            'utilisateur' => $utilisateur,
            'iSmaitre' => $iSmaitre,
            'sender' => $sender
        ]);
    }

}

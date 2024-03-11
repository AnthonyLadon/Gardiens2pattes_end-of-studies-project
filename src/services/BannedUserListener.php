<?php

namespace App\services;

use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


// Listener qui vérifie si l'utilisateur est banni
class BannedUserListener implements EventSubscriberInterface
{

    // On écoute l'évènement d'authentification
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    // On vérifie si l'utilisateur est banni
    public function onAuthenticationSuccess(AuthenticationEvent $event)
    {
        /** @var TokenInterface $token */
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        // Si l'utilisateur est banni, exception levée
        if ($user->isBanni()) {
            throw new DisabledException('Your account has been banned.');
        }
    }
}
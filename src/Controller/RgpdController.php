<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RgpdController extends AbstractController
{

    // Affichage des regles de confidentialité (RGPD)
    /**
     * @route ("/politique_confidentialite", name="rgpd")
     */
    public function index(): Response
    {
        return $this->render('rgpd/index.html.twig', [
        ]);
    }

    // affichage des conditions d'utilisation du site
    /**
     * @route ("/conditions_utilisation", name="conditions")
     */
    public function conditions(): Response
    {
        return $this->render('rgpd/conditions.html.twig', [
        ]);
    }

}

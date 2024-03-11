<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CookiesController extends AbstractController
{

    // affichage de la page 'en savoir plus' sur les cookies
    
    /**
     * @route ("/cookies", name="app_cookies")
     */
    public function index(): Response
    {
        return $this->render('cookies/index.html.twig', [
        ]);
    }
}

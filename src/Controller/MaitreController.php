<?php

namespace App\Controller;

use App\Entity\Maitres;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaitreController extends AbstractController
{
    #[Route('/page_maitre/{id}', name: 'page_maitre')]
    /**
     * @Route("/maitre/{id}",name="maitre_page")
     */
    public function index($id, EntityManagerInterface $entityManagerInterface): Response
    {
        $repository = $entityManagerInterface->getRepository(Maitres::class);
        $maitre = $repository->find($id);

        return $this->render('maitre/page_maitre.html.twig', [
            'maitre' => $maitre,
        ]);
    }
}

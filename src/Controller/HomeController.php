<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Commentaires;
use App\Entity\Prestataires;
use App\Entity\CategoriesAnimaux;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    // -------------------------------------------------------
    // Affichage page home + 4 derniers prestataires inscrits
    // & formulaire de recherche
    // -------------------------------------------------------

    /**
     * @Route("/", name="home")
     */
    public function index(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {

        if (isset($_GET['submit'])) {
            // Récupération des données envoyées en GET par le formulaire + filtrage des données entrées par l'utilisateur
            ($request->query->all()['pseudo']) !== "" ? $pseudo = filter_var(($request->query->all()['pseudo']), FILTER_SANITIZE_SPECIAL_CHARS) : $pseudo = null;
            if (isset($request->query->all()['categories'])) {
                $categories = $request->query->all()['categories'];
            } else {
                $categories = null;
            }
            ($request->query->all()['localite']) !== "" ? $localite = filter_var(($request->query->all()['localite']), FILTER_SANITIZE_SPECIAL_CHARS) : $localite = null;
            ($request->query->all()['commune']) !== "" ? $commune = filter_var(($request->query->all()['commune']), FILTER_SANITIZE_SPECIAL_CHARS) : $commune = null;
            ($request->query->all()['codePostal']) !== "" ? $codePostal = filter_var(($request->query->all()['codePostal']), FILTER_SANITIZE_NUMBER_INT) : $codePostal = null;
            if (isset($request->query->all()['veterinaire'])) {
                $veto = ($request->query->all()['veterinaire']);
            } else {
                $veto = null;
            };
            if (isset($request->query->all()['jardin'])) {
                $jardin = ($request->query->all()['jardin']);
            } else {
                $jardin = null;
            };
            if (isset($request->query->all()['voiture'])) {
                $voiture = ($request->query->all()['voiture']);
            } else {
                $voiture = null;
            };
            if (isset($request->query->all()['gardeDomicile'])) {
                $gardeDomicile = ($request->query->all()['gardeDomicile']);
            } else {
                $gardeDomicile = null;
            };

            // Récupération des données en BDD
            $repositoryPrestataires = $entityManager->getRepository(Prestataires::class);
            $prestataires = $repositoryPrestataires->PrestSearch($pseudo, $categories, $localite, $codePostal, $commune, $veto, $jardin, $voiture, $gardeDomicile);


            $pagination = $paginator->paginate(
                $prestataires,
                $request->query->getInt('page', 1), //numéro de page
                4 // Définition de la limite d'items par page
            );

            // envoi les données reçues par la DB à la vue liste de prestataires
            return $this->render('prestataire/list.html.twig', [
                'pagination' => $pagination,
                'prestataires' => $prestataires
            ]);
        }

        // recupération des catégories d'animaux
        $repositoryCatégoriesAnimaux = $entityManager->getRepository(CategoriesAnimaux::class);
        $categ = $repositoryCatégoriesAnimaux->findAll();

        // Récupération des 4 derniers prestataires inscrits
        $repository = $entityManager->getRepository(Prestataires::class);
        $last4prestataires = $repository->findLastPrestataires();

        // recuperation des commentaires en avant 
        $repository = $entityManager->getRepository(Commentaires::class);
        $commentaires = $repository->findBy(['en_avant' => 1]);

        // récuperation des images du carrousel
        $repository = $entityManager->getRepository(Images::class);
        $imgCarrousel = $repository->findBy(['homeCarrousel' => 1]);


        return $this->render('home/index.html.twig', [
            'last4prestataires' => $last4prestataires,
            'commentaires' => $commentaires,
            'categ' => $categ,
            'imgCarrousel' => $imgCarrousel
        ]);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Commentaires;
use App\Entity\SignalementAbus;
use App\Entity\CategoriesAnimaux;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Admin\UtilisateursCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class AdminController extends AbstractDashboardController
{

    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ){}
        
        
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        //return parent::index();

         // Genere une route pour afficher la page d'administration
         $url = $this->adminUrlGenerator
         ->setController(UtilisateursCrudController::class)
         ->generateUrl();
         return $this->redirect($url);

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Gardiens À Deux Pattes')
            ->setTranslationDomain('admin')
            ->setFaviconPath('img/favicons/favicon.ico');
    }


    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoURL('Accueil', 'fas fa-home', '/');
        yield MenuItem::linkToDashboard('Utilisateurs', 'fa-solid fa-user');
        yield MenuItem::linkToCrud('Categories d\'Animal', 'fa-solid fa-hippo', CategoriesAnimaux::class);
        yield MenuItem::linkToCrud('Commentaires', 'fa-solid fa-comment', Commentaires::class);
        yield MenuItem::linkToCrud('Signalements', 'fa-solid fa-bell',SignalementAbus::class);
        yield MenuItem::linktoURL('Carrousel page d\'accueil', 'fa-regular fa-image', '/carrousel');
        yield MenuItem::linkToURL('Newsletters', 'fa-regular fa-newspaper', '/newsletters');
        yield MenuItem::linkToURL('Modifier mot de passe', 'fa-solid fa-lock', '/reset-password');
    }

}
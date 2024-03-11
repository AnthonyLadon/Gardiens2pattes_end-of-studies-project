<?php

namespace App\Controller;

use App\Entity\Maitres;
use App\Entity\Reservation;
use App\Entity\Commentaires;
use App\Entity\NotesGardien;
use App\Entity\Prestataires;
use App\Entity\Utilisateurs;
use App\Form\CommentaireType;
use App\Entity\Indisponibilites;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PrestataireController extends AbstractController
{
    // ---------------------------------------------------
    // Affichage de tous les Prestataires (+ pagination)
    // ---------------------------------------------------

    /**
     * @Route("/gardiens",name="gardiens_list")
     */
    public function listePrestataires(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator): Response
    {

        $repository = $entityManager->getRepository(Prestataires::class);
        $prestataires = $repository->findAll();
        $favoris = null;

        // récupérer le maitre depuis le user connecté (si connecté & ROLE_MAITRE)
        if($this->getUser() and $this->isGranted('ROLE_MAITRE')){
            $user = $this->getUser();
            $repositoryUser = $entityManager->getRepository(Utilisateurs::class);
            $user = $repositoryUser->find($user);
            $maitre = $user->getMaitre();

            // récupérer les coordonnees gps du maitre
            $lat = $user->getAdresse()->getLatitude();
            $lng = $user->getAdresse()->getLongitude();
    
            // récupérer les favoris du maitre et les stocker dans un talbeau d'id
            $favoris = $maitre->getFavoris();
            $favoris = $favoris->toArray();
            $favoris = array_map(function ($favoris) {
                return $favoris->getPrestataire()->getId();
            }, $favoris);
        }

        // valeur par defaut du rayon de recherche
        $rayon = 100000;

        //******************* Formulaire de recherche de gardien par zone
        if (isset($_GET['submit-rayon'])){
            // Récupération des données envoyées en GET par le formulaire + filtrage des données entrées par l'utilisateur
            ($request->query->all()['rayon'])!== "" ? $rayon = filter_var(($request->query->all()['rayon']), FILTER_SANITIZE_NUMBER_INT) : $rayon = null;
            // On empeche d'entrer un rayon qui vaut zéro
            $rayon == 0 ? $rayon = null : $rayon = $rayon*1000;

            // récuperation de la connexion à la DB 
            $conn = $entityManager->getConnection();
            // requete SQL pour récupérer les adresses dans un rayon de x km
            $sql = "SELECT p.id
            FROM prestataires as p
            INNER JOIN utilisateurs as u ON u.prestataire_id = p.id
            INNER JOIN adresses as ad ON u.adresse_id = ad.id
            WHERE
                ACOS(SIN(RADIANS(:lat)) *
                    SIN(RADIANS(ad.latitude)) +
                    COS(RADIANS(:lat)) *
                    COS(RADIANS(ad.latitude)) *
                    COS(RADIANS(:lng - ad.longitude))
                ) *
                6378137 < :rayon";

                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':lat', $lat);
                $stmt->bindValue(':lng', $lng);
                $stmt->bindValue(':rayon', $rayon);
                $prestatairesId = $stmt->executeQuery();
                $prestatairesId = $prestatairesId->fetchAllAssociative();

                // récupérer les entité prestataires depuis le tableau $prestatairesId
                $prestataires = [];
                foreach ($prestatairesId as $prestataireId) {
                    $prestataires[] = $repository->find($prestataireId['id']);
                }


        // Utilise le bundle de pagination => https://github.com/KnpLabs/KnpPaginatorBundle
        $pagination = $paginator->paginate(
            $prestataires, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            4 // Définition de la limite d'items par page
        );

        $msg = "Liste filtrée par zone en kilométres";
        $this->addFlash('success', $msg);

        return $this->render('prestataire/list.html.twig', [
            'pagination' => $pagination,
            'favoris' => $favoris,
            'prestataires' => $prestataires,
            'rayon' => $rayon/1000,
        ]);
    }else if(isset($_GET['submit-zone-deplacement'])){

        // récuperationd de la connexion à la DB 
        $conn = $entityManager->getConnection();
        // requete SQL pour récupérer les gardiens qui se déplacent dont l'utilisateur est dans la zone de déplacement
        $sql = "SELECT *
        FROM prestataires AS p
        INNER JOIN utilisateurs AS u ON u.prestataire_id = p.id
        INNER JOIN adresses AS ad ON u.adresse_id = ad.id
        WHERE ST_Distance_Sphere(
                ST_GeomFromText(CONCAT('POINT(', ad.longitude, ' ', ad.latitude, ')')),
                ST_GeomFromText(CONCAT('POINT(', :lng, ' ', :lat, ')'))
            ) <= (p.zone_gardiennage) * 1000";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':lng', $lng);
        $stmt->bindValue(':lat', $lat);
        $prestatairesId = $stmt->executeQuery();
        $prestatairesId = $prestatairesId->fetchAllAssociative();

        // récupérer les entité prestataires depuis le tableau $prestatairesId
        $prestataires = [];
        foreach ($prestatairesId as $prestataireId) {
            $prestataires[] = $repository->find($prestataireId['prestataire_id']);
        }

        // Utilise le bundle de pagination => https://github.com/KnpLabs/KnpPaginatorBundle
        $pagination = $paginator->paginate(
        $prestataires, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        4 // Définition de la limite d'items par page
        );

        $msg = "Les gardiens qui se déplacent jusque chez vous ont été affichés.";
        $this->addFlash('success', $msg);
        
        return $this->render('prestataire/list.html.twig', [
            'pagination' => $pagination,
            'favoris' => $favoris,
            'prestataires' => $prestataires,
            'rayon' => $rayon/1000,
        ]);

    }else{
        // Utilise le bundle de pagination => https://github.com/KnpLabs/KnpPaginatorBundle
        $pagination = $paginator->paginate(
            $prestataires, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            4 // Définition de la limite d'items par page
        );

        return $this->render('prestataire/list.html.twig', [
            'pagination' => $pagination,
            'favoris' => $favoris,
            'prestataires' => $prestataires,
            'rayon' => $rayon/10000,
        ]);
    }
}

    // ---------------------------------------------------------------------------------------
    // Affichage de la page detail prestataire & possibilité ajout en favoris si app.user->ROLE_MAITRE
    // Calendrier avec indisponibilités et reservations si app.user->ROLE_MAITRE
    // Affichage des commentaires et possibilité d'ajouter un commentaire si app.user->ROLE_MAITRE
    // Affichage de la note moyenne du gardien
    // récupération coordonnées latitude et longitude du gardien pour affichage de la map
    // ---------------------------------------------------------------------------------------
    
    /**
     * @Route("/detail-gardien/{id}",name="detail_gardien")
     */
    public function detail($id, EntityManagerInterface $entityManager): Response
    {
        
        $repository = $entityManager->getRepository(Prestataires::class);
        $prestataire = $repository->find($id);
        $favoris = null;

        // récupérer l'entité utilisateur liée au prestataire
        $repositoryUser = $entityManager->getRepository(Utilisateurs::class);
        $prestataireUser = $repositoryUser->findOneBy(['prestataire' => $prestataire->getId()]);

        // récupérer les notes que le prestataire a reçu
        $repositoryNote = $entityManager->getRepository(NotesGardien::class);
        $notes = $repositoryNote->findBy(['gardien' => $prestataire]);
        $noteMoyenne = 0;
        $nbNotes = 0;

        // si le prestataire a reçu des notes, calculer la note noteMoyenne
        if ($notes) {
            $noteMoyenne = array_reduce($notes, function ($total, $note) {
                return $total + $note->getNote();
            });
            $nbNotes = count($notes);
            $noteMoyenne = $noteMoyenne / count($notes);
            // arrondir la note à 1 chiffre après la virgule
            $noteMoyenne = round($noteMoyenne, 1);
        }

        // recuperer les favoris du maitre connecté
        if ($this->isGranted('ROLE_MAITRE')) {

            // récupérer le maitre depuis le user connecté
            $user = $this->getUser();
            $repositoryUser = $entityManager->getRepository(Utilisateurs::class);
            $user = $repositoryUser->find($user);
            $maitre = $user->getMaitre();

            // récupérer les favoris du maitre et les stocker dans un talbeau d'id
            $favoris = $maitre->getFavoris();
            $favoris = $favoris->toArray();
            $favoris = array_map(function ($favoris) {
                return $favoris->getPrestataire()->getId();
            }, $favoris);
        }

        // récupérer les dates d'indisponibilités du gardien
        $indisp = null;
        $repositoryIndispos = $entityManager->getRepository(Indisponibilites::class);
        $indispos = $repositoryIndispos->findBy(['prestataires' => $prestataire->getId()]);
        if($indispos != null){
            foreach($indispos as $indispo){
                $indisponibilites[] = [
                    'id' => $indispo->getId(),
                    'start' => $indispo->getDateDebut()->format('Y-m-d'),
                    'end' => $indispo->getDateFin()->format('Y-m-d'),
                    'title' => "indisponible",
                    'color' => "Coral",
                ];
            }
            // json encode les indisponibilités pour les passer en paramètre à fullcalendar
            $indisp = json_encode($indisponibilites);
        }

        // recupérer les reservations du gardien
        $repositoryReservations = $entityManager->getRepository(Reservation::class);
        $reservations = $repositoryReservations->findBy(['gardien' => $prestataire->getId()]);

        // recuperer le nombre de reservations terminées du gardien (dateFin < date du jour)
        $nbReservationsTerminees = 0;
        foreach($reservations as $reservation){
            if($reservation->getDateFin() < new \DateTime()){
                $nbReservationsTerminees++;
            }
        }

        // recuperer le nombre de reservations en cours du gardien (dateFin > date du jour)
        $reservations = $repositoryReservations->findBy(['gardien' => $prestataire->getId()]);
        foreach($reservations as $reservation){
            if($reservation->isValidationPrestataire() == true){
                $reservations[] = [
                    'id' => $reservation->getId(),
                    'start' => $reservation->getDateDebut()->format('Y-m-d'),
                    'end' => $reservation->getDateFin()->format('Y-m-d'),
                    'title' => "En gardiennage",
                    'color' => "#68899D",
                ];
            }
        }
        $reservations = json_encode($reservations);


        // recuperer les commentaires du gardien et stocker dans un tableau
        $commentaires = $prestataire->getCommentaire();
        $commentaires = $commentaires->toArray();
        $notePrecedanteNb = null;


        // trier et mettre le commentaire du maitre connecté en 1er dans le tableau
        if ($this->getUser() and $this->isGranted('ROLE_MAITRE')) {
            // récuperer les notes recues par le gardien
            $notes = $prestataire->getNotesGardiens();
            $commentaireMaitre = null;
            $commentaire = null;
            // récupération du commentaire du maitre
            foreach ($commentaires as $commentaire) {
                if ($commentaire->getMaitre() == $maitre) {
                    $commentaireMaitre = $commentaire;
                }
            }
            // si le commentaire du maitre existe, le mettre en 1er dans le tableau
            if ($commentaireMaitre != null) {
                $key = array_search($commentaireMaitre, $commentaires);
                unset($commentaires[$key]);
                array_unshift($commentaires, $commentaireMaitre);
            }
            // récupérer la note que le maitre a donné au gardien
            $notePrecedante = null;
            $repositoryNote = $entityManager->getRepository(NotesGardien::class);
            if($commentaire != null && $user->getMaitre() != null){
                $notePrecedante = $repositoryNote->findOneBy([
                    'maitre' => $user->getMaitre(),
                    'gardien' => $commentaire->getPrestataires()
                ]);
            }
            
            if ($notePrecedante != null){
                $notePrecedanteNb = $notePrecedante->getNote();
            }

            return $this->render('prestataire/detail.html.twig', [
                'prestataire' => $prestataire,
                'favoris' => $favoris,
                'commentaires' => $commentaires,
                'commentaireMaitre' => $commentaireMaitre,
                'notes' => $notes,
                'noteMoyenne' => $noteMoyenne,
                'nbNotes' => $nbNotes,
                'notePrecedante' => $notePrecedanteNb,
                'indispos' => $indisp,
                'reservations' => $reservations,
                'nbReservationsTerminees' => $nbReservationsTerminees,
            ]);
        }
        else
        {
            return $this->render('prestataire/detail.html.twig', [
                'prestataire' => $prestataire,
                'favoris' => $favoris,
                'commentaires' => $commentaires,
                'notes' => $notes,
                'noteMoyenne' => $noteMoyenne,
                'notePrecedante' => $notePrecedanteNb,
                'nbNotes' => $nbNotes,
                'indispos' => $indisp,
                'reservations' => $reservations,
                'nbReservationsTerminees' => $nbReservationsTerminees,
            ]);
        }
    }


    // ----------------------------------------------------------------
    // Afficher les gardiens sur une map autour de l'adresse du maitre
    // ----------------------------------------------------------------
    /**
     * @Route("/map/gardiens/{id}", name="map_gardiens")
     */
    public function map(EntityManagerInterface $entityManager, $id): Response
    {
        $repository = $entityManager->getRepository(Prestataires::class);
        $prestataires = $repository->findAll();

        $repositoryUser = $entityManager->getRepository(Utilisateurs::class);
        $user = $repositoryUser->find($id);


        return $this->render('prestataire/mapListPrestataires.html.twig', [
            'prestataires' => $prestataires,
            'user' => $user,
        ]);

    }


    // ---------------------------------------------------------------------------
    // Ajouter un commentaire et une note au prestataire (si ROLE_MAITRE)
    // ---------------------------------------------------------------------------
    /**
     * @Route("/detail-gardien/commentaire/{id}/{id_maitre}",name="ajouter_commentaire")
     */
    public function ajouterCommentaire($id, $id_maitre, EntityManagerInterface $entityManager, Request $request): Response
    {
        $repository = $entityManager->getRepository(Prestataires::class);
        $prestataire = $repository->find($id);

        $repositoryMaitre = $entityManager->getRepository(Maitres::class);
        $maitre = $repositoryMaitre->find($id_maitre);

        $notePrecedanteNb = null;

        // creation du formulaire
        $commentaire = new Commentaires();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        // si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $note = new NotesGardien();
            $note->setMaitre($maitre);
            $note->setGardien($prestataire);
            $note->setNote($form->get('note')->getData());

            $date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $commentaire->setDate($date);
            $commentaire->setMaitre($maitre);
            $commentaire->setPrestataires($prestataire);

            // enregistrer en db
            $entityManager->persist($commentaire);
            $entityManager->persist($note);
            $entityManager->flush();

            return $this->redirectToRoute('detail_gardien', ['id' => $id]);
        }

        return $this->render('prestataire/gestionCommentaire.html.twig', [
            'form' => $form->createView(),
            'prestataire' => $prestataire,
            'notePrecedante' => $notePrecedanteNb
        ]);
    }


    // ---------------------------------------------------------------------------
    // Modifier le commentaire (si ROLE_MAITRE & si le commentaire est le sien)
    // ---------------------------------------------------------------------------
    /**
     * @Route("/detail-gardien/modifier-commentaire/{id}",name="modifier_commentaire")
     */
    public function modifierCommentaire($id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $repository = $entityManager->getRepository(Commentaires::class);
        $commentaire = $repository->find($id);

        $id_prestataire = $commentaire->getPrestataires()->getId();

        $repositoryUtilisateur = $entityManager->getRepository(Utilisateurs::class);
        $user = $repositoryUtilisateur->find($this->getUser());

        // récupérer la note que le maitre a donné au gardien
        $repositoryNote = $entityManager->getRepository(NotesGardien::class);
        $notePrecedante = $repositoryNote->findOneBy([
            'maitre' => $user->getMaitre(),
            'gardien' => $commentaire->getPrestataires()
        ]);
        $notePrecedanteNb = $notePrecedante->getNote();


        // creation du formulaire
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);


        // si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('note')->getData() != $notePrecedanteNb and $form->get('note')->getData() != null) {
                $notePrecedante->setNote($form->get('note')->getData());
            }

            $date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $commentaire->setDate($date);

            // enregistrer en bdd
            $entityManager->persist($commentaire);
            $entityManager->persist($notePrecedante);
            $entityManager->flush();

            return $this->redirectToRoute('detail_gardien', ['id' => $id_prestataire]);
        }

        return $this->render('prestataire/gestionCommentaire.html.twig', [
            'form' => $form->createView(),
            'prestataire' => $commentaire->getPrestataires(),
            'notePrecedante' => $notePrecedanteNb
        ]);
    }

}

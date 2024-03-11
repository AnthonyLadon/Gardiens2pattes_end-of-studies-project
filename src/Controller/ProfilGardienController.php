<?php

namespace App\Controller;

use App\Entity\Images;
use App\Form\ImageType;
use App\Entity\Adresses;
use App\Form\GardienType;
use App\Entity\Reservation;
use App\Entity\Commentaires;
use App\Entity\NotesGardien;
use App\Entity\Prestataires;
use App\Entity\Utilisateurs;
use App\Form\UpdateAdressType;
use App\Entity\SignalementAbus;
use OpenCage\Geocoder\Geocoder;
use App\Entity\Indisponibilites;
use App\services\UploaderHelper;
use App\Form\IndisponibiliteType;
use App\Form\ReponseCommentaireType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfilGardienController extends AbstractController
{

  //***************************************************************************************************
  //* Gestion profil Gardien
  //****************************************************************************************************


  // ---------------------------------------------------
  // Affichage profil Gardien 
  // ---------------------------------------------------
  /**
   * @Route("/profil/gardien/{id}", name="profil_gardien")
   */
  public function showProfilGardien(entityManagerInterface $entityManager, $id): Response
  {
    $repository = $entityManager->getRepository(Prestataires::class);
    $prestataire = $repository->find($id);

    $commentaires = $entityManager->getRepository(Commentaires::class)->findBy(['prestataires' => $prestataire]);
    $reservations = $entityManager->getRepository(Reservation::class)->findBy(['gardien' => $prestataire]);

    // compter le nombre de Reservation à valider par le gardien
    $nbReservations = $entityManager->getRepository(Reservation::class)->findBy(['gardien' => $prestataire, 'validationPrestataire' => false]);
    $nbReservations = count($nbReservations);

    $notesGardien = $entityManager->getRepository(NotesGardien::class)->findBy(['gardien' => $prestataire]);
    // lié chaque note au maitre qui l'a donné
    $notes = [];
    foreach ($notesGardien as $note) {
      $notes[] = [
        'note' => $note->getNote(),
        'maitre' => $note->getMaitre(),
      ];
    }

    // récupérer les dates d'indisponibilités du gardien
    $indisp = null;
    $repositoryIndispos = $entityManager->getRepository(Indisponibilites::class);
    $indispos = $repositoryIndispos->findBy(['prestataires' => $prestataire->getId()]);
    if ($indispos != null) {
      foreach ($indispos as $indispo) {
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

    // recupérer les reservations acceptées du gardien
    $resas = null;
    $repositoryReservations = $entityManager->getRepository(Reservation::class);
    $reservations = $repositoryReservations->findBy(['gardien' => $prestataire->getId()]);
    foreach ($reservations as $reservation) {
      if ($reservation->isValidationPrestataire() == true) {
        $reservations[] = [
          'id' => $reservation->getId(),
          'start' => $reservation->getDateDebut()->format('Y-m-d'),
          'end' => $reservation->getDateFin()->format('Y-m-d'),
          'title' => "Reservation acceptée",
          'color' => "CadetBlue",
        ];
      }
    }
    $resas = json_encode($reservations);

    // recupérer les reservations en attente d'acceptation du gardien
    $resaEnAtttente = null;
    $repositoryReservations = $entityManager->getRepository(Reservation::class);
    $reservations = $repositoryReservations->findBy(['gardien' => $prestataire->getId()]);
    foreach ($reservations as $reservation) {
      if ($reservation->isValidationPrestataire() == false) {
        $resaEnAtttente[] = [
          'id' => $reservation->getId(),
          'start' => $reservation->getDateDebut()->format('Y-m-d'),
          'end' => $reservation->getDateFin()->format('Y-m-d'),
          'title' => "En attente de validation",
          'color' => "LightSalmon",
        ];
      }
    }
    $resaEnAtttente = json_encode($resaEnAtttente);


    return $this->render('profil/gardien/gardien.html.twig', [
      'prestataire' => $prestataire,
      'commentaires' => $commentaires,
      'reservations' => $reservations,
      'nbReservations' => $nbReservations,
      'indispos' => $indisp,
      'resas' => $resas,
      'resaEnAtttente' => $resaEnAtttente,
      'notes' => $notes,
    ]);
  }


  // ----------------------------------------------------------------
  // Ajouter/modifier la photo de profil gardien
  // ----------------------------------------------------------------
  /**
   * @Route("/profil/gardien/photo_profil/{id}",name="addImageGardien")
   */
  public function addImagePrest(Request $request, Prestataires $prestataire, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, $id, TranslatorInterface $translator): Response
  {

    $repository = $entityManager->getRepository(Prestataires::class);
    $prestataire = $repository->findOneById($id);
    // récupération image actuelle si elle existe
    $imgProfil = $entityManager->getRepository(Images::class)->findOneBy(['prestataire' => $prestataire]);

    $image = new Images();
    $form = $this->createForm(ImageType::class, $image);
    $form->handleRequest($request);


    if ($form->isSubmitted() && $form->isValid()) {

      $uploadedImage = $form['imageFile']->getData();

      if ($imgProfil && $uploadedImage) {
        // Suppression de l'image en base de données
        $query = $entityManager->createQuery('DELETE FROM App\Entity\Images i WHERE i.prestataire = :id')
          ->setParameter('id', $prestataire->getId());
        $query->execute();

        // suppression du fichier dans le dossier uploads
        $imgToDelete = $uploaderHelper->getUploadPath() . '/' . $imgProfil;
        unlink($imgToDelete);
      }

      // évite de supprimer l'image si formulaire est envoyé vide
      if ($uploadedImage) {
        $newImageName = $uploaderHelper->uploadImages($uploadedImage);
        $image->setImage($newImageName);
        $image->setPrestataire($prestataire);
        $entityManager->persist($image);
        $entityManager->flush();

        $msg = $translator->trans('Votre image a bien été enregistré');

        $this->addFlash('success', $msg);

        return $this->redirectToRoute('profil_gardien', [
          "id" => $id
        ]);
      }

      $msg = $translator->trans('L\'image n\'est pas valide');

      $this->addFlash('notice',  $msg);
    }

    return $this->render('profil/gardien/imageProfil.html.twig', [
      'form' => $form->createView(),
      'id' => $id
    ]);
  }


  // ---------------------------------------------------
  // CRUD infos profil Gardien 
  // ---------------------------------------------------
  /**
   * @Route("/profil/gardien/infos/{id}", name="update_infos_prest")
   */
  public function updatePrest(EntityManagerInterface $entityManager, $id, Request $request, TranslatorInterface $translator): Response
  {
    $repository = $entityManager->getRepository(Prestataires::class);
    $prestataire = $repository->find($id);

    $form = $this->createForm(GardienType::class, $prestataire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

      $form = $form->getData();

      // si le gardien ne propose pas de garde à domicile, on met le tarif à 0
      if ($form->getTarif() == null || $form->getTarif() == 0) {
        $form->setGardeDomicile(false);
      }
      // si le gardien ne propose pas la garde à domicile, on met le tarif à 0
      if ($form->isGardeDomicile() and $form->isGardeDomicile() == false) {
        $form->setTarif(0);
      }

      $entityManager->persist($prestataire);
      $entityManager->flush();

      $msg = $translator->trans('Vos informations ont bien été mises à jour');

      $this->addFlash('success', $msg);

      return $this->redirectToRoute('profil_gardien', [
        'id' => $id
      ]);
    }

    return $this->render('profil/gardien/updateInfos.html.twig', [
      'prestataire' => $prestataire,
      'form' => $form->createView(),
    ]);
  }


  // --------------------------------------------------------------
  // Modifier adresse gardien (et mise ajout coordonnées GPS)
  // --------------------------------------------------------------
  /**
   * @Route("/profil/gardien/adresse/{id}", name="update_adress_prest")
   */
  public function updatePrestAdress(EntityManagerInterface $entityManager, $id, Request $request, TranslatorInterface $translator): Response
  {
    $repository = $entityManager->getRepository(Utilisateurs::class);
    $user = $repository->find($id);

    $repositoryAdresse = $entityManager->getRepository(Adresses::class);
    if ($user->getAdresse() != null) {
      $adresse = $repositoryAdresse->findOneBy(['id' => $user->getAdresse()->getId()]);
    } else {
      $adresse = new Adresses();
    }

    $form = $this->createForm(UpdateAdressType::class, $adresse);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

      // récupération données communes,localité,zipCodes depuis fichier json
      $json = file_get_contents('../public/zipcode-belgium.json');
      $data = json_decode($json, true);

      // ajout commune selon le code postal entré par l'utilisateur
      $codePostal = $form->get("codePostal")->getViewData();
      $commune = $form->get("commune")->getViewData();
      $localite = null;
      $rue = $form->get("rue")->getViewData();
      $num = $form->get("numero")->getViewData();

      // verifie que $codePostal est bien un Integer de 4 chiffres
      // sinon ajout un 0 devant
      $codePostal = (int)$codePostal;
      $codePostal = strval($codePostal);
      $codePostal = str_pad($codePostal, 4, "0", STR_PAD_LEFT);

      // Trouve la localité selon code postal entrée par l'utilisateur
      foreach ($data as $key => $value) {
        if ($value['zip'] == $codePostal) {
          $localite = $value['localite'];
        }
      }

      // récupération des coordonnées GPS de l'adresse de l'utilisateur
      $lat = null;
      $lng = null;

      $adresseString = ($rue . " " . $num . " " . $codePostal . " " . $commune);
      $apiKey = 'b091b28cf9ff4f33acbedc0c90166f8c'; // <- Clé API OpenCage
      // Appel de l'API Geocoder (opencagedata.com) pour récupérer les coordonnées GPS de l'adresse
      $geocoder = new Geocoder($apiKey);

      $result = $geocoder->geocode($adresseString);
      if ($result) {
        // récupération des données de latitude et longitude de l'adresse passée en paramêtre
        if (isset($result['results'][0]['geometry']['lat']) && isset($result['results'][0]['geometry']['lng'])) {
          $lat = $result['results'][0]['geometry']['lat'];
          $lng = $result['results'][0]['geometry']['lng'];

          // chager en string les coordonnées GPS
          $lat = strval($lat);
          $lng = strval($lng);

          // remplissage entiée adresse
          $adresse->setCodePostal($codePostal);
          $adresse->setCommune($commune);
          if ($localite != null) {
            $adresse->setLocalite($localite);
          }
          $adresse->setRue($rue);
          $adresse->setNumero($num);
          $adresse->setLatitude($lat);
          $adresse->setLongitude($lng);
          $user->setAdresse($adresse); // si l'adresse n'existait pas, on la crée

          $entityManager->persist($user);
          $entityManager->persist($adresse);
          $entityManager->flush();
        } else {
          $msg = $translator->trans('Adresse non trouvée, veuillez verifier votre adresse');
          $this->addFlash('notice', $msg);
          return $this->redirectToRoute('update_adress_prest', [
            'id' => $id
          ]);
        }
      }

      $msg = $translator->trans('Vos informations ont bien été mises à jour');
      $this->addFlash('success', $msg);

      return $this->redirectToRoute('profil_gardien', [
        'id' => $user->getPrestataire()->getId()
      ]);
    }

    return $this->render('profil/gardien/updateAdresse.html.twig', [
      'user' => $user,
      'form' => $form->createView(),
    ]);
  }


  // ----------------------------------------------------------------
  // Ajouter des photos au carrousel du Gardien
  // ----------------------------------------------------------------
  /**
   * @Route("/profil/gardien/carrousel/{id}",name="images_carrousel_gardien")
   */
  public function crudImagesPrest(Request $request, Prestataires $prestataire, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, $id, TranslatorInterface $translator): Response
  {

    $repository = $entityManager->getRepository(Prestataires::class);
    $prestataire = $repository->findOneById($id);

    $image = new Images();
    $form = $this->createForm(ImageType::class, $image);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

      $uploadedImage = $form['imageFile']->getData();

      if ($uploadedImage) {
        $newImageName = $uploaderHelper->uploadImages($uploadedImage);
        $image->setImage($newImageName);
        $image->setGallerieGardien($prestataire);
      }

      if ($image->getImage() != null) {
        $entityManager->persist($image);
        $entityManager->flush();
        $this->addFlash('success', 'Votre image a bien été enregistré');

        return $this->redirectToRoute('images_carrousel_gardien', [
          "id" => $id
        ]);
      }

      $msg = $translator->trans('Votre image n\'est pas valide');

      $this->addFlash('notice', $msg);
    }

    return $this->render('profil/gardien/gardienImagesCarrousel.html.twig', [
      'form' => $form->createView(),
      'id' => $id,
      "prestataire" => $prestataire
    ]);
  }


  // ----------------------------------------------------------------
  // supprimer une photo du carrousel profil de Prestataire
  // ----------------------------------------------------------------
  /**
   * @Route("/profil/gardien/suprimer_img/{id}/{id_img}",name="supprImageGardien")
   */
  public function supprImagesPrest(Prestataires $prestataire, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, $id, $id_img, TranslatorInterface $translator): Response
  {

    // On récupére l'id du prestataire et celui de l'image passés en parametre depus le lien
    $repository = $entityManager->getRepository(Prestataires::class);
    $prestataire = $repository->findOneById($id);
    // récupération de l'image
    $img_carrousel_prest = $entityManager->getRepository(Images::class)->findOneBy(['id' =>  $id_img]);

    // Suppression de l'image en base de données
    $query = $entityManager->createQuery('DELETE FROM App\Entity\Images i WHERE i.id = :id')
      ->setParameter('id', $id_img);
    $query->execute();

    // suppression du fichier dans le dossier uploads
    $imgToDelete = $uploaderHelper->getUploadPath() . '/' . $img_carrousel_prest;
    unlink($imgToDelete);

    $entityManager->flush();

    $msg = $translator->trans('Votre image a bien été supprimé');

    $this->addFlash('success', $msg);

    return $this->redirectToRoute('images_carrousel_gardien', [
      "id" => $id,
      "prestataire" => $prestataire
    ]);
  }


  // ----------------------------------------------------------------
  // Ajouter une période d'indisponibilité -> profil de Prestataire
  // ----------------------------------------------------------------
  /**
   * @Route("/profil/gardien/indisponibilite/{id}",name="indisponibilite_gardien")
   */
  public function crudIndisponibilitePrest(Request $request, Prestataires $prestataire, EntityManagerInterface $entityManager, $id, TranslatorInterface $translator): Response
  {
    $repository = $entityManager->getRepository(Prestataires::class);
    $prestataire = $repository->findOneById($id);

    $indisponibilite = new Indisponibilites();
    $form = $this->createForm(IndisponibiliteType::class, $indisponibilite);
    $form->handleRequest($request);

    if ($form->isSubmitted() and $form->isValid()) {
      $indisponibilite->setPrestataires($prestataire);
      $indisponibilite->setDateDebut($form->getData()->getDateDebut());
      $indisponibilite->setDateFin($form->getData()->getDateFin());
      $entityManager->persist($indisponibilite);
      $entityManager->flush();

      $msg = $translator->trans('Votre période d\'indisponibilité a bien été enregistré');

      $this->addFlash('success', $msg);

      return $this->redirectToRoute('profil_gardien', [
        "id" => $id
      ]);
    }

    return $this->render('profil/gardien/gardienIndisponibilite.html.twig', [
      'form' => $form->createView(),
      'id' => $id,
      "prestataire" => $prestataire
    ]);
  }


  // ----------------------------------------------------------------
  // Signaler un commentaire -> profil de Prestataire
  // ----------------------------------------------------------------
  /**
   * @Route("/profil/gardien/signaler/{id}/{id_commentaire}",name="signaler_commentaire")
   */
  public function signalerCommentaire(EntityManagerInterface $entityManager, $id, $id_commentaire, TranslatorInterface $translator): Response
  {
    $commentaire = $entityManager->getRepository(Commentaires::class)->findOneById($id_commentaire);

    // verifier si le commentaire n'a pas deja été signalé
    $signalement = $entityManager->getRepository(SignalementAbus::class)->findOneBy(['commentaire' => $commentaire]);
    if ($signalement and $signalement->isEstTraite() == false) {
      $this->addFlash('error', 'Ce commentaire a déjà été signalé');
      return $this->redirectToRoute('profil_gardien', [
        "id" => $id
      ]);
    }

    $signalement = new SignalementAbus();
    $signalement->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
    $signalement->setPrestataires($commentaire->getPrestataires());
    $signalement->setCommentaire($commentaire);

    $entityManager->persist($signalement);
    $entityManager->flush();

    $msg = $translator->trans('Le commentaire a bien été signalé');

    $this->addFlash('success', $msg);

    return $this->redirectToRoute('profil_gardien', [
      "id" => $id
    ]);
  }



  // --------------------------------------------------------------------------------------------
  // repondre à un commentaire (ajout texte dans champ reponseGardien dans la table commentaire)
  // --------------------------------------------------------------------------------------------
  /**
   * @Route("/profil/gardien/repondre/{id}/{id_commentaire}",name="repondre_commentaire")
   */
  public function repondreCommentaire(EntityManagerInterface $entityManager, $id, $id_commentaire, Request $request, TranslatorInterface $translator): Response
  {
    $commentaire = $entityManager->getRepository(Commentaires::class)->findOneById($id_commentaire);

    $form = $this->createForm(ReponseCommentaireType::class, $commentaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() and $form->isValid()) {
      $commentaire->setReponseGardien($form->getData()->getReponseGardien());
      $entityManager->persist($commentaire);
      $entityManager->flush();

      $msg = $translator->trans('Votre réponse a bien été enregistrée');

      $this->addFlash('success', $msg);

      return $this->redirectToRoute('profil_gardien', [
        "id" => $id
      ]);
    }

    return $this->render('profil/gardien/repondreCommentaire.html.twig', [
      'form' => $form->createView(),
      'id' => $id,
      'id_commentaire' => $id_commentaire,
      'commentaire' => $commentaire
    ]);
  }
}

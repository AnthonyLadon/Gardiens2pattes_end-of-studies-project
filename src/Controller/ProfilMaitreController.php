<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Animaux;
use App\Entity\Favoris;
use App\Entity\Maitres;
use App\Form\ImageType;
use App\Form\VideoType;
use App\Entity\Adresses;
use App\Form\AnimalType;
use App\Form\MaitreType;
use App\Form\UserFormType;
use App\Entity\Prestataires;
use App\Entity\Utilisateurs;
use App\Form\UpdateAdressType;
use OpenCage\Geocoder\Geocoder;
use App\services\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ProfilMaitreController extends AbstractController
{

//***************************************************************************************************
//* Gestion profil Maître 
//****************************************************************************************************


    /**
     * @Route("/profil/maitre/{id}", name="profil_maitre")
     */
    // ---------------------------------------------------
    // Affichage profil Maître
    // ---------------------------------------------------
    public function showProfilMaitre(entityManagerInterface $entityManager, $id): Response
    {
        $repository = $entityManager->getRepository(Maitres::class);
        $maitre = $repository->find($id);

        return $this->render('profil/maitre/maitre.html.twig', [
            'maitre' => $maitre,
        ]);
    }

    // ---------------------------------------------------
    // CRUD infos profil Maitre
    // ---------------------------------------------------
     /**
     * @Route("/profil/maitre/infos/{id}", name="update_infos_maitre")
     */
    public function updateMaitre(EntityManagerInterface $entityManager, $id, Request $request, TranslatorInterface $translator): Response
    {
      $repository = $entityManager->getRepository(Maitres::class);
      $maitre = $repository->find($id);

      $form = $this->createForm(MaitreType::class, $maitre);
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {

        $form = $form->getData();

        $entityManager->persist($maitre);
        $entityManager->flush();

        $msg = $translator->trans('Vos informations ont bien été mises à jour');

        $this->addFlash('success', $msg);

      return $this->redirectToRoute('profil_maitre', [
        'id'=> $id
        ]);
      }

        return $this->render('profil/maitre/updateInfos.html.twig', [
          'maitre' => $maitre,
          'form' => $form->createView(),
        ]);
    }


    // ----------------------------------------------------------------
    // Ajouter/modifier la photo de profil maître
    // ----------------------------------------------------------------
    /**
     * @Route("/profil/maitre/photo_profil/{id}",name="addImageMaitre")
     */
    public function addImageMaitre(Request $request, Maitres $maitre, UploaderHelper $uploaderHelper, EntityManagerInterface $entityManager, $id, TranslatorInterface $translator): Response
    {

        $repository = $entityManager->getRepository(Maitres::class);
        $maitre = $repository->findOneById($id);
        // récupération image actuelle si elle existe
        $imgProfil = $entityManager->getRepository(Images::class)->findOneBy(['maitre' => $maitre]);

        $image = new Images();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);


          if ($form->isSubmitted() && $form->isValid()) {

          $uploadedImage = $form['imageFile']->getData();

          if ($imgProfil && $uploadedImage){
            // Suppression de l'image en base de données
            $query = $entityManager->createQuery('DELETE FROM App\Entity\Images i WHERE i.maitre = :id')
            ->setParameter('id', $maitre->getId());
            $query->execute();

            // suppression du fichier dans le dossier uploads
            $imgToDelete = $uploaderHelper->getUploadPath().'/'.$imgProfil;
            unlink($imgToDelete);
          }

           // évite de supprimer l'image si formulaire est envoyé vide
          if($uploadedImage){
            $newImageName = $uploaderHelper->uploadImages($uploadedImage);
            $image->setImage($newImageName);
            $image->setMaitre($maitre);
            $entityManager->persist($image);
            $entityManager->flush();

            $msg = $translator->trans('Votre image a bien été enregistré');
 
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('profil_maitre', [
              "id" => $id
          ]);
          }

          $msg = $translator->trans('L\image n\'est pas valide');

           $this->addFlash('notice', $msg);
         }

       return $this->render('profil/maitre/imageProfil.html.twig', [
        'form' => $form->createView(),
        'id' => $id
     ]);
    }


    // ----------------------------------------------------------------
    // Modifier le pseudo du maître
    // ----------------------------------------------------------------
    /**
     * @Route("/profil/maitre/modif_pseudo/{id}", name="pseudo_maitre")
     */
    public function updatePseudoMaitre($id, EntityManagerInterface $entityManager, Request $request, TranslatorInterface $translator): Response
    {
      $repository = $entityManager->getRepository(Maitres::class);
      $maitre = $repository->find($id);

      $repositoryUser = $entityManager->getRepository(Utilisateurs::class);
      $user = $repositoryUser->findOneBy(['maitre' => $maitre]);
    
      $form = $this->createForm(UserFormType::class, $user);
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()){
          $user->setPseudo($form->get('pseudo')->getData());
          $entityManager->persist($user);
          $entityManager->flush();

          return $this->redirectToRoute('profil_maitre', [
            'id'=> $id
            ]);
      }

      return $this->render('profil/maitre/modifier_pseudo.html.twig', [
        'form' => $form->createView(),
      ]);
    }


    // ----------------------------------------------------------------
    // Afficher la liste des gardiens favoris d'un maitre
    // ----------------------------------------------------------------
    /**
     * @Route("/profil/maitre/favoris/{id}", name="favoris_maitre")
     */
    public function favorisMaitre(EntityManagerInterface $entityManager, Request $request, PaginatorInterface $paginator, $id): Response
    {
        $repository = $entityManager->getRepository(Maitres::class);
        $maitre = $repository->find($id);

        // recuperer les gardiens favoris du maitre
        $favoris = $maitre->getFavoris();
  

          // Utilise le bundle de pagination => https://github.com/KnpLabs/KnpPaginatorBundle
          $pagination = $paginator->paginate(
            $favoris, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            4 // Définition de la limite d'items par page
        );

        return $this->render('profil/maitre/favoris.html.twig', [
            'maitre' => $maitre,
            'pagination' => $pagination
        ]);
    }


    // ------------------------------------------------------------------------------------
    // Ajouter / retirer un gardien en favoris (lorsqu'on est loggé en tant que maitre)
    // ------------------------------------------------------------------------------------
    /**
     * @Route("/favoris/{id}",name="add_remove_favoris_maitre")
     */
    public function favoris($id, EntityManagerInterface $entityManager): Response
    {
        // récupérer le maitre depuis le user connecté
        $user = $this->getUser();
        $repositoryUser = $entityManager->getRepository(Utilisateurs::class);
        $user = $repositoryUser->find($user);
        $maitre = $user->getMaitre();

        // récupérer le prestataire depuis l'id
        $repositoryPrestataire = $entityManager->getRepository(Prestataires::class);
        $prestataire = $repositoryPrestataire->find($id);

        // récupérer les favoris du maitre et les stocker dans un talbeau d'id
        $favoris = $maitre->getFavoris();
        $favoris = $favoris->toArray();
        $favoris = array_map(function ($favoris) {
            return $favoris->getPrestataire()->getId();
        }, $favoris);

        // si le prestataire n'est pas dans les favoris du maitre
        if (!in_array($id, $favoris)) {
            $favoris = new Favoris();
            $favoris->setMaitres($maitre);
            $favoris->setPrestataire($prestataire);
            $entityManager->persist($favoris);
            $entityManager->flush();
        } else {
            // si le prestataire est dans les favoris du maitre
            $repositoryFavoris = $entityManager->getRepository(Favoris::class);
            $favoris = $repositoryFavoris->findOneBy(['maitres' => $maitre, 'prestataire' => $prestataire]);
            $entityManager->remove($favoris);
            $entityManager->flush();
        }

        return $this->redirectToRoute('detail_gardien', [
          'id' => $id
          ]);
    }


    // -----------------------------------------------------------------------
    // Abonnement / désabonnement à la newsletter
    // -----------------------------------------------------------------------
    /**
     * @Route("/profil/maitre/newsletter/{id}", name="newsletter_maitre")
     */
    public function newsletterMaitre($id, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $repository = $entityManager->getRepository(Maitres::class);
        $maitre = $repository->find($id);

        if ($maitre->isNewsletter() == 0) {
            $maitre->setNewsletter(1);
            $entityManager->persist($maitre);
            $entityManager->flush();

            $msg = $translator->trans('Votre abonnement à la newsletter a bien été pris en compte');
            $this->addFlash('success', $msg);
        } else {
            $maitre->setNewsletter(0);
            $entityManager->persist($maitre);
            $entityManager->flush();
            $msg = $translator->trans('Votre abonnement à la newsletter a bien été annulé');
            $this->addFlash('success', $msg);
        }


        return $this->redirectToRoute('profil_maitre', [
            'id' => $id,
        ]);
    }

    // -----------------------------------------------------------------------
    // Ajout d'un animal par le maitre
    // -----------------------------------------------------------------------
    /**
     * @Route("/profil/maitre/ajout_animal/{id}", name="animal_maitre")
     */
    public function animalMaitre($id, EntityManagerInterface $entityManager, Request $request, TranslatorInterface $translator): Response
    {
        $repository = $entityManager->getRepository(Maitres::class);
        $maitre = $repository->find($id);

        $animal = new Animaux();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animal->setMaitre($maitre);
            $entityManager->persist($animal);
            $entityManager->flush();

            $msg = $translator->trans('Votre animal a bien été enregistré');
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('profil_maitre', [
                'id' => $id,
            ]);
        }

        return $this->render('profil/maitre/ajout_animal.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
        ]);
    }


    // -----------------------------------------------------------------------
    // Suppression un animal par le maitre
    // -----------------------------------------------------------------------
    /**
     * @Route("/profil/maitre/supprimer_animal/{id}", name="supprimer_animal_maitre")
     */
    public function supprimerAnimalMaitre($id, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $repository = $entityManager->getRepository(Animaux::class);
        $animal = $repository->find($id);

        $entityManager->remove($animal);
        $entityManager->flush();

        $msg = $translator->trans('Votre animal a bien été supprimé');
        $this->addFlash('success', $msg);

        return $this->redirectToRoute('profil_maitre', [
            'id' => $animal->getMaitre()->getId(),
        ]);
    }



    // -----------------------------------------------------------------------
    // Ajout photo de profil de l'animal par le maitre
    // -----------------------------------------------------------------------
    /**
     * @Route("/profil/maitre/photo_animal/{id}/{animal_id}", name="ajout_photo_animal")
     */
    public function ajoutPhotoAnimalMaitre($id, $animal_id, EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper, Request $request, TranslatorInterface $translator): Response
    {
        $repository = $entityManager->getRepository(Animaux::class);
        $animal = $repository->find($animal_id);

        // récuperer l'image de l'animal si elle existe sinon on lui donne la valeur null
        if ($entityManager->getRepository(Images::class)->findOneBy(['animal' => $animal])) {
            $Currentimage = $entityManager->getRepository(Images::class)->findOneBy(['animal' => $animal]);
            $Currentimage = $Currentimage->getImage();
        } else {
            $Currentimage = null;
        }

        // création du formulaire
        $imgAnimal = new Images();
        $form = $this->createForm(ImageType::class, $imgAnimal);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {

          $uploadedImage = $form['imageFile']->getData();

          if ($Currentimage && $uploadedImage){
            // Suppression de l'image en base de données
            $query = $entityManager->createQuery('DELETE FROM App\Entity\Images i WHERE i.animal = :id')
            ->setParameter('id', $animal_id);
            $query->execute();

            // suppression du fichier dans le dossier uploads
              $imgToDelete = $uploaderHelper->getUploadPath().'/'.$Currentimage;
              unlink($imgToDelete);
          }

            // évite de supprimer l'image si formulaire est envoyé vide
            if($uploadedImage){
              $newImageName = $uploaderHelper->uploadImages($uploadedImage);
              $imgAnimal->setImage($newImageName);
              $imgAnimal->setAnimal($animal);
              $entityManager->persist($imgAnimal);
              $entityManager->flush();

              $msg = $translator->trans('Votre photo a bien été enregistrée');
             $this->addFlash('success', $msg);

              return $this->redirectToRoute('profil_maitre', [
                  'id' => $id,
              ]);
           }
      }

        return $this->render('profil/maitre/ajout_photo_animal.html.twig', [
            'form' => $form->createView(),
            'id' => $id,
            'animal_id' => $animal_id,
        ]);
  }


    // -----------------------------------------------------------------------
    // modifier les informations de l'animal par le maitre
    // -----------------------------------------------------------------------
    /**
     * @Route("/profil/maitre/modifier_animal/{id}/{id_animal}", name="modifier_infos_animal_maitre")
     */
    public function modifierAnimalMaitre($id, $id_animal, EntityManagerInterface $entityManager, Request $request, TranslatorInterface $translator): Response
    {
        $repository = $entityManager->getRepository(Animaux::class);
        $animal = $repository->find($id_animal);

        $repositoryMaitre = $entityManager->getRepository(Maitres::class);
        $maitre = $repositoryMaitre->find($id);

        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($animal);
            $entityManager->flush();

            $msg = $translator->trans('Vos informations ont bien été modifiées');
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('profil_maitre', [
              'id' => $maitre->getId()
            ]);
        }

        return $this->render('profil/maitre/modifier_infos_animal.html.twig', [
            'form' => $form->createView(),
            'id' => $maitre->getId(),
            'animal_id' => $id,
        ]);
    }


    // -----------------------------------------------------------------------
    // Ajout de photo au carrousel page profil maitre
    // -----------------------------------------------------------------------
    /**
     * @Route("/profil/maitre/photo_carrousel/{id}", name="ajout_photo_carrousel")
     */
    public function ajoutPhotoCarrouselMaitre($id, EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper, Request $request, TranslatorInterface $translator): Response
    {
      $repository = $entityManager->getRepository(Maitres::class);
      $maitre = $repository->find($id);

      // création du formulaire
      $imgCarrousel = new Images();
      $form = $this->createForm(ImageType::class, $imgCarrousel);
      $form->handleRequest($request);


       if ($form->isSubmitted() && $form->isValid()) {

        $uploadedImage = $form['imageFile']->getData();

        if($uploadedImage){
          $newImageName = $uploaderHelper->uploadImages($uploadedImage);

          $imgCarrousel->setImage($newImageName);
          $imgCarrousel->setGallerieMaitre($maitre);
        }

        if ($imgCarrousel->getImage() != null ){
          $entityManager->persist($imgCarrousel);
          $entityManager->flush();

          $msg = $translator->trans('Votre photo a bien été enregistrée');
          $this->addFlash('success', $msg);

          return $this->redirectToRoute('ajout_photo_carrousel', [
              'id' => $id,
          ]);
        }
        
        $msg = $translator->trans('Votre image n\'est pas valide');
        $this->addFlash('notice', $msg);
       }

      return $this->render('profil/maitre/ajout_photo_carrousel.html.twig', [
          'form' => $form->createView(),
          'id' => $id,
      ]);

    }


    // -----------------------------------------------------------------------
    // Supprimer une photo du carrousel page profil maitre
    // -----------------------------------------------------------------------
    /**
     * @Route("/profil/maitre/supprimer_photo_carrousel/{id}/{image_id}", name="supprimer_photo_carrousel")
     */
    public function supprimerPhotoCarrouselMaitre($id, $image_id, EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper, Request $request, TranslatorInterface $translator): Response
    {
      
      $repositoryImage = $entityManager->getRepository(Images::class);
      $image = $repositoryImage->find($image_id);

      $imageToDelete = $image->getImage();

      $entityManager->remove($image);
      $entityManager->flush();

      // suppression du fichier dans le dossier uploads
      $imgToDelete = $uploaderHelper->getUploadPath().'/'.$imageToDelete;
      unlink($imgToDelete);

      $msg = $translator->trans('Votre photo a bien été supprimée');
      $this->addFlash('success', $msg);

      return $this->redirectToRoute('ajout_photo_carrousel', [
          'id' => $id,
      ]);
    }


    // --------------------------------------------------------------
    // Modifier adresse maitre (et mise a jout coordonnées GPS)
    // --------------------------------------------------------------
     /**
     * @Route("/profil/maitre/adresse/{id}", name="update_adress_maitre")
     */
    public function updateMaitreAdress(EntityManagerInterface $entityManager, $id, Request $request, TranslatorInterface $translator): Response
    {
      $repository = $entityManager->getRepository(Utilisateurs::class);
      $user = $repository->find($id);

      $repositoryAdresse = $entityManager->getRepository(Adresses::class);
      if($user->getAdresse() != null){
        $adresse = $repositoryAdresse->findOneBy(['id' => $user->getAdresse()->getId()]);
      }else{
        $adresse = new Adresses();
      }

      $form = $this->createForm(UpdateAdressType::class, $adresse);
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()){

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
            if ($value['zip'] == $codePostal){
                $localite = $value['localite'];
            }
        }

        // récupération des coordonnées GPS de l'adresse de l'utilisateur
        $lat = null;
        $lng = null;

        $adresseString = ($rue." ".$num." ".$codePostal." ".$commune);
        $apiKey = 'b091b28cf9ff4f33acbedc0c90166f8c';// <- Clé API OpenCage
        // Appel de l'API Geocoder (opencagedata.com) pour récupérer les coordonnées GPS de l'adresse
        $geocoder = new Geocoder($apiKey);


        $result = $geocoder->geocode($adresseString);
        if($result){
            // récupération des données de latitude et longitude de l'adresse passée en paramêtre
            if(isset($result['results'][0]['geometry']['lat']) && isset($result['results'][0]['geometry']['lng'])){
                $lat = $result['results'][0]['geometry']['lat'];
                $lng = $result['results'][0]['geometry']['lng'];

                // chager en string les coordonnées GPS
                $lat = strval($lat);
                $lng = strval($lng);

                // remplissage entiée adresse
                $adresse->setCodePostal($codePostal);
                $adresse->setCommune($commune);
                if ($localite != null){
                    $adresse->setLocalite($localite);
                }
                $adresse->setRue($rue);
                $adresse->setNumero($num);
                $adresse->setLatitude($lat);
                $adresse->setLongitude($lng);
                $user->setAdresse($adresse);// si l'adresse n'existait pas, on la crée

                $entityManager->persist($user);
                $entityManager->persist($adresse);
                $entityManager->flush();
            }else{
                $msg = $translator->trans('Adresse non trouvée, veuillez verifier votre adresse');
                $this->addFlash('notice', $msg);
                return $this->redirectToRoute('update_adress_maitre', [
                  'id'=> $id
                  ]);
            }
        }

        $msg = $translator->trans('Vos informations ont bien été mises à jour');
        $this->addFlash('success', $msg );

      return $this->redirectToRoute('profil_maitre', [
        'id'=> $user->getMaitre()->getId()
        ]);
      }

        return $this->render('profil/maitre/updateAdress.html.twig', [
          'user' => $user,
          'form' => $form->createView(),
        ]);
    }
}

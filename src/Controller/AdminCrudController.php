<?php

namespace App\Controller;

use App\Entity\Images;
use App\Form\ImageType;
use App\services\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCrudController extends AbstractController
{

    // -----------------------------------------------
    // Gestion des photos du carrousel page d'accueil
    // -----------------------------------------------
    /**
     * @Route("/carrousel", name="admin_carrousel")
     */
    public function CrudCarrousel(EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper, Request $request, TranslatorInterface $translator): Response
    {
        $repository = $entityManager->getRepository(Images::class);
        $carrouselImages = $repository->findBy(['homeCarrousel' => 1]);
        // comtpage et stockage du nombre de photos du carrousel présent en DB
        $carrouselCount = $repository->findBy(['homeCarrousel' => 1]);
        $carrouselCount = count($carrouselCount);


        // création du formulaire
        $imgCarrousel = new Images();
        $form = $this->createForm(ImageType::class, $imgCarrousel);
        $form->handleRequest($request);
  
  
         if ($form->isSubmitted() && $form->isValid()) {
  
          $uploadedImage = $form['imageFile']->getData();
  
          if($uploadedImage){
            $newImageName = $uploaderHelper->uploadImages($uploadedImage);
  
            $imgCarrousel->setImage($newImageName);
            $imgCarrousel->setHomeCarrousel(true);
          }
  
          if ($imgCarrousel->getImage() != null ){
            $entityManager->persist($imgCarrousel);
            $entityManager->flush();
  
            $msg = $translator->trans('Votre photo a bien été ajoutée');
            $this->addFlash('success', $msg);
  
            return $this->redirectToRoute('admin_carrousel', [
            ]);
          }
          
          $msg = $translator->trans('Votre image n\'est pas valide');
          $this->addFlash('notice', $msg);
         }
  
        return $this->render('admin_crud/carrousel/carrousel.html.twig', [
            'form' => $form->createView(),
            'carrouselCount' => $carrouselCount,
            'carrouselImages' => $carrouselImages,
        ]);

    }


    // -------------------------------------
    // Supprimer une photo du carrousel
    // -------------------------------------
    /**
     * @Route("/admin/supprimer_image_carrousel/{id}", name="admin_suppr_carrousel")
     */
    public function supprimerPhotoCarrouselAccueil($id, EntityManagerInterface $entityManager, UploaderHelper $uploaderHelper, TranslatorInterface $translator): Response
    {
      
      $repositoryImage = $entityManager->getRepository(Images::class);
      $image = $repositoryImage->find($id);

      $imageToDelete = $image->getImage();

      $entityManager->remove($image);
      $entityManager->flush();

      // suppression du fichier dans le dossier uploads
      $imgToDelete = $uploaderHelper->getUploadPath().'/'.$imageToDelete;
      unlink($imgToDelete);

      $msg = $translator->trans('Votre photo a bien été supprimée');
      $this->addFlash('success', $msg);

      return $this->redirectToRoute('admin_carrousel', [
      ]);
    }

}

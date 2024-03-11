<?php

namespace App\services;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

// Creation d'une classe de gestion des images uploadées
Class UploaderHelper{

    // On récupère le chemin du dossier uploads déclaré dans config/services.yaml
    private $uploadsPath;

    public function __construct(string $uploadsPath){
        $this->uploadsPath = $uploadsPath;
    }

    public function getUploadPath(){
        return $this->uploadsPath;
    }

    public function uploadImages(UploadedFile $uploadedFile): string
    {
        $destination = $this->uploadsPath;

        // Traitement du nom de l'image pour éviter les doublons
        $originalImageName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        // Utilisation d'Urlizer pour créer un nom d'image unique
        $newImageName = Urlizer::urlize($originalImageName).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        // envoi du fichier dans le dossier uploads
        $uploadedFile->move(
        $destination,
        $newImageName
        );

        // On renvoi le nouveau nom de l'image pour pouvoir le stocker en DB
        return $newImageName;
    }

}
<?php

namespace App\DataFixtures;

use App\Entity\Adresses;
use App\Entity\CategoriesAnimaux;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{

    // -------------------------------------------------------------------------------------------------------------
    // - Insertion des localités, communes et code postaux de Belgique 
    //   depuis fichier (public/Json zipcode-belgium.json)
    // - Insertion des catégories d'animaux dans la table categorie_animaux
    // -------------------------------------------------------------------------------------------------------------

    //* Pour executer les fixtures -> php bin/console doctrine:fixtures:load
    //! Attention cette opération purge votre DB avant d'inserer les fixtures

    public function load(ObjectManager $manager): void
    {

        // Récupére depuis le fichier json les communes + code postaux de Belgique
        $json = file_get_contents('public/zipcode-belgium.json');

        // Decoder le fichier json
        $json_data = json_decode($json,true);

        // remplacement é et è par e
        foreach ($json_data as $key => $value) {
            $json_data[$key]['city'] = str_replace('é', 'e', $value['city']);
            $json_data[$key]['city'] = str_replace('è', 'e', $value['city']);
        }


        foreach($json_data as $key => $value){
            $adresse = new Adresses();
            $adresse->setLocalite($value['localite']);
            $adresse->setCommune($value['city']);
            $adresse->setCodePostal($value['zip']);
            $manager->persist($adresse);
        }

        // Ajout catégories animaux domestiques
        $categories= [
            'Chats',
            'Chiens',
            'Poissons',
            'Amphibiens',
            'Ruminants',
            'Rongeurs',
            'Oiseaux',
            'Insectes'
        ];

        foreach ($categories as $category) {
            $categorie = new CategoriesAnimaux();
            $categorie->setNom($category);
            $manager->persist($categorie);
        }

        // Ajout catégories animaux exotiques
        $categoriesExotiques = [
            'Reptiles',
            'Arachnides'
        ];

        foreach ($categoriesExotiques as $category) {
            $categorie = new CategoriesAnimaux();
            $categorie->setNom($category);
            $categorie->setExotique(true);
            $manager->persist($categorie);
        }

        $manager->flush();
    }


}


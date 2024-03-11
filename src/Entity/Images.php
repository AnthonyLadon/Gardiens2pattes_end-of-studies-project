<?php

namespace App\Entity;

use App\Entity\CarrouselAccueil;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ImagesRepository;

#[ORM\Entity(repositoryClass: ImagesRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Images
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Maitres $maitre = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Prestataires $prestataire = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Animaux $animal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'imgGallerie')]
    private ?Prestataires $gallerieGardien = null;

    #[ORM\ManyToOne(inversedBy: 'imgGallerieMaitre')]
    private ?Maitres $gallerieMaitre = null;

    #[ORM\Column(nullable: true)]
    private ?bool $homeCarrousel = null;

    /**
     * @ORM\PreRemove
     */
    //* Supprime aussi le fichier image du projet 
    //* lorsqu'on supprimer l'image de la base de données
    public function preRemove(): void
    {
        // Supprimer l'image du projet
        $filePath = 'public/img/uploads/' . $this->image;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaitre(): ?Maitres
    {
        return $this->maitre;
    }

    public function setMaitre(?Maitres $maitre): self
    {
        $this->maitre = $maitre;

        return $this;
    }

    public function getPrestataire(): ?Prestataires
    {
        return $this->prestataire;
    }

    public function setPrestataire(?Prestataires $prestataire): self
    {
        $this->prestataire = $prestataire;

        return $this;
    }

    public function getAnimal(): ?Animaux
    {
        return $this->animal;
    }

    public function setAnimal(?Animaux $animal): self
    {
        $this->animal = $animal;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getGallerieGardien(): ?Prestataires
    {
        return $this->gallerieGardien;
    }

    public function setGallerieGardien(?Prestataires $gallerieGardien): self
    {
        $this->gallerieGardien = $gallerieGardien;

        return $this;
    }

    public function getGallerieMaitre(): ?Maitres
    {
        return $this->gallerieMaitre;
    }

    public function setGallerieMaitre(?Maitres $gallerieMaitre): self
    {
        $this->gallerieMaitre = $gallerieMaitre;

        return $this;
    }

    public function __toString(): string
    {
        return $this->image;
    }

    public function isHomeCarrousel(): ?bool
    {
        return $this->homeCarrousel;
    }

    public function setHomeCarrousel(?bool $homeCarrousel): self
    {
        $this->homeCarrousel = $homeCarrousel;

        return $this;
    }


}

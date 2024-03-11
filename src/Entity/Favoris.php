<?php

namespace App\Entity;

use App\Repository\FavorisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavorisRepository::class)]
class Favoris
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'favoris')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Maitres $maitres = null;

    #[ORM\ManyToOne(inversedBy: 'favoris')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prestataires $prestataire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaitres(): ?Maitres
    {
        return $this->maitres;
    }

    public function setMaitres(?Maitres $maitres): self
    {
        $this->maitres = $maitres;

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
}

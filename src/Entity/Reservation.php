<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\Column]
    private ?bool $validationPrestataire = null;

    #[ORM\Column]
    private ?bool $paiementOk = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbPassages = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prestataires $gardien = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Maitres $maitre = null;

    #[ORM\Column(nullable: true)]
    private ?int $prixTotal = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hebergement = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Animaux $animal = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePaiement = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbPromenades = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_sent_enquete = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function isValidationPrestataire(): ?bool
    {
        return $this->validationPrestataire;
    }

    public function setValidationPrestataire(bool $validationPrestataire): self
    {
        $this->validationPrestataire = $validationPrestataire;

        return $this;
    }

    public function isPaiementOk(): ?bool
    {
        return $this->paiementOk;
    }

    public function setPaiementOk(bool $paiementOk): self
    {
        $this->paiementOk = $paiementOk;

        return $this;
    }

    public function getNbPassages(): ?int
    {
        return $this->nbPassages;
    }

    public function setNbPassages(?int $nbPassages): self
    {
        $this->nbPassages = $nbPassages;

        return $this;
    }

    public function getGardien(): ?Prestataires
    {
        return $this->gardien;
    }

    public function setGardien(?Prestataires $gardien): self
    {
        $this->gardien = $gardien;

        return $this;
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

    public function getPrixTotal(): ?int
    {
        return $this->prixTotal;
    }

    public function setPrixTotal(?int $prixTotal): self
    {
        $this->prixTotal = $prixTotal;

        return $this;
    }

    public function isHebergement(): ?bool
    {
        return $this->hebergement;
    }

    public function setHebergement(?bool $hebergement): self
    {
        $this->hebergement = $hebergement;

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

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): self
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }

    public function getNbPromenades(): ?int
    {
        return $this->nbPromenades;
    }

    public function setNbPromenades(?int $nbPromenades): self
    {
        $this->nbPromenades = $nbPromenades;

        return $this;
    }

    public function isIsSentEnquete(): ?bool
    {
        return $this->is_sent_enquete;
    }

    public function setIsSentEnquete(?bool $is_sent_enquete): self
    {
        $this->is_sent_enquete = $is_sent_enquete;

        return $this;
    }
}

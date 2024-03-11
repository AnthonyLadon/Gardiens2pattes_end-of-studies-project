<?php

namespace App\Entity;

use App\Repository\SignalementAbusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SignalementAbusRepository::class)]
class SignalementAbus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?bool $estTraite = false;

    #[ORM\ManyToOne(inversedBy: 'signalement')]
    private ?Prestataires $prestataires = null;

    #[ORM\ManyToOne(inversedBy: 'signalement')]
    private ?Commentaires $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function isEstTraite(): ?bool
    {
        return $this->estTraite;
    }

    public function setEstTraite(bool $estTraite): self
    {
        $this->estTraite = $estTraite;

        return $this;
    }

    public function getPrestataires(): ?Prestataires
    {
        return $this->prestataires;
    }

    public function setPrestataires(?Prestataires $prestataires): self
    {
        $this->prestataires = $prestataires;

        return $this;
    }

    public function getCommentaire(): ?Commentaires
    {
        return $this->commentaire;
    }

    public function setCommentaire(?Commentaires $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function __toString(): string
    {
        return $this->date->format('d/m/Y') . ' ' . $this->estTraite;
    }
}

<?php

namespace App\Entity;

use App\Repository\CommentairesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentairesRepository::class)]
class Commentaires
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentaire = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'Commentaire', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prestataires $prestataires = null;

    #[ORM\ManyToOne(inversedBy: 'commentaires', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Maitres $maitre = null;

    #[ORM\OneToMany(mappedBy: 'commentaire', targetEntity: SignalementAbus::class)]
    private Collection $signalement;

    #[ORM\Column(nullable: true)]
    private ?bool $en_avant = false;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ReponseGardien = null;

    public function __construct()
    {
        $this->signalement = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
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

    public function getPrestataires(): ?Prestataires
    {
        return $this->prestataires;
    }

    public function setPrestataires(?Prestataires $prestataires): self
    {
        $this->prestataires = $prestataires;

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

    /**
     * @return Collection<int, SignalementAbus>
     */
    public function getSignalement(): Collection
    {
        return $this->signalement;
    }

    public function addSignalement(SignalementAbus $signalement): self
    {
        if (!$this->signalement->contains($signalement)) {
            $this->signalement->add($signalement);
            $signalement->setCommentaire($this);
        }

        return $this;
    }

    public function removeSignalement(SignalementAbus $signalement): self
    {
        if ($this->signalement->removeElement($signalement)) {
            // set the owning side to null (unless already changed)
            if ($signalement->getCommentaire() === $this) {
                $signalement->setCommentaire(null);
            }
        }

        return $this;
    }

    public function isEnAvant(): ?bool
    {
        return $this->en_avant;
    }

    public function setEnAvant(?bool $en_avant): self
    {
        $this->en_avant = $en_avant;

        return $this;
    }

    public function __toString()
    {
        return $this->getTitre();
    }

    public function getReponseGardien(): ?string
    {
        return $this->ReponseGardien;
    }

    public function setReponseGardien(?string $ReponseGardien): self
    {
        $this->ReponseGardien = $ReponseGardien;

        return $this;
    }
}

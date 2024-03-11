<?php

namespace App\Entity;

use App\Repository\CategoriesAnimauxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriesAnimauxRepository::class)]
class CategoriesAnimaux
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?bool $exotique = false;

    #[ORM\OneToMany(mappedBy: 'categorieAnimal', targetEntity: Animaux::class)]
    private Collection $animaux;

    #[ORM\ManyToMany(targetEntity: Prestataires::class, mappedBy: 'specialisations')]
    private Collection $prestataires;

    public function __construct()
    {
        $this->animaux = new ArrayCollection();
        $this->prestataires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function isExotique(): ?bool
    {
        return $this->exotique;
    }

    public function setExotique(bool $exotique): self
    {
        $this->exotique = $exotique;

        return $this;
    }

    /**
     * @return Collection<int, Animaux>
     */
    public function getAnimaux(): Collection
    {
        return $this->animaux;
    }

    public function addAnimaux(Animaux $animaux): self
    {
        if (!$this->animaux->contains($animaux)) {
            $this->animaux->add($animaux);
            $animaux->setCategorieAnimal($this);
        }

        return $this;
    }

    public function removeAnimaux(Animaux $animaux): self
    {
        if ($this->animaux->removeElement($animaux)) {
            // set the owning side to null (unless already changed)
            if ($animaux->getCategorieAnimal() === $this) {
                $animaux->setCategorieAnimal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Prestataires>
     */
    public function getPrestataires(): Collection
    {
        return $this->prestataires;
    }

    public function addPrestataire(Prestataires $prestataire): self
    {
        if (!$this->prestataires->contains($prestataire)) {
            $this->prestataires->add($prestataire);
            $prestataire->addSpecialisation($this);
        }

        return $this;
    }

    public function removePrestataire(Prestataires $prestataire): self
    {
        if ($this->prestataires->removeElement($prestataire)) {
            $prestataire->removeSpecialisation($this);
        }

        return $this;
    }

}

<?php

namespace App\Entity;

use App\Entity\Commentaires;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MaitresRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: MaitresRepository::class)]
class Maitres
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\Column]
    private ?bool $newsletter = false;

    #[ORM\OneToMany(mappedBy: 'maitre', targetEntity: Animaux::class, orphanRemoval: true)]
    private Collection $animal;

    #[ORM\OneToOne(mappedBy: 'maitre', cascade: ['persist', 'remove'])]
    private ?Utilisateurs $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'maitre', targetEntity: Images::class)]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'maitre', targetEntity: Commentaires::class, orphanRemoval: true)]
    private Collection $Commentaire;

    #[ORM\OneToMany(mappedBy: 'maitres', targetEntity: Favoris::class, orphanRemoval: true)]
    private Collection $favoris;

    #[ORM\OneToMany(mappedBy: 'gallerieMaitre', targetEntity: Images::class)]
    private Collection $imgGallerieMaitre;

    #[ORM\OneToMany(mappedBy: 'maitre', targetEntity: NotesGardien::class, orphanRemoval: true)]
    private Collection $notesGardiens;

    #[ORM\OneToMany(mappedBy: 'maitre', targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->animal = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->imgGallerieMaitre = new ArrayCollection();
        $this->notesGardiens = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function isNewsletter(): ?bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): self
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * @return Collection<int, Animaux>
     */
    public function getAnimal(): Collection
    {
        return $this->animal;
    }

    public function addAnimal(Animaux $animal): self
    {
        if (!$this->animal->contains($animal)) {
            $this->animal->add($animal);
            $animal->setMaitre($this);
        }

        return $this;
    }

    public function removeAnimal(Animaux $animal): self
    {
        if ($this->animal->removeElement($animal)) {
            // set the owning side to null (unless already changed)
            if ($animal->getMaitre() === $this) {
                $animal->setMaitre(null);
            }
        }

        return $this;
    }

    public function getUtilisateur(): ?Utilisateurs
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateurs $utilisateur): self
    {
        // unset the owning side of the relation if necessary
        if ($utilisateur === null && $this->utilisateur !== null) {
            $this->utilisateur->setMaitre(null);
        }

        // set the owning side of the relation if necessary
        if ($utilisateur !== null && $utilisateur->getMaitre() !== $this) {
            $utilisateur->setMaitre($this);
        }

        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Images>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setMaitre($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getMaitre() === $this) {
                $image->setMaitre(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Commentaires>
     */
    public function getCommentaire(): Collection
    {
        return $this->Commentaire;
    }

    public function addCommentaire(Commentaires $commentaire): self
    {
        if (!$this->Commentaire->contains($commentaire)) {
            $this->Commentaire->add($commentaire);
            $commentaire->setMaitres($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaires $commentaire): self
    {
        if ($this->Commentaire->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getMaitres() === $this) {
                $commentaire->setMaitres(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Favoris>
     */
    public function getFavoris(): Collection
    {
        return $this->favoris;
    }

    public function addFavori(Favoris $favori): self
    {
        if (!$this->favoris->contains($favori)) {
            $this->favoris->add($favori);
            $favori->setMaitres($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): self
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getMaitres() === $this) {
                $favori->setMaitres(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Images>
     */
    public function getImgGallerieMaitre(): Collection
    {
        return $this->imgGallerieMaitre;
    }

    public function addImgGallerieMaitre(Images $imgGallerieMaitre): self
    {
        if (!$this->imgGallerieMaitre->contains($imgGallerieMaitre)) {
            $this->imgGallerieMaitre->add($imgGallerieMaitre);
            $imgGallerieMaitre->setGallerieMaitre($this);
        }

        return $this;
    }

    public function removeImgGallerieMaitre(Images $imgGallerieMaitre): self
    {
        if ($this->imgGallerieMaitre->removeElement($imgGallerieMaitre)) {
            // set the owning side to null (unless already changed)
            if ($imgGallerieMaitre->getGallerieMaitre() === $this) {
                $imgGallerieMaitre->setGallerieMaitre(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NotesGardien>
     */
    public function getNotesGardiens(): Collection
    {
        return $this->notesGardiens;
    }

    public function addNotesGardien(NotesGardien $notesGardien): self
    {
        if (!$this->notesGardiens->contains($notesGardien)) {
            $this->notesGardiens->add($notesGardien);
            $notesGardien->setMaitre($this);
        }

        return $this;
    }

    public function removeNotesGardien(NotesGardien $notesGardien): self
    {
        if ($this->notesGardiens->removeElement($notesGardien)) {
            // set the owning side to null (unless already changed)
            if ($notesGardien->getMaitre() === $this) {
                $notesGardien->setMaitre(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setMaitre($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getMaitre() === $this) {
                $reservation->setMaitre(null);
            }
        }

        return $this;
    }

}

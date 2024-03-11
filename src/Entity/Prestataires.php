<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PrestatairesRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: PrestatairesRepository::class)]
class Prestataires
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $Iban = null;

    #[ORM\Column]
    private ?bool $gardeDomicile = false;

    #[ORM\Column]
    private ?bool $vehicule = false;

    #[ORM\Column]
    private ?bool $jardin = false;

    #[ORM\Column(nullable: true)]
    private ?int $tarif = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bio = null;

    #[ORM\ManyToMany(targetEntity: CategoriesAnimaux::class, inversedBy: 'prestataires')]
    private Collection $specialisations;

    #[ORM\OneToMany(mappedBy: 'prestataires', targetEntity: Indisponibilites::class)]
    private Collection $indisponibilite;

    #[ORM\OneToMany(mappedBy: 'prestataires', targetEntity: Commentaires::class, orphanRemoval: true)]
    private Collection $Commentaire;

    #[ORM\OneToMany(mappedBy: 'prestataires', targetEntity: SignalementAbus::class)]
    private Collection $signalement;

    #[ORM\OneToMany(mappedBy: 'prestataire', targetEntity: Images::class)]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'prestataire', targetEntity: Favoris::class, orphanRemoval: true)]
    private Collection $favoris;

    #[ORM\Column(nullable: true)]
    private ?bool $soins_veto = null;

    #[ORM\Column(nullable: true)]
    private ?int $zoneGardiennage = null;

    #[ORM\Column(nullable: true)]
    private ?int $tarif_deplacement = null;

    #[ORM\OneToOne(mappedBy: 'prestataire', cascade: ['persist', 'remove'])]
    private ?Utilisateurs $utilisateur = null;

    #[ORM\OneToMany(mappedBy: 'gallerieGardien', targetEntity: Images::class)]
    private Collection $imgGallerie;

    #[ORM\OneToMany(mappedBy: 'gardien', targetEntity: NotesGardien::class, orphanRemoval: true)]
    private Collection $notesGardiens;

    #[ORM\OneToMany(mappedBy: 'gardien', targetEntity: Reservation::class)]
    private Collection $reservations;

    #[ORM\Column(nullable: true)]
    private ?int $tarif_promenade = null;

    #[ORM\Column(nullable: true)]
    private ?string $stripeAccountId = null;

    public function __construct()
    {
        $this->specialisations = new ArrayCollection();
        $this->indisponibilite = new ArrayCollection();
        $this->Commentaire = new ArrayCollection();
        $this->signalement = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->favoris = new ArrayCollection();
        $this->imgGallerie = new ArrayCollection();
        $this->notesGardiens = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIban(): ?string
    {
        return $this->Iban;
    }

    public function setIban(?string $Iban): self
    {
        $this->Iban = $Iban;

        return $this;
    }

    public function isGardeDomicile(): ?bool
    {
        return $this->gardeDomicile;
    }

    public function setGardeDomicile(bool $gardeDomicile): self
    {
        $this->gardeDomicile = $gardeDomicile;

        return $this;
    }

    public function isVehicule(): ?bool
    {
        return $this->vehicule;
    }

    public function setVehicule(bool $vehicule): self
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    public function isJardin(): ?bool
    {
        return $this->jardin;
    }

    public function setJardin(bool $jardin): self
    {
        $this->jardin = $jardin;

        return $this;
    }

    public function getTarif(): ?int
    {
        return $this->tarif;
    }

    public function setTarif(?int $tarif): self
    {
        $this->tarif = $tarif;

        return $this;
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


    /**
     * @return Collection<int, CategoriesAnimaux>
     */
    public function getSpecialisations(): Collection
    {
        return $this->specialisations;
    }

    public function addSpecialisation(CategoriesAnimaux $specialisation): self
    {
        if (!$this->specialisations->contains($specialisation)) {
            $this->specialisations->add($specialisation);
        }

        return $this;
    }

    public function removeSpecialisation(CategoriesAnimaux $specialisation): self
    {
        $this->specialisations->removeElement($specialisation);

        return $this;
    }

    /**
     * @return Collection<int, Indisponibilites>
     */
    public function getIndisponibilite(): Collection
    {
        return $this->indisponibilite;
    }

    public function addIndisponibilite(Indisponibilites $indisponibilite): self
    {
        if (!$this->indisponibilite->contains($indisponibilite)) {
            $this->indisponibilite->add($indisponibilite);
            $indisponibilite->setPrestataires($this);
        }

        return $this;
    }

    public function removeIndisponibilite(Indisponibilites $indisponibilite): self
    {
        if ($this->indisponibilite->removeElement($indisponibilite)) {
            // set the owning side to null (unless already changed)
            if ($indisponibilite->getPrestataires() === $this) {
                $indisponibilite->setPrestataires(null);
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
            $commentaire->setPrestataires($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaires $commentaire): self
    {
        if ($this->Commentaire->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getPrestataires() === $this) {
                $commentaire->setPrestataires(null);
            }
        }

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
            $signalement->setPrestataires($this);
        }

        return $this;
    }

    public function removeSignalement(SignalementAbus $signalement): self
    {
        if ($this->signalement->removeElement($signalement)) {
            // set the owning side to null (unless already changed)
            if ($signalement->getPrestataires() === $this) {
                $signalement->setPrestataires(null);
            }
        }

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
            $image->setPrestataire($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getPrestataire() === $this) {
                $image->setPrestataire(null);
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
            $favori->setPrestataire($this);
        }

        return $this;
    }

    public function removeFavori(Favoris $favori): self
    {
        if ($this->favoris->removeElement($favori)) {
            // set the owning side to null (unless already changed)
            if ($favori->getPrestataire() === $this) {
                $favori->setPrestataire(null);
            }
        }

        return $this;
    }

    public function isSoinsVeto(): ?bool
    {
        return $this->soins_veto;
    }

    public function setSoinsVeto(?bool $soins_veto): self
    {
        $this->soins_veto = $soins_veto;

        return $this;
    }

    public function getZoneGardiennage(): ?int
    {
        return $this->zoneGardiennage;
    }

    public function setZoneGardiennage(?int $zoneGardiennage): self
    {
        $this->zoneGardiennage = $zoneGardiennage;

        return $this;
    }

    public function getTarifDeplacement(): ?int
    {
        return $this->tarif_deplacement;
    }

    public function setTarifDeplacement(?int $tarif_deplacement): self
    {
        $this->tarif_deplacement = $tarif_deplacement;

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
            $this->utilisateur->setPrestataire(null);
        }

        // set the owning side of the relation if necessary
        if ($utilisateur !== null && $utilisateur->getPrestataire() !== $this) {
            $utilisateur->setPrestataire($this);
        }

        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Images>
     */
    public function getImgGallerie(): Collection
    {
        return $this->imgGallerie;
    }

    public function addImgGallerie(Images $imgGallerie): self
    {
        if (!$this->imgGallerie->contains($imgGallerie)) {
            $this->imgGallerie->add($imgGallerie);
            $imgGallerie->setGallerieGardien($this);
        }

        return $this;
    }

    public function removeImgGallerie(Images $imgGallerie): self
    {
        if ($this->imgGallerie->removeElement($imgGallerie)) {
            // set the owning side to null (unless already changed)
            if ($imgGallerie->getGallerieGardien() === $this) {
                $imgGallerie->setGallerieGardien(null);
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
            $notesGardien->setGardien($this);
        }

        return $this;
    }

    public function removeNotesGardien(NotesGardien $notesGardien): self
    {
        if ($this->notesGardiens->removeElement($notesGardien)) {
            // set the owning side to null (unless already changed)
            if ($notesGardien->getGardien() === $this) {
                $notesGardien->setGardien(null);
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
            $reservation->setGardien($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getGardien() === $this) {
                $reservation->setGardien(null);
            }
        }

        return $this;
    }

    public function getTarifPromenade(): ?int
    {
        return $this->tarif_promenade;
    }

    public function setTarifPromenade(?int $tarif_promenade): self
    {
        $this->tarif_promenade = $tarif_promenade;

        return $this;
    }

    public function getStripeAccountId(): ?string
    {
        return $this->stripeAccountId;
    }

    public function setStripeAccountId(?string $stripeAccountId): self
    {
        $this->stripeAccountId = $stripeAccountId;

        return $this;
    }

}

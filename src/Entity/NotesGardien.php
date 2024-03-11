<?php

namespace App\Entity;

use App\Repository\NotesGardienRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotesGardienRepository::class)]
class NotesGardien
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $note = null;

    #[ORM\ManyToOne(inversedBy: 'notesGardiens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Prestataires $gardien = null;

    #[ORM\ManyToOne(inversedBy: 'notesGardiens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Maitres $maitre = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;

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
}

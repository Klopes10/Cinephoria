<?php
// src/Entity/Qualite.php

namespace App\Entity;

use App\Repository\QualiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QualiteRepository::class)]
#[ORM\Table(name: 'qualite')]
class Qualite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    // Stockage DECIMAL: on garde string en interne
    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    private ?string $prix = null;

    /** @var Collection<int, Seance> */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'qualite')]
    private Collection $seances;

    public function __construct()
    {
        $this->seances = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getLabel(): ?string { return $this->label; }
    public function setLabel(string $label): self { $this->label = $label; return $this; }

    public function getPrix(): ?float { return $this->prix !== null ? (float)$this->prix : null; }
    public function setPrix(float|string $prix): self { $this->prix = (string)$prix; return $this; }

    /** @return Collection<int, Seance> */
    public function getSeances(): Collection { return $this->seances; }

    public function addSeance(Seance $seance): self
    {
        if (!$this->seances->contains($seance)) {
            $this->seances->add($seance);
            $seance->setQualite($this);
        }
        return $this;
    }

    public function removeSeance(Seance $seance): self
    {
        if ($this->seances->removeElement($seance)) {
            if ($seance->getQualite() === $this) {
                $seance->setQualite(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return (string)($this->label ?? 'Qualité');
    }
}

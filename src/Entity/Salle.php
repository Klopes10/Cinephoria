<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $nombrePlaces = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /** @var Collection<int, Seance> */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'salle')]
    private Collection $seances;

    /** @var Collection<int, Incident> */
    #[ORM\OneToMany(targetEntity: Incident::class, mappedBy: 'salle')]
    private Collection $incidents;

    #[ORM\ManyToOne(inversedBy: 'salles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    
    #[ORM\ManyToOne(targetEntity: Qualite::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Qualite $qualite = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->seances = new ArrayCollection();
        $this->incidents = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getNombrePlaces(): ?int { return $this->nombrePlaces; }
    public function setNombrePlaces(int $nombrePlaces): static { $this->nombrePlaces = $nombrePlaces; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    /** @return Collection<int, Seance> */
    public function getSeances(): Collection { return $this->seances; }
    public function addSeance(Seance $seance): static
    {
        if (!$this->seances->contains($seance)) {
            $this->seances->add($seance);
            $seance->setSalle($this);
        }
        return $this;
    }
    public function removeSeance(Seance $seance): static
    {
        if ($this->seances->removeElement($seance) && $seance->getSalle() === $this) {
            $seance->setSalle(null);
        }
        return $this;
    }

    /** @return Collection<int, Incident> */
    public function getIncidents(): Collection { return $this->incidents; }
    public function addIncident(Incident $incident): static
    {
        if (!$this->incidents->contains($incident)) {
            $this->incidents->add($incident);
            $incident->setSalle($this);
        }
        return $this;
    }
    public function removeIncident(Incident $incident): static
    {
        if ($this->incidents->removeElement($incident) && $incident->getSalle() === $this) {
            $incident->setSalle(null);
        }
        return $this;
    }

    public function getCinema(): ?Cinema { return $this->cinema; }
    public function setCinema(?Cinema $cinema): static { $this->cinema = $cinema; return $this; }

    public function getQualite(): ?Qualite { return $this->qualite; }
    public function setQualite(?Qualite $qualite): static { $this->qualite = $qualite; return $this; }

    public function __toString(): string
    {
        return $this->getNom();
    }
}

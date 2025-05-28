<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateHeureDebut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateHeureFin = null;

    #[ORM\Column(length: 255)]
    private ?string $qualite = null;

    #[ORM\Column]
    private ?int $placesDisponible = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'seances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Film $film = null;

    #[ORM\ManyToOne(inversedBy: 'seances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $salle = null;

    #[ORM\ManyToOne(inversedBy: 'seances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    #[ORM\Column]
    private ?float $prix = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'seance', cascade: ['remove'])]
    private Collection $reservations;

    /**
     * @var Collection<int, Siege>
     */
    #[ORM\OneToMany(targetEntity: Siege::class, mappedBy: 'seance', cascade: ['persist', 'remove'])]
    private Collection $sieges;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->reservations = new ArrayCollection();
        $this->sieges = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getDateHeureDebut(): ?\DateTimeImmutable { return $this->dateHeureDebut; }

    public function setDateHeureDebut(\DateTimeImmutable $dateHeureDebut): static
    {
        $this->dateHeureDebut = $dateHeureDebut;
        return $this;
    }

    public function getDateHeureFin(): ?\DateTimeImmutable { return $this->dateHeureFin; }

    public function setDateHeureFin(\DateTimeImmutable $dateHeureFin): static
    {
        $this->dateHeureFin = $dateHeureFin;
        return $this;
    }

    public function getQualite(): ?string { return $this->qualite; }

    public function setQualite(string $qualite): static
    {
        $this->qualite = $qualite;
        return $this;
    }

    public function getPlacesDisponible(): ?int { return $this->placesDisponible; }

    public function setPlacesDisponible(int $placesDisponible): static
    {
        $this->placesDisponible = $placesDisponible;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getFilm(): ?Film { return $this->film; }

    public function setFilm(?Film $film): static
    {
        $this->film = $film;
        return $this;
    }

    public function getSalle(): ?Salle { return $this->salle; }

    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;
        return $this;
    }

    public function getCinema(): ?Cinema { return $this->cinema; }

    public function setCinema(?Cinema $cinema): static
    {
        $this->cinema = $cinema;
        return $this;
    }

    public function getPrix(): ?float { return $this->prix; }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection { return $this->reservations; }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setSeance($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getSeance() === $this) {
                $reservation->setSeance(null);
            }
        }
        return $this;
    }

    public function getNombrePlacesSalle(): ?int
    {
        return $this->salle?->getNombrePlaces();
    }

    public function __toString(): string
    {
        return 'Séance ' . $this->id;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setQualiteFromSalle(): void
    {
        if ($this->salle !== null) {
            $this->qualite = $this->salle->getQualite();
        }
    }

    #[ORM\PrePersist]
    public function setPlacesFromSalle(): void
    {
        if ($this->salle !== null && $this->placesDisponible === null) {
            $this->placesDisponible = $this->salle->getNombrePlaces();
        }
    }

    /**
     * @return Collection<int, Siege>
     */
    public function getSieges(): Collection
    {
        return $this->sieges;
    }

    public function addSiege(Siege $siege): static
    {
        if (!$this->sieges->contains($siege)) {
            $this->sieges->add($siege);
            $siege->setSeance($this);
        }

        return $this;
    }

    public function removeSiege(Siege $siege): static
    {
        if ($this->sieges->removeElement($siege)) {
            if ($siege->getSeance() === $this) {
                $siege->setSeance(null);
            }
        }

        return $this;
    }
}

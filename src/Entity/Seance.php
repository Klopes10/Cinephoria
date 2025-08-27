<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: 'time_immutable')]
    private ?\DateTimeImmutable $heureDebut = null;

    #[ORM\Column(type: 'time_immutable')]
    private ?\DateTimeImmutable $heureFin = null;

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

    /** @var Collection<int, Reservation> */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'seance', cascade: ['remove'])]
    private Collection $reservations;

    /** @var Collection<int, Siege> */
    #[ORM\OneToMany(targetEntity: Siege::class, mappedBy: 'seance', cascade: ['persist', 'remove'])]
    private Collection $sieges;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->reservations = new ArrayCollection();
        $this->sieges = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getDate(): ?\DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): static { $this->date = $date; return $this; }

    public function getHeureDebut(): ?\DateTimeImmutable { return $this->heureDebut; }
    public function setHeureDebut(\DateTimeImmutable $heureDebut): static { $this->heureDebut = $heureDebut; return $this; }

    public function getHeureFin(): ?\DateTimeImmutable { return $this->heureFin; }
    public function setHeureFin(\DateTimeImmutable $heureFin): static { $this->heureFin = $heureFin; return $this; }

    public function getPlacesDisponible(): ?int { return $this->placesDisponible; }
    public function setPlacesDisponible(int $placesDisponible): static { $this->placesDisponible = $placesDisponible; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getFilm(): ?Film { return $this->film; }
    public function setFilm(?Film $film): static { $this->film = $film; return $this; }

    public function getSalle(): ?Salle { return $this->salle; }
    public function setSalle(?Salle $salle): static
    {
        $this->salle = $salle;

        // Init/maj des places si non renseigné
        if ($salle && $this->placesDisponible === null) {
            $this->placesDisponible = $salle->getNombrePlaces();
        }

        return $this;
    }

    public function getCinema(): ?Cinema { return $this->cinema; }
    public function setCinema(?Cinema $cinema): static { $this->cinema = $cinema; return $this; }

    /** Qualité dérivée de la salle (utile pour l’UI/admin) */
    public function getQualite(): ?Qualite
    {
        return $this->salle?->getQualite();
    }

    /** Prix dérivé de la qualité de la salle */
    public function getPrix(): ?float
    {
        return $this->getQualite()?->getPrix();
    }

    /** @return Collection<int, Reservation> */
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
        if ($this->reservations->removeElement($reservation) && $reservation->getSeance() === $this) {
            $reservation->setSeance(null);
        }
        return $this;
    }

    public function getNombrePlacesSalle(): ?int
    {
        return $this->salle?->getNombrePlaces();
    }

    public function __toString(): string
    {
        return 'Séance ' . ($this->id ?? '');
    }

    #[ORM\PrePersist]
    public function setPlacesFromSalle(): void
    {
        if ($this->salle !== null && $this->placesDisponible === null) {
            $this->placesDisponible = $this->salle->getNombrePlaces();
        }
    }

    /** Validation : la salle doit appartenir au cinéma choisi */
    #[Assert\Callback]
    public function validateCinemaSalle(ExecutionContextInterface $ctx): void
    {
        if ($this->cinema && $this->salle && $this->salle->getCinema() !== $this->cinema) {
            $ctx->buildViolation('La salle sélectionnée n’appartient pas au cinéma choisi.')
                ->atPath('salle')
                ->addViolation();
        }
    }

    /** @return Collection<int, Siege> */
    public function getSieges(): Collection { return $this->sieges; }

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
        if ($this->sieges->removeElement($siege) && $siege->getSeance() === $this) {
            $siege->setSeance(null);
        }
        return $this;
    }
}

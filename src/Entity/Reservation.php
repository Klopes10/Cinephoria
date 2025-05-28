<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $nombrePlaces = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $placesAttribuees = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Seance $seance = null;

    #[ORM\Column(type: 'float')]
    private ?float $prixTotal = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function updateSeanceAndTotal(): void
    {
        if ($this->seance !== null && $this->nombrePlaces !== null) {
            $placesRestantes = $this->seance->getPlacesDisponible();
            $placesDemandées = $this->nombrePlaces;

            if ($placesRestantes < $placesDemandées) {
                throw new \Exception("Il ne reste que {$placesRestantes} places disponibles pour cette séance.");
            }

            $this->seance->setPlacesDisponible($placesRestantes - $placesDemandées);
            $this->prixTotal = $this->seance->getPrix() * $placesDemandées;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombrePlaces(): ?int
    {
        return $this->nombrePlaces;
    }

    public function setNombrePlaces(int $nombrePlaces): static
    {
        $this->nombrePlaces = $nombrePlaces;
        return $this;
    }

    public function getPlacesAttribuees(): ?string
    {
        return $this->placesAttribuees;
    }

    public function setPlacesAttribuees(?string $placesAttribuees): static
    {
        $this->placesAttribuees = $placesAttribuees;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getSeance(): ?Seance
    {
        return $this->seance;
    }

    public function setSeance(?Seance $seance): static
    {
        $this->seance = $seance;
        return $this;
    }

    public function getPrixTotal(): ?float
    {
        return $this->prixTotal;
    }

    public function setPrixTotal(float $prixTotal): static
    {
        $this->prixTotal = $prixTotal;
        return $this;
    }
}

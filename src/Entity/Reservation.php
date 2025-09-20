<?php

namespace App\Entity;

use App\Entity\Siege;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;

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

    #[ORM\ManyToMany(targetEntity: Siege::class)]
    #[ORM\JoinTable(name: 'reservation_siege')]
    private Collection $sieges;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->sieges = new ArrayCollection();
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

        // Marquer les sièges comme réservés
        foreach ($this->getSieges() as $siege) {
            $siege->setIsReserved(true);
        }
    }

    #[Assert\Callback]
    public function validateSiegesCount(ExecutionContextInterface $context, $payload): void
    {
        if ($this->nombrePlaces !== null && count($this->sieges) !== $this->nombrePlaces) {
            $context->buildViolation('Le nombre de sièges sélectionnés doit correspondre au nombre de places.')
                ->atPath('sieges')
                ->addViolation();
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

    public function getSieges(): Collection
    {
        return $this->sieges;
    }

    public function addSiege(Siege $siege): static
    {
        if (!$this->sieges->contains($siege)) {
            $this->sieges[] = $siege;
        }

        return $this;
    }

    public function removeSiege(Siege $siege): static
    {
        $this->sieges->removeElement($siege);
        return $this;
    }

    public function getSiegesString(): string
    {
        return implode(', ', $this->getSieges()->map(fn(Siege $s) => $s->getNumero())->toArray());
    }


    public function __toString(): string
    {
        return 'Réservation #' . $this->id;
    }
}

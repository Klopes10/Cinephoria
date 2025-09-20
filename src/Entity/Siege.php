<?php

namespace App\Entity;

use App\Repository\SiegeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiegeRepository::class)]
#[ORM\Table(name: 'siege')]
class Siege
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Numéro séquentiel (1,2,3,...) */
    #[ORM\Column]
    private ?int $numero = null;

    /** Code lisible (A1, A2, B7, ...) */
    #[ORM\Column(length: 10)]
    private ?string $code = null;

    /** Siège PMR ? */
    #[ORM\Column(type: 'boolean')]
    private bool $isPMR = false;

    /** Réservé ? */
    #[ORM\Column(type: 'boolean')]
    private bool $isReserved = false;

    #[ORM\ManyToOne(inversedBy: 'sieges')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Seance $seance = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = strtoupper(trim($code));
        return $this;
    }

    public function isPMR(): bool
    {
        return $this->isPMR;
    }

    public function setIsPMR(bool $isPMR): self
    {
        $this->isPMR = $isPMR;
        return $this;
    }

    public function isReserved(): bool
    {
        return $this->isReserved;
    }

    public function setIsReserved(bool $isReserved): self
    {
        $this->isReserved = $isReserved;
        return $this;
    }

    public function getSeance(): ?Seance
    {
        return $this->seance;
    }

    public function setSeance(?Seance $seance): self
    {
        $this->seance = $seance;
        return $this;
    }

    public function __toString(): string
    {
        return $this->code ?: (string) $this->numero;
    }
}

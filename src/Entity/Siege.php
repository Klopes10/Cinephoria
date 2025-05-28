<?php

namespace App\Entity;

use App\Repository\SiegeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiegeRepository::class)]
class Siege
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $numero;

    #[ORM\Column(options: ["default" => false])]
    private bool $isPMR = false;

    #[ORM\Column(options: ["default" => false])]
    private bool $isReserved = false;

    #[ORM\ManyToOne(inversedBy: 'sieges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Seance $seance = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): static
    {
        $this->numero = $numero;
        return $this;
    }

    public function isPMR(): bool
    {
        return $this->isPMR;
    }

    public function setIsPMR(bool $isPMR): static
    {
        $this->isPMR = $isPMR;
        return $this;
    }

    public function isReserved(): bool
    {
        return $this->isReserved;
    }

    public function setIsReserved(bool $isReserved): static
    {
        $this->isReserved = $isReserved;
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
}

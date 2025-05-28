<?php

namespace App\Entity;

use App\Repository\FilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $synopsis = null;

    #[ORM\Column]
    private ?int $ageMinimum = null;

    #[ORM\Column(length: 255)]
    private ?string $affiche = null;

    #[ORM\Column]
    private ?bool $coupDeCoeur = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $noteMoyenne = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Seance>
     */
    #[ORM\OneToMany(targetEntity: Seance::class, mappedBy: 'film', orphanRemoval: true, cascade: ['persist'])]
    private Collection $seances;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'film', orphanRemoval: true, cascade: ['persist'])]
    private Collection $avis;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->seances = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): static
    {
        $this->synopsis = $synopsis;
        return $this;
    }

    public function getAgeMinimum(): ?int
    {
        return $this->ageMinimum;
    }

    public function setAgeMinimum(int $ageMinimum): static
    {
        $this->ageMinimum = $ageMinimum;
        return $this;
    }

    public function getAffiche(): ?string
    {
        return $this->affiche;
    }

    public function setAffiche(string $affiche): static
    {
        $this->affiche = $affiche;
        return $this;
    }

    public function isCoupDeCoeur(): ?bool
    {
        return $this->coupDeCoeur;
    }

    public function setCoupDeCoeur(bool $coupDeCoeur): static
    {
        $this->coupDeCoeur = $coupDeCoeur;
        return $this;
    }

    public function getNoteMoyenne(): ?float
    {
        return $this->noteMoyenne;
    }

    public function setNoteMoyenne(?float $noteMoyenne): static
    {
        $this->noteMoyenne = $noteMoyenne;
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

    /**
     * @return Collection<int, Seance>
     */
    public function getSeances(): Collection
    {
        return $this->seances;
    }

    public function addSeance(Seance $seance): static
    {
        if (!$this->seances->contains($seance)) {
            $this->seances->add($seance);
            $seance->setFilm($this);
        }
        return $this;
    }

    public function removeSeance(Seance $seance): static
    {
        if ($this->seances->removeElement($seance)) {
            if ($seance->getFilm() === $this) {
                $seance->setFilm(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setFilm($this);
        }
        return $this;
    }

    public function removeAvi(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            if ($avi->getFilm() === $this) {
                $avi->setFilm(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->titre;
    }
}

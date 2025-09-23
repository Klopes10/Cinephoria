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

    #[ORM\Column(nullable: true)]
    private ?int $ageMinimum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $affiche = null;

    #[ORM\Column]
    private ?bool $coupDeCoeur = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $noteMoyenne = null;

    
    #[ORM\Column]
    private ?\DateTimeImmutable $datePublication = null;

    #[ORM\ManyToOne(targetEntity: Genre::class, inversedBy: 'films')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Genre $genre = null;

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
        
        $this->datePublication = new \DateTimeImmutable();
        $this->seances = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->coupDeCoeur = false;
    }

    public function __toString(): string
    {
        return (string) $this->titre;
    }

    public function getId(): ?int { return $this->id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = $titre; return $this; }

    public function getSynopsis(): ?string { return $this->synopsis; }
    public function setSynopsis(string $synopsis): static { $this->synopsis = $synopsis; return $this; }

    public function getAgeMinimum(): ?int { return $this->ageMinimum; }
    public function setAgeMinimum(?int $ageMinimum): static { $this->ageMinimum = $ageMinimum; return $this; }

    public function getAffiche(): ?string { return $this->affiche; }
    public function setAffiche(?string $affiche): static { $this->affiche = $affiche; return $this; }

    public function isCoupDeCoeur(): ?bool { return $this->coupDeCoeur; }
    public function setCoupDeCoeur(bool $coupDeCoeur): static { $this->coupDeCoeur = $coupDeCoeur; return $this; }

    public function getNoteMoyenne(): ?float { return $this->noteMoyenne; }
    public function setNoteMoyenne(?float $noteMoyenne): static { $this->noteMoyenne = $noteMoyenne; return $this; }

   
    public function getDatePublication(): ?\DateTimeImmutable { return $this->datePublication; }
    public function setDatePublication(\DateTimeImmutable $datePublication): static { $this->datePublication = $datePublication; return $this; }

    public function getGenre(): ?Genre { return $this->genre; }
    public function setGenre(?Genre $genre): static { $this->genre = $genre; return $this; }

    /** @return Collection<int, Seance> */
    public function getSeances(): Collection { return $this->seances; }

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

    /** @return Collection<int, Avis> */
    public function getAvis(): Collection { return $this->avis; }

    public function addAvis(Avis $avis): static
    {
        if (!$this->avis->contains($avis)) {
            $this->avis->add($avis);
            $avis->setFilm($this);
        }
        return $this;
    }

    public function removeAvis(Avis $avis): static
    {
        if ($this->avis->removeElement($avis)) {
            if ($avis->getFilm() === $this) {
                $avis->setFilm(null);
            }
        }
        return $this;
    }
}

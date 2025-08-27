<?php

namespace App\DataFixtures;

use App\Entity\Cinema;
use App\Entity\Salle;
use App\Entity\Genre;
use App\Entity\Film;
use App\Entity\Seance;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer un cinéma
        $cinema = new Cinema();
        $cinema->setNom('Cinéphoria Bordeaux');
        $cinema->setVille('Bordeaux');
        $cinema->setPays('France');
        $cinema->setAdresse("1 rue principale");
        $cinema->setCodePostal("67350");
        $manager->persist($cinema);

        // Créer une salle
        $salle = new Salle();
        $salle->setNom('Salle 1');
        $salle->setNombrePlaces(20);
        $salle->setQualite('4K');
        $salle->setCreatedAt(new \DateTimeImmutable());
        $salle->setCinema($cinema);
        $manager->persist($salle);

          // Créer un genre
          $genre = new Genre();
          $genre->setNom('Science-Fiction');
          $manager->persist($genre);

        // Créer un film
        $film = new Film();
        $film->setTitre('Interstellar');
        $film->setSynopsis('Exploration d\'un trou noir.');
        $film->setAffiche('interstellar.jpg');
        $film->setAgeMinimum(10);
        $film->setCoupDeCoeur(true);
        $film->setNoteMoyenne(4.7);
        $film->setCreatedAt(new \DateTimeImmutable());
        $film->setGenre($genre); // ← AJOUT OBLIGATOIRE
        $manager->persist($film);

      

        // Créer une séance
        $seance = new Seance();
        $seance->setDate(new \DateTimeImmutable('2025-05-25'));
        $seance->setHeureDebut(new \DateTimeImmutable('14:00'));
        $seance->setHeureFin(new \DateTimeImmutable('16:30'));
        $seance->setQualite('4K');
        $seance->setPlacesDisponible($salle->getNombrePlaces());
        $seance->setCreatedAt(new \DateTimeImmutable());
        $seance->setPrix(11.90);
        $seance->setSalle($salle);
        $seance->setCinema($cinema);
        $seance->setFilm($film);
        $manager->persist($seance);

        // Enregistre en base
        $manager->flush();
    }
}

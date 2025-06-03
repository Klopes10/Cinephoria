<?php

namespace App\DataFixtures;

use App\Entity\Cinema;
use App\Entity\Salle;
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
        $cinema->setPays('France'); // ← AJOUT OBLIGATOIRE
        $cinema->setAdresse("1 rue principale");
        $cinema->setCodePostal("67350");
        $manager->persist($cinema);

        // Créer une salle liée à ce cinéma
        $salle = new Salle();
        $salle->setNom('Salle 1');
        $salle->setNombrePlaces(20);
        $salle->setQualite('4K');
        $salle->setCreatedAt(new \DateTimeImmutable());
        $salle->setCinema($cinema);
        $manager->persist($salle);

        // Créer un film (facultatif mais utile)
        $film = new Film();
        $film->setTitre('Interstellar');
        $film->setSynopsis('Exploration d\'un trou noir.');
        $film->setAffiche('interstellar.jpg');
        $film->setAgeMinimum(10);
        $film->setCoupDeCoeur(true);
        $film->setNoteMoyenne(4.7);
        $film->setCreatedAt(new \DateTimeImmutable());
        $manager->persist($film);
        
        // Créer une séance pour ce film dans cette salle
        $seance = new Seance();
        $seance->setDateHeureDebut(new \DateTimeImmutable('2025-05-25 14:00:00'));
        $seance->setDateHeureFin(new \DateTimeImmutable('2025-05-25 16:30:00'));
        $seance->setQualite('4K');
        $seance->setPlacesDisponible($salle->getNombrePlaces());
        $seance->setCreatedAt(new \DateTimeImmutable());
        $seance->setPrix(11.90);
        $seance->setSalle($salle);
        $seance->setFilm($film);
        $manager->persist($seance);

        // Envoie en base (déclenche les événements, donc les sièges sont générés)
        $manager->flush();
    }
}

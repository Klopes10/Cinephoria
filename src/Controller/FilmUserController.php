<?php
namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
use App\Repository\CinemaRepository;
use App\Repository\SeanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FilmUserController extends AbstractController
{
    #[Route('/films', name: 'app_films', methods: ['GET'])]
    public function index(
        FilmRepository $filmRepository,
        CinemaRepository $cinemaRepository,
        GenreRepository $genreRepository
    ): Response {
        $films = $filmRepository->findAll();

        // Alimente tes listes (ou garde tes listes hardcodées)
        $villes = [
            'france'   => method_exists($cinemaRepository, 'findDistinctCitiesByCountry') ? $cinemaRepository->findDistinctCitiesByCountry('France') : [],
            'belgique' => method_exists($cinemaRepository, 'findDistinctCitiesByCountry') ? $cinemaRepository->findDistinctCitiesByCountry('Belgique') : [],
        ];
        $genres = $genreRepository->findAll();

        return $this->render('film_user/index.html.twig', [
            'films'  => $films,
            'villes' => $villes,
            'genres' => $genres,
        ]);
    }

    #[Route('/films/{id}', name: 'app_films_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(
        Film $film,
        Request $request,
        SeanceRepository $seanceRepository,
        CinemaRepository $cinemaRepository
    ): Response {
        // 1) Villes + pays
        $cinemas = $cinemaRepository->findAll();
        $villesData = [];
        foreach ($cinemas as $cinema) {
            $villesData[] = [
                'ville' => $cinema->getVille(),
                'pays'  => $cinema->getPays(),
            ];
        }
        $villesData = array_unique($villesData, SORT_REGULAR);
        usort($villesData, function ($a, $b) {
            if ($a['pays'] === 'France' && $b['pays'] !== 'France') return -1;
            if ($a['pays'] !== 'France' && $b['pays'] === 'France') return 1;
            return strcmp($a['ville'], $b['ville']);
        });
    
        // Transforme en tableau simple + séparateur |
        $villes = [];
        $franceDone = false;
        foreach ($villesData as $row) {
            if ($row['pays'] === 'Belgique' && !$franceDone) {
                $villes[] = '|';
                $franceDone = true;
            }
            $villes[] = $row['ville'];
        }
    
        // 2) Jours (aujourd’hui + 7)
        $jours = [];
        $joursLabels = ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'];
        $moisLabels  = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
        $today = new \DateTimeImmutable('today');
        for ($i = 0; $i < 8; $i++) {
            $d = $today->modify("+$i day");
            $jours[$d->format('Y-m-d')] = sprintf(
                '%s %02d %s',
                $joursLabels[(int)$d->format('w')],
                (int)$d->format('d'),
                $moisLabels[(int)$d->format('n') - 1]
            );
        }
    
        // 3) Actifs
        $activeVille = $request->query->get('ville') ?: ($villes[0] ?? null);
        $activeDate  = $request->query->get('date')  ?: array_key_first($jours);
    
        // 4) Sessions du couple (ville, date) actif
        $sessions = [];
        if ($activeVille && $activeDate && $activeVille !== '|') {
            $sessions = $seanceRepository->findByFilmVilleAndDate($film, $activeVille, $activeDate);
        }
    
        // 5) Grouping pour Twig
        $grouped = [];
        if ($activeVille && $activeDate && $activeVille !== '|') {
            $grouped[$activeVille][$activeDate] = $sessions;
        }
    
        return $this->render('film_user/show.html.twig', [
            'film'        => $film,
            'villes'      => $villes,
            'jours'       => $jours,
            'grouped'     => $grouped,
            'activeVille' => $activeVille,
            'activeDate'  => $activeDate,
        ]);
    }
    

}

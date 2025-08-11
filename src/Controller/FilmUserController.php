<?php
namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
use App\Repository\CinemaRepository;
use App\Repository\SeanceRepository;
use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    CinemaRepository $cinemaRepository,
    AvisRepository $avisRepository   // ← pour la note moyenne
): Response {
    // --- Villes triées: France d'abord, Belgique ensuite ---
    $cinemas = $cinemaRepository->findAll();
    $villesData = [];
    foreach ($cinemas as $cinema) {
        $villesData[] = ['ville' => $cinema->getVille(), 'pays' => $cinema->getPays()];
    }
    $villesData = array_unique($villesData, SORT_REGULAR);
    usort($villesData, function ($a, $b) {
        if ($a['pays'] === 'France' && $b['pays'] !== 'France') return -1;
        if ($a['pays'] !== 'France' && $b['pays'] === 'France') return 1;
        return strcmp($a['ville'], $b['ville']);
    });

    $villes = [];
    $franceDone = false;
    foreach ($villesData as $row) {
        if ($row['pays'] === 'Belgique' && !$franceDone) {
            $villes[] = '|';
            $franceDone = true;
        }
        $villes[] = $row['ville'];
    }

    // --- Jours: aujourd’hui + 7 ---
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

    // --- Actifs (éviter que "|" soit actif) ---
    $activeVille = $request->query->get('ville');
    if (!$activeVille || $activeVille === '|') {
        foreach ($villes as $v) { if ($v !== '|') { $activeVille = $v; break; } }
    }
    $activeDate  = $request->query->get('date') ?: array_key_first($jours);

    // --- Précharge TOUTES les séances (8 jours) pour ce film, groupées pour le JS ---
    $sessionsMap = $seanceRepository->findByFilmBetweenDatesForJs($film, $today, $today->modify('+7 day'));

    // counts initiaux pour les "dots" des jours (ville active)
    $dayCounts = [];
    if (isset($sessionsMap[$activeVille])) {
        foreach ($sessionsMap[$activeVille] as $ymd => $list) {
            $dayCounts[$ymd] = \is_array($list) ? \count($list) : 0;
        }
    }

    // --- Contenu initial côté serveur (SEO + no-JS) pour le couple actif ---
    $sessions = [];
    if ($activeVille && $activeDate && $activeVille !== '|') {
        $sessions = $seanceRepository->findByFilmVilleAndDate($film, $activeVille, $activeDate);
    }
    $grouped = [];
    if ($activeVille && $activeDate && $activeVille !== '|') {
        $grouped[$activeVille][$activeDate] = $sessions;
    }

    // --- Note moyenne du film (table Avis) ---
    $noteMoy = $avisRepository->getAverageNoteForFilm($film); // ex: 3.7 ou null

    return $this->render('film_user/show.html.twig', [
        'film'         => $film,
        'villes'       => $villes,
        'jours'        => $jours,
        'grouped'      => $grouped,
        'activeVille'  => $activeVille,
        'activeDate'   => $activeDate,
        'dayCounts'    => $dayCounts,
        'sessionsMap'  => $sessionsMap, // pour le JS (sans DateTime)
        'noteMoy'      => $noteMoy,     // pour afficher la note entre synopsis & séances
    ]);
}



    #[Route('/films/{id}/sessions', name: 'app_films_sessions', methods: ['GET'])]
public function sessionsPartial(
    Film $film,
    Request $request,
    SeanceRepository $seanceRepository
): Response {
    $ville = $request->query->get('ville');
    $date  = $request->query->get('date');

    $sessions = [];
    if ($ville && $date && $ville !== '|') {
        $sessions = $seanceRepository->findByFilmVilleAndDate($film, $ville, $date);
    }

    return $this->render('film_user/_sessions_grid.html.twig', [
        'sessions' => $sessions,
        'activeVille' => $ville,
    ]);
}

#[Route('/films/{id}/day-counts', name: 'app_films_day_counts', methods: ['GET'])]
public function dayCounts(
    Film $film,
    Request $request,
    SeanceRepository $seanceRepository
): JsonResponse {
    $ville = $request->query->get('ville');
    $today = new \DateTimeImmutable('today');

    $counts = [];
    if ($ville && $ville !== '|') {
        // ↳ ajoute cette méthode dans SeanceRepository (voir section 4)
        $counts = $seanceRepository->countByFilmVilleBetweenDates(
            $film,
            $ville,
            $today,
            $today->modify('+7 day')
        );
    }

    return $this->json(['counts' => $counts]);
}
    

}

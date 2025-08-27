<?php

namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
use App\Repository\CinemaRepository;
use App\Repository\SeanceRepository;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class FilmUserController extends AbstractController
{
    #[Route('/films', name: 'app_films', methods: ['GET'])]
    public function index(
        FilmRepository $filmRepository,
        CinemaRepository $cinemaRepository,
        GenreRepository $genreRepository,
        AvisRepository $avisRepository,
        SeanceRepository $seanceRepository   // <-- ajoute ce repo ici
    ): Response {
        $films = $filmRepository->findAll();
    
        $filmsWithNotes = [];
        foreach ($films as $film) {
            $filmsWithNotes[] = [
                'entity'  => $film,
                'noteMoy' => $avisRepository->getAverageNoteForFilm($film),
                'nbAvis'  => $avisRepository->countValidatedForFilm($film),
            ];
        }
    
        // >>> Dispo: pour chaque film, liste des villes + jours (1..7) où il a au moins une séance
        $rows = $seanceRepository->createQueryBuilder('s')
            ->select('IDENTITY(s.film) AS fid, s.date AS sdate, c.ville AS ville')
            ->join('s.salle', 'sa')
            ->join('sa.cinema', 'c')
            ->getQuery()->getResult(); // array ['fid'=>, 'sdate'=>\DateTimeInterface, 'ville'=>]
    
        $availability = [];
        foreach ($rows as $r) {
            $fid   = (int) $r['fid'];
            $ville = (string) $r['ville'];
            /** @var \DateTimeInterface $dt */
            $dt    = $r['sdate'];
            $dow   = (int) $dt->format('N'); // 1..7 (Lundi..Dimanche)
    
            if (!isset($availability[$fid])) {
                $availability[$fid] = ['villes' => [], 'jours' => []];
            }
            $availability[$fid]['villes'][$ville] = true;
            $availability[$fid]['jours'][$dow]    = true;
        }
        foreach ($availability as $fid => &$a) {
            $a['villes'] = array_keys($a['villes']);
            $a['jours']  = array_keys($a['jours']);
        }
        unset($a);
    
        $villes = [
            'france'   => $cinemaRepository->findDistinctCitiesByCountry('France'),
            'belgique' => $cinemaRepository->findDistinctCitiesByCountry('Belgique'),
        ];
        $genres = $genreRepository->findAll();
    
        return $this->render('film_user/index.html.twig', [
            'films'        => $filmsWithNotes,
            'villes'       => $villes,
            'genres'       => $genres,
            'availability' => $availability, // <<< passe la map au Twig
        ]);
    }

    #[Route('/films/{id}', name: 'app_films_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(
        Film $film,
        Request $request,
        SeanceRepository $seanceRepository,
        CinemaRepository $cinemaRepository,
        AvisRepository $avisRepository
    ): Response {
        // --- Villes triées: France d'abord, Belgique ensuite ---
        $cinemas    = $cinemaRepository->findAll();
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

        $villes      = [];
        $franceSplit = false;
        foreach ($villesData as $row) {
            if ($row['pays'] === 'Belgique' && !$franceSplit) {
                $villes[]   = '|';
                $franceSplit = true;
            }
            $villes[] = $row['ville'];
        }

        // --- Jours: aujourd’hui + 7 ---
        $jours        = [];
        $joursLabels  = ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'];
        $moisLabels   = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
        $today        = new \DateTimeImmutable('today');

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
            foreach ($villes as $v) {
                if ($v !== '|') { $activeVille = $v; break; }
            }
        }
        $activeDate  = $request->query->get('date') ?: array_key_first($jours);

        // --- Précharge toutes les séances (8 jours) pour ce film ---
        $sessionsMap = $seanceRepository->findByFilmBetweenDatesForJs(
            $film,
            $today,
            $today->modify('+7 day')
        );

        // counts initiaux pour les "dots" des jours (ville active)
        $dayCounts = [];
        if ($activeVille && isset($sessionsMap[$activeVille])) {
            foreach ($sessionsMap[$activeVille] as $ymd => $list) {
                $dayCounts[$ymd] = \is_array($list) ? \count($list) : 0;
            }
        }

        // --- Contenu initial (SEO / no-JS) ---
        $sessions = [];
        if ($activeVille && $activeDate && $activeVille !== '|') {
            $sessions = $seanceRepository->findByFilmVilleAndDate($film, $activeVille, $activeDate);
        }
        $grouped = [];
        if ($activeVille && $activeDate && $activeVille !== '|') {
            $grouped[$activeVille][$activeDate] = $sessions;
        }

        // --- Notes / Avis ---
        $noteMoy       = $avisRepository->getAverageNoteForFilm($film);
        $nbAvis        = $avisRepository->countValidatedForFilm($film);
        $avisPreloaded = $nbAvis > 0 ? $avisRepository->findValidatedForFilm($film) : [];

        return $this->render('film_user/show.html.twig', [
            'film'          => $film,
            'villes'        => $villes,
            'jours'         => $jours,
            'grouped'       => $grouped,
            'activeVille'   => $activeVille,
            'activeDate'    => $activeDate,
            'dayCounts'     => $dayCounts,
            'sessionsMap'   => $sessionsMap,
            'noteMoy'       => $noteMoy,
            'nbAvis'        => $nbAvis,
            'avisPreloaded' => $avisPreloaded,
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
            'sessions'    => $sessions,
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
            $counts = $seanceRepository->countByFilmVilleBetweenDates(
                $film,
                $ville,
                $today,
                $today->modify('+7 day')
            );
        }

        return $this->json(['counts' => $counts]);
    }

    #[Route('/films/{id}/avis', name: 'app_films_reviews', methods: ['GET'])]
    public function reviews(
        Film $film,
        AvisRepository $avisRepository,
        Request $request
    ): Response {
        $avis = $avisRepository->findValidatedForFilm($film);

        if ($request->isXmlHttpRequest()) {
            return $this->render('film_user/_avis_modal_content.html.twig', [
                'film' => $film,
                'avis' => $avis,
            ]);
        }

        return $this->render('film_user/avis.html.twig', [
            'film'   => $film,
            'avis'   => $avis,
            'nbAvis' => \count($avis),
        ]);
    }

    // ========= NOUVELLE ROUTE AJAX POUR LE FILTRE =========
    #[Route('/films/filter', name: 'app_films_filter', methods: ['GET'])]
    public function filter(
        Request $request,
        FilmRepository $filmRepository,
        SeanceRepository $seanceRepository,
        AvisRepository $avisRepository,
        EntityManagerInterface $em
    ): Response {
        $ville  = trim((string) $request->query->get('ville', ''));
        $genre  = trim((string) $request->query->get('genre', ''));
        $jour   = trim((string) $request->query->get('jour', '')); // Lundi..Dimanche
        $page   = max(1, (int) $request->query->get('page', 1));
        $perPage = 12;
    
        // 1) Base: films (+ genre éventuel)
        $qb = $filmRepository->createQueryBuilder('f')
            ->leftJoin('f.genre', 'g')->addSelect('g');
    
        if ($genre !== '') {
            $qb->andWhere('g.nom = :gn')->setParameter('gn', $genre);
        }
    
        $filmsBase = $qb->getQuery()->getResult(); // tableau d'entités Film
    
        // 2) Si une ville est choisie, on restreint aux films qui ont AU MOINS UNE séance dans cette ville.
        //    (On évite les fonctions SQL non-portables.)
        $filmIdsAllowed = null;
        if ($ville !== '') {
            // Récupère tous les couples (film_id, date) qui matchent la ville
            $dql = 'SELECT DISTINCT IDENTITY(s.film) AS fid, s.date AS sdate
                    FROM App\Entity\Seance s
                    JOIN s.salle sa
                    JOIN sa.cinema c
                    WHERE c.ville = :ville';
            $rows = $em->createQuery($dql)
                ->setParameter('ville', $ville)
                ->getResult(); // renvoie un tableau de [fid => int, sdate => \DateTimeInterface]
    
            // Si un jour est choisi, on filtre ces résultats par jour côté PHP
            if ($jour !== '') {
                $mapFrToNum = [
                    'Lundi' => 1, 'Mardi' => 2, 'Mercredi' => 3,
                    'Jeudi' => 4, 'Vendredi' => 5, 'Samedi' => 6, 'Dimanche' => 7,
                ];
                $wanted = $mapFrToNum[$jour] ?? null;
    
                if ($wanted) {
                    $rows = array_filter($rows, function($r) use ($wanted) {
                        /** @var \DateTimeInterface $d */
                        $d = $r['sdate'];
                        // ISO-8601: 1 (lundi) à 7 (dimanche)
                        $isoDow = (int) $d->format('N');
                        return $isoDow === $wanted;
                    });
                }
            }
    
            // On garde juste les film IDs qui ont au moins une séance restante après le filtre jour éventuel
            $filmIdsAllowed = array_values(array_unique(array_map(fn($r) => (int)$r['fid'], $rows)));
        }
    
        // 3) Applique la restriction de ville/jour si nécessaire
        if (is_array($filmIdsAllowed)) {
            $filmsBase = array_values(array_filter($filmsBase, fn($f) => in_array($f->getId(), $filmIdsAllowed, true)));
        }
    
        // 4) Ajoute les notes / nb avis
        $filmsWithNotes = [];
        foreach ($filmsBase as $film) {
            $filmsWithNotes[] = [
                'entity'  => $film,
                'noteMoy' => $avisRepository->getAverageNoteForFilm($film),
                'nbAvis'  => $avisRepository->countValidatedForFilm($film),
            ];
        }
    
        // 5) Pagination (12 par page)
        $total      = count($filmsWithNotes);
        $totalPages = (int) ceil($total / $perPage);
        if ($page > $totalPages && $totalPages > 0) { $page = $totalPages; }
        $offset     = ($page - 1) * $perPage;
        $slice      = array_slice($filmsWithNotes, $offset, $perPage);
    
        // 6) Renvoie juste la LISTE (ton partial affiche "Aucun film..." si vide)
        return $this->render('film_user/_films_list.html.twig', [
            'films' => $slice,
        ]);
    }
    

}

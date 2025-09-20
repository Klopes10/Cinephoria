<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Seance;
use App\Entity\Reservation;
use App\Repository\SeanceRepository;
use App\Repository\SiegeRepository;
use App\Service\MongoReservationsLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReservationUserController extends AbstractController
{
    public function __construct(
        private readonly MongoReservationsLogger $mongoLogger, // ⇦ logger Mongo
    ) {}

    #[Route('/reservation', name: 'app_reservation')]
    public function index(SeanceRepository $seanceRepo, EntityManagerInterface $em): Response
    {
        // --- Ruban 7 jours (FR + Europe/Paris)
        $tz    = new \DateTimeZone('Europe/Paris');
        $days  = [];
        $start = new \DateTimeImmutable('today', $tz);

        $fmt = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $tz->getName(),
            \IntlDateFormatter::GREGORIAN,
            "EEE d MMM." // ex: "sam. 20 sept."
        );

        for ($i = 0; $i < 7; $i++) {
            $d = $start->modify("+$i day");

            // Commence par une majuscule, conserve le reste tel quel (pour garder "sept.")
            $raw   = $fmt->format($d);               // "sam. 20 sept."
            $label = mb_strtoupper(mb_substr($raw, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($raw, 1, null, 'UTF-8'); // "Sam. 20 sept."

            $days[] = [
                'iso'   => $d->format('Y-m-d'),
                'label' => $label,
            ];
        }

        // --- Villes par pays
        $cinemaRows = $em->getRepository(Cinema::class)->createQueryBuilder('c')
            ->select('c.ville, c.pays')
            ->groupBy('c.ville, c.pays')
            ->orderBy('c.pays', 'ASC')
            ->addOrderBy('c.ville', 'ASC')
            ->getQuery()->getArrayResult();

        $villesParPays = [];
        foreach ($cinemaRows as $row) {
            $pays  = (string)($row['pays'] ?? '');
            $ville = (string)($row['ville'] ?? '');
            if (!$pays || !$ville) {
                continue;
            }
            $villesParPays[$pays][] = $ville;
        }
        foreach ($villesParPays as $p => &$v) {
            $v = array_values(array_unique($v));
            sort($v);
        }
        unset($v);

        // --- Séances sur 7 jours
        $allSeances = $seanceRepo->createQueryBuilder('s')
            ->innerJoin('s.film', 'f')->addSelect('f')
            ->innerJoin('s.salle', 'sa')->addSelect('sa')
            ->innerJoin('sa.cinema', 'c')->addSelect('c')
            ->andWhere('s.date BETWEEN :d1 AND :d2')
            ->setParameter('d1', $start)
            ->setParameter('d2', $start->modify('+6 day'))
            ->orderBy('c.pays', 'ASC')
            ->addOrderBy('c.ville', 'ASC')
            ->addOrderBy('f.titre', 'ASC')
            ->addOrderBy('s.date', 'ASC')
            ->addOrderBy('s.heureDebut', 'ASC')
            ->getQuery()->getResult();

        $allData     = [];
        $filmsByCity = [];

        foreach ($allSeances as $s) {
            /** @var Seance $s */
            $cinema  = $s->getSalle()->getCinema();
            $ville   = (string)$cinema->getVille();
            $pays    = (string)$cinema->getPays();
            $cityKey = $ville . ' — ' . $pays;

            $dateIso = $s->getDate()->format('Y-m-d');
            $film    = $s->getFilm();
            $fid     = $film->getId();

            if (!isset($allData[$cityKey][$dateIso][$fid])) {
                $allData[$cityKey][$dateIso][$fid] = [
                    'film' => [
                        'id'           => $fid,
                        'titre'        => $film->getTitre(),
                        'affiche'      => method_exists($film, 'getAffiche') ? $film->getAffiche() : null,
                        'age'          => method_exists($film, 'getAgeMinimum') ? $film->getAgeMinimum() : null,
                        'synopsis'     => method_exists($film, 'getSynopsis') ? $film->getSynopsis() : null,
                        'genre'        => $film->getGenre() ? $film->getGenre()->getNom() : null,
                        'coupDeCoeur'  => method_exists($film, 'isCoupDeCoeur') ? (bool) $film->isCoupDeCoeur() : false,
                    ],
                    'seances' => [],
                ];
            }

            $allData[$cityKey][$dateIso][$fid]['seances'][] = [
                'id'     => $s->getId(),
                'heure'  => $s->getHeureDebut()->format('H:i'),
                'fin'    => $s->getHeureFin() ? $s->getHeureFin()->format('H:i') : null,
                'format' => $s->getQualite()?->getLabel(),
                'salle'  => $s->getSalle()?->getNom(),
                'places' => (int)$s->getPlacesDisponible(),
            ];

            $filmsByCity[$cityKey][$fid] = [
                'id'    => $fid,
                'titre' => $film->getTitre(),
            ];
        }

        foreach ($filmsByCity as $cityKey => $map) {
            usort($map, fn($a, $b) => strcmp($a['titre'], $b['titre']));
            $filmsByCity[$cityKey] = array_values($map);
        }

        return $this->render('reservation_user/index.html.twig', [
            'days'          => $days,
            'villesParPays' => $villesParPays,
            'data'          => json_encode($allData),
            'filmsByCity'   => json_encode($filmsByCity),
        ]);
    }

    /**
     * Page de réservation d’une séance
     */
    #[Route('/reservation/seance/{id}', name: 'app_reservation_new', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function new(Seance $seance): Response
    {
        $film   = $seance->getFilm();
        $salle  = $seance->getSalle();
        $cinema = $salle?->getCinema();

        $poster = $this->resolvePoster(
            method_exists($film, 'getAffiche') ? $film->getAffiche() : null
        );

        // PMR + rangées standard
        [$pmrRowData, $rowsData] = $this->buildPmrAndRowsFromSeats($seance);

        // Datetimes combinés
        $debut = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $seance->getDate()->format('Y-m-d') . ' ' . $seance->getHeureDebut()->format('H:i:s'),
            $seance->getDate()->getTimezone()
        );
        $fin = $seance->getHeureFin()
            ? \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $seance->getDate()->format('Y-m-d') . ' ' . $seance->getHeureFin()->format('H:i:s'),
                $seance->getDate()->getTimezone()
              )
            : null;

        return $this->render('reservation_user/new.html.twig', [
            'seance' => [
                'id'           => $seance->getId(),
                'date'         => $debut,
                'fin'          => $fin,
                'format'       => $seance->getQualite()?->getLabel(),
                'salle'        => $salle?->getNom() ?? $salle?->getNumero(),
                'placesDispo'  => (int)$seance->getPlacesDisponible(),
                'tarif'        => method_exists($seance, 'getTarif') ? $seance->getTarif() : (method_exists($seance, 'getPrix') ? $seance->getPrix() : 9.5),
            ],
            'film' => [
                'titre'    => $film->getTitre(),
                'affiche'  => method_exists($film, 'getAffiche') ? $film->getAffiche() : null,
                'genre'    => $film->getGenre()?->getNom(),
                'age'      => method_exists($film, 'getAgeMinimum') ? $film->getAgeMinimum() : null,
                'synopsis' => method_exists($film, 'getSynopsis') ? $film->getSynopsis() : null,
            ],
            'cinema' => [
                'nom'   => $cinema?->getNom(),
                'ville' => $cinema?->getVille(),
                'pays'  => $cinema?->getPays(),
            ],
            'poster' => $poster,

            // Utilisés par le Twig
            'pmrRow' => $pmrRowData,
            'rows'   => $rowsData,
        ]);
    }

    /**
     * PMR (A1..A5) puis rangées de 10, avec code renvoyé
     */
    private function buildPmrAndRowsFromSeats(Seance $seance): array
    {
        $all = $seance->getSieges()?->toArray() ?? [];

        usort($all, fn($a, $b) => $a->getNumero() <=> $b->getNumero());

        $pmr = array_values(array_filter($all, fn($s) => (bool)$s->isPMR()));
        $std = array_values(array_filter($all, fn($s) => !(bool)$s->isPMR()));

        $pmrRow = array_slice($pmr, 0, 6);

        $map = fn($s) => [
            'id'         => $s->getId(),
            'numero'     => $s->getNumero(),
            'code'       => $s->getCode(),
            'isPMR'      => (bool)$s->isPMR(),
            'isReserved' => (bool)$s->isReserved(),
        ];

        $pmrRowData = array_map($map, $pmrRow);

        $rowsData = [];
        $chunks = array_chunk($std, 10);
        foreach ($chunks as $chunk) {
            $rowsData[] = array_map($map, $chunk);
        }

        return [$pmrRowData, $rowsData];
    }

    #[Route('/reservation/seance/{id}/confirm', name: 'app_reservation_confirm', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function confirm(
        Seance $seance,
        Request $request,
        EntityManagerInterface $em,
        SiegeRepository $siegeRepo
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['ok' => false, 'message' => 'Requête invalide.'], 400);
        }

        $raw = (string) $request->request->get('seats', '');
        $ids = array_values(array_filter(array_map('intval', array_map('trim', explode(',', $raw)))));
        if (empty($ids)) {
            return $this->json(['ok' => false, 'message' => 'Aucun siège sélectionné.'], 400);
        }

        try {
            $sieges = $siegeRepo->createQueryBuilder('sg')
                ->andWhere('sg.seance = :seance')
                ->andWhere('sg.id IN (:ids)')
                ->setParameter('seance', $seance)
                ->setParameter('ids', $ids)
                ->getQuery()->getResult();

            if (count($sieges) !== count($ids)) {
                return $this->json(['ok' => false, 'message' => 'Un ou plusieurs sièges sont invalides.'], 400);
            }
            foreach ($sieges as $sg) {
                if ($sg->isReserved()) {
                    return $this->json(['ok' => false, 'message' => 'Un siège a déjà été réservé. Rechargez la page.'], 409);
                }
            }

            if (count($ids) > 10) {
                return $this->json([
                    'ok' => false,
                    'message' => 'Vous ne pouvez pas réserver plus de 10 sièges par commande.'
                ], 400);
            }

            $reservation = new Reservation();
            $reservation->setUser($this->getUser());
            $reservation->setSeance($seance);
            $reservation->setNombrePlaces(count($sieges));

            foreach ($sieges as $sg) {
                $reservation->addSiege($sg);
            }

            if (method_exists($reservation, 'getCreatedAt') && null === $reservation->getCreatedAt()) {
                $reservation->setCreatedAt(new \DateTimeImmutable());
            }

            $em->persist($reservation);
            $em->flush();

            // ⇨ Journalise dans MongoDB (agrégation 7 jours)
            $this->mongoLogger->log($reservation);

            return $this->json([
                'ok'       => true,
                'message'  => 'Réservation validée.',
                'redirect' => $this->generateUrl('app_account'),
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'ok'      => false,
                'message' => 'Erreur serveur: '.$e->getMessage()
            ], 500);
        }
    }

    private function resolvePoster(?string $raw): string
    {
        if (!$raw) return '/images/placeholder.jpg';
        if (str_starts_with($raw, 'http') || str_starts_with($raw, '/')) return $raw;
        if (str_starts_with($raw, 'uploads/')) return '/' . $raw;
        return '/uploads/affiches/' . $raw;
    }
}

<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Repository\SeanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationUserController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    public function index(SeanceRepository $seanceRepo, EntityManagerInterface $em): Response
    {
        // Ruban 7 jours (libellés FR)
$days = [];
$start = new \DateTimeImmutable('today');

$fmt = new \IntlDateFormatter(
    'fr_FR',                     // langue
    \IntlDateFormatter::NONE,
    \IntlDateFormatter::NONE,
    $start->getTimezone()->getName(),
    \IntlDateFormatter::GREGORIAN,
    "EEE dd MMM."              // ex. "sam. 23 août."
);

for ($i = 0; $i < 7; $i++) {
    $d = $start->modify("+$i day");
    $label = $fmt->format($d);
    // ucfirst « français » (garde les accents)
    $label = mb_convert_case($label, MB_CASE_TITLE, 'UTF-8');

    $days[] = [
        'iso'   => $d->format('Y-m-d'),
        'label' => $label,
    ];
}

        // Toutes les villes groupées par pays
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
            if (!$pays || !$ville) continue;
            $villesParPays[$pays][] = $ville;
        }
        foreach ($villesParPays as $p => &$v) {
            $v = array_values(array_unique($v));
            sort($v);
        }

        // Toutes les séances sur 7 jours
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

        // … (reste inchangé)
        $allData = [];
        $filmsByCity = [];

        foreach ($allSeances as $s) {
            $cinema = $s->getSalle()->getCinema();
            $ville  = (string)$cinema->getVille();
            $pays   = (string)$cinema->getPays();
            $cityKey = $ville.' — '.$pays;

            $dateIso = $s->getDate()->format('Y-m-d');
            $film    = $s->getFilm();
            $fid     = $film->getId();

            if (!isset($allData[$cityKey][$dateIso][$fid])) {
                $allData[$cityKey][$dateIso][$fid] = [
                    'film' => [
                        'id'       => $fid,
                        'titre'    => $film->getTitre(),
                        'affiche'  => method_exists($film, 'getAffiche') ? $film->getAffiche() : null,
                        'age'      => method_exists($film, 'getAgeMinimum') ? $film->getAgeMinimum() : null,
                        'synopsis' => method_exists($film, 'getSynopsis') ? $film->getSynopsis() : null,
                    ],
                    'seances' => [],
                ];
            }

            $allData[$cityKey][$dateIso][$fid]['seances'][] = [
                'id'     => $s->getId(),
                'heure'  => $s->getHeureDebut()->format('H:i'),
                'salle'  => $s->getSalle()?->getNom(),
                'places' => (int)$s->getPlacesDisponible(),
            ];

            $filmsByCity[$cityKey][$fid] = [
                'id'    => $fid,
                'titre' => $film->getTitre(),
            ];
        }

        foreach ($filmsByCity as $cityKey => $map) {
            usort($map, fn($a,$b) => strcmp($a['titre'], $b['titre']));
            $filmsByCity[$cityKey] = array_values($map);
        }

        return $this->render('reservation_user/index.html.twig', [
            'days'          => $days,
            'villesParPays' => $villesParPays,
            'data'          => json_encode($allData),
            'filmsByCity'   => json_encode($filmsByCity),
        ]);
    }
}

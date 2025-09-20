<?php

namespace App\Controller\Api;

use App\Entity\Reservation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class MobileController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack,
    ) {}

    #[Route('/me/seances', name: 'me_seances', methods: ['GET'])]
    public function meSeances(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        $tz    = new \DateTimeZone('Europe/Paris');
        $today = new \DateTimeImmutable('today', $tz);
        $now   = new \DateTimeImmutable('now',   $tz);

        // r = Reservation, s = Seance, f = Film
        $qb = $this->em->getRepository(Reservation::class)->createQueryBuilder('r')
            ->join('r.seance', 's')->addSelect('s')
            ->join('s.film', 'f')->addSelect('f')
            ->join('s.salle', 'sl')->addSelect('sl')
            ->where('r.user = :u')
            // Séances à venir : date > today OU (date = today ET heureDebut >= heure courante)
            ->andWhere('s.date > :today OR (s.date = :today AND s.heureDebut >= :nowTime)')
            ->andWhere('s.date IS NOT NULL')
            ->andWhere('s.heureDebut IS NOT NULL')
            ->setParameter('u', $user)
            ->setParameter('today',  $today,  Types::DATE_IMMUTABLE)
            ->setParameter('nowTime', \DateTimeImmutable::createFromFormat('H:i:s', $now->format('H:i:s'), $tz), Types::TIME_IMMUTABLE)
            ->addOrderBy('s.date', 'ASC')
            ->addOrderBy('s.heureDebut', 'ASC');

        /** @var Reservation[] $rows */
        $rows = $qb->getQuery()->getResult();

        $req  = $this->requestStack->getCurrentRequest();
        $base = $req?->getSchemeAndHttpHost() ?? ''; // ex: http://localhost:8080

        $out = [];
        foreach ($rows as $r) {
            $s  = $r->getSeance();
            if (!$s) { continue; }

            $f  = $s->getFilm();
            $sl = $s->getSalle();

            $date = $s->getDate();        // DateImmutable
            $hDeb = $s->getHeureDebut();  // TimeImmutable
            $hFin = $s->getHeureFin();    // TimeImmutable|nullable

            if (!$date || !$hDeb) { continue; }

            // Combine date + heureDebut en ISO8601
            $combined = new \DateTimeImmutable(
                $date->format('Y-m-d') . ' ' . $hDeb->format('H:i:s'),
                $tz
            );

            // URL d'affiche absolue et correcte
            $affiche = (string)($f?->getAffiche() ?? '');
            if ($affiche !== '') {
                if (!str_starts_with($affiche, 'http')) {
                    // Normalise le chemin relatif
                    $path = ltrim($affiche, '/');
                    // Si ça ne commence pas par "uploads/", on force le dossier canonical
                    if (!str_starts_with($path, 'uploads/')) {
                        $path = 'uploads/affiches/' . $path;
                    }
                    $affiche = rtrim($base, '/') . '/' . $path; // ex: http://localhost:8080/uploads/affiches/xxx.png
                }
                // sinon : URL absolue -> on la laisse telle quelle
            }

            // Sièges (si relation dispo)
            $seats = [];
            if (method_exists($r, 'getSieges') && $r->getSieges()) {
                foreach ($r->getSieges() as $sg) {
                    $seat =
                        (method_exists($sg, 'getCode')   ? $sg->getCode()   : null) ??
                        (method_exists($sg, 'getNom')    ? $sg->getNom()    : null) ??
                        (method_exists($sg, 'getNumero') ? $sg->getNumero() : null);
                    if ($seat !== null) {
                        $seats[] = (string)$seat;
                    }
                }
            }

            $out[] = [
                'id'            => $s->getId(),
                'film'          => $f?->getTitre() ?? 'Film',
                'affiche'       => $affiche,
                'jour'          => $date->format('Y-m-d'),
                'heureDebut'    => $hDeb->format('H:i:s'),
                'heureFin'      => $hFin?->format('H:i:s') ?? '',
                'date'          => $combined->format(\DateTimeInterface::ATOM),
                'salle'         => $sl?->getNom() ?? '',
                'qualite'       => $s->getQualite()?->getLabel() ?? '', // adaptez si votre entité a getLabel()
                'seats'         => $seats,
                'reservationId' => $r->getId(),
            ];
        }

        return $this->json($out);
    }

    #[Route('/reservations/{id}', name: 'reservation_show', methods: ['GET'])]
    public function reservationShow(Reservation $reservation): JsonResponse
    {
        if ($reservation->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Forbidden'], 403);
        }

        // QR "signé" simple: rid/uid/timestamp + HMAC
        $payload = [
            'rid' => $reservation->getId(),
            'uid' => $reservation->getUser()->getId(),
            'ts'  => time(),
        ];
        $secret = $_ENV['QR_SECRET'] ?? 'dev-secret';
        $sig = hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES), $secret);

        return $this->json([
            'id'         => $reservation->getId(),
            'qrcodeData' => base64_encode(json_encode($payload + ['sig' => $sig], JSON_UNESCAPED_SLASHES)),
        ]);
    }
}

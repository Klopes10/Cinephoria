<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function account(
        ReservationRepository $reservationRepo,
        AvisRepository $avisRepository
    ): Response {
        $user = $this->getUser();
        $now  = new \DateTimeImmutable();

        $reservations = $reservationRepo->findBy(['user' => $user]);

        $upcoming = [];
        $history  = [];

        foreach ($reservations as $r) {
            $seance = $r->getSeance();
            $date   = $seance?->getDate();

            $film    = $seance?->getFilm();
            $titre   = $film?->getTitre() ?? 'Film';
            $affiche = $film?->getAffiche();
            $ville   = $seance?->getCinema()?->getVille()
                    ?? $seance?->getSalle()?->getCinema()?->getVille()
                    ?? '';

            // ========= NB DE PLACES =========
            // 1) Si la réservation a des sièges liés -> on prend count()
            // 2) Sinon on retombe sur un champ numérique éventuel (getNombrePlaces / getNbPlaces / getPlaces)
            $nbPlaces = null;

            if (method_exists($r, 'getSieges')) {
                $sieges = $r->getSieges();
                
                if (\is_countable($sieges)) {
                    $nbPlaces = \count($sieges);
                }
            }

            if ($nbPlaces === null || $nbPlaces === 0) {
                if (method_exists($r, 'getNombrePlaces') && null !== $r->getNombrePlaces()) {
                    $nbPlaces = (int) $r->getNombrePlaces();
                } elseif (method_exists($r, 'getNbPlaces') && null !== $r->getNbPlaces()) {
                    $nbPlaces = (int) $r->getNbPlaces();
                } elseif (method_exists($r, 'getPlaces') && null !== $r->getPlaces()) {
                    $nbPlaces = (int) $r->getPlaces();
                } else {
                    $nbPlaces = 1;
                }
            }

            // ========= AVIS UTILISATEUR =========
            $userNote = 0;
            $rated    = false;
            $pending  = false;

            if ($film && $user) {
                $avis = $avisRepository->findOneBy(['film' => $film, 'user' => $user]);
                if ($avis) {
                    $rated    = true;
                    $userNote = (int) $avis->getNoteSur5();
                    $pending  = !$avis->isValide();
                }
            }

            $row = [
                'id'         => $r->getId(),
                'film'       => $film,
                'titre'      => $titre,
                'affiche'    => $affiche,
                'dateSeance' => $date,
                'ville'      => $ville,
                'nbPlaces'   => $nbPlaces,
                'userNote'   => $userNote, // 0..5
                'rated'      => $rated,    // déjà noté par l’utilisateur ?
                'pending'    => $pending,  // en attente de validation ?
            ];

            if ($date instanceof \DateTimeInterface) {
                if ($date >= $now) { $upcoming[] = $row; } else { $history[] = $row; }
            } else {
                $history[] = $row;
            }
        }

        usort($upcoming, static fn($a, $b) =>
            ($a['dateSeance'] <=> $b['dateSeance']) ?: ($a['id'] <=> $b['id'])
        );
        usort($history, static fn($a, $b) =>
            ($b['dateSeance'] <=> $a['dateSeance']) ?: ($b['id'] <=> $a['id'])
        );

        return $this->render('account/index.html.twig', [
            'upcoming' => $upcoming,
            'history'  => $history,
        ]);
    }

    #[Route('/mon-compte/note', name: 'app_account_rate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function rate(
        Request $request,
        ReservationRepository $reservationRepo,
        AvisRepository $avisRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['ok' => false, 'error' => 'unauthenticated'], 401);
        }

        $data    = json_decode($request->getContent() ?: '[]', true) ?: [];
        $resId   = (int)($data['reservationId'] ?? 0);
        $note    = (int)($data['note'] ?? 0);
        $comment = trim((string)($data['comment'] ?? ''));

        $note = max(1, min(5, $note));

        $reservation = $reservationRepo->find($resId);
        if (!$reservation || $reservation->getUser() !== $user) {
            return $this->json(['ok' => false, 'error' => 'reservation_not_found'], 404);
        }

        $film = $reservation->getSeance()->getFilm();

        // Un avis max par user/film
        $existing = $avisRepo->findOneBy(['film' => $film, 'user' => $user]);
        if ($existing) {
            return $this->json(['ok' => false, 'error' => 'already_rated'], 409);
        }

        $isAutoValidated = ($comment === '' || $comment === null);

        $avis = (new Avis())
            ->setUser($user)
            ->setFilm($film)
            ->setNoteSur5($note)
            ->setCommentaire($comment ?: null)
            ->setValide($isAutoValidated)
            ->setCreatedAt(new \DateTimeImmutable());

        $em->persist($avis);
        $em->flush();

        $avg   = $avisRepo->getAverageNoteForFilm($film); // sur "valide = true" seulement
        $count = $avisRepo->countValidatedForFilm($film);

        return $this->json([
            'ok'        => true,
            'validated' => $isAutoValidated,
            'note'      => $note,
            'average'   => $avg,
            'count'     => $count,
            'filmId'    => $film->getId(),
            'resId'     => $reservation->getId(),
        ]);
    }
}

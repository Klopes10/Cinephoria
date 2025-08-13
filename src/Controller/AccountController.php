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
        $now  = new \DateTimeImmutable('now');

        // Récupère toutes les réservations de l’utilisateur
        $reservations = $reservationRepo->findBy(['user' => $user]);

        $upcoming = [];
        $history  = [];

        foreach ($reservations as $r) {
            $seance = $r->getSeance();
            $date   = $seance?->getDate(); // \DateTimeInterface|NULL

            // Film / affiche / titre
            $film    = $seance?->getFilm();
            $titre   = $film?->getTitre() ?? 'Film';
            $affiche = $film?->getAffiche();

            // Ville (en fonction de ton modèle de données)
            $ville =
                $seance?->getCinema()?->getVille()
                ?? $seance?->getSalle()?->getCinema()?->getVille()
                ?? '';

            // Nombre de places (entité: getNombrePlaces)
            $nbPlaces = method_exists($r, 'getNombrePlaces')
                ? $r->getNombrePlaces()
                : (method_exists($r, 'getNbPlaces') ? $r->getNbPlaces() : 1);

            // Note déjà donnée par l’utilisateur pour ce film (si validée)
            $userNote = 0;
            if ($film && $user) {
                $avis = $avisRepository->findOneBy([
                    'film'   => $film,
                    'user'   => $user,
                    'valide' => true,
                ]);
                if ($avis && method_exists($avis, 'getNoteSur5')) {
                    $userNote = (int) $avis->getNoteSur5();
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
                'userNote'   => $userNote, // affichage étoilé et lock si > 0
            ];

            if ($date instanceof \DateTimeInterface) {
                if ($date >= $now) {
                    $upcoming[] = $row;
                } else {
                    $history[]  = $row;
                }
            } else {
                // si pas de date, on met par défaut en historique
                $history[] = $row;
            }
        }

        // Tri : à venir (du plus proche au plus lointain)
        usort($upcoming, static fn(array $a, array $b) =>
            ($a['dateSeance'] <=> $b['dateSeance']) ?: ($a['id'] <=> $b['id'])
        );

        // Historique (du plus récent au plus ancien)
        usort($history, static fn(array $a, array $b) =>
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
    $note    = max(1, min(5, (int)($data['note'] ?? 0)));
    $comment = trim((string)($data['comment'] ?? ''));

    $reservation = $reservationRepo->find($resId);
    if (!$reservation || $reservation->getUser() !== $user) {
        return $this->json(['ok' => false, 'error' => 'reservation_not_found'], 404);
    }

    $film = $reservation->getSeance()->getFilm();

    // Bloque les doublons (qu’ils soient déjà validés ou encore en attente)
    $existing = $avisRepo->findOneBy(['film' => $film, 'user' => $user]);
    if ($existing) {
        return $this->json(['ok' => false, 'error' => 'already_rated_or_pending'], 409);
    }

    $avis = (new Avis())
        ->setUser($user)
        ->setFilm($film)
        ->setNoteSur5($note)
        ->setCommentaire($comment ?: null)
        ->setValide(false) // <-- IMPORTANT : en attente de validation employé
        ->setCreatedAt(new \DateTimeImmutable());

    $em->persist($avis);
    $em->flush();

    // Moyenne/compte restent ceux des AVIS VALIDÉS uniquement (ta repo le fait déjà)
    $avg   = $avisRepo->getAverageNoteForFilm($film);
    $count = $avisRepo->countValidatedForFilm($film);

    return $this->json([
        'ok'        => true,
        'note'      => $note,
        'pending'   => true,      // <-- explicite côté front
        'validated' => false,     // <-- explicite côté front
        'average'   => $avg,      // inchangé (n’intègre pas cet avis)
        'count'     => $count,    // idem
        'filmId'    => $film->getId(),
        'resId'     => $reservation->getId(),
    ]);
}
}

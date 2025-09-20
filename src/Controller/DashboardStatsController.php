<?php

namespace App\Controller;

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardStatsController extends AbstractController
{
    #[Route('/admin/stats', name: 'admin_stats')]
    public function index(): Response
    {
        $stats = [];
        $collectionsNames = [];

        try {
            // Utilise l’URI docker si dispo, sinon fallback local
            $uri = $_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017';
            $dbName = $_ENV['MONGODB_DB'] ?? 'cinephoria';

            $client = new Client($uri);
            $db = $client->selectDatabase($dbName);
            $collection = $db->selectCollection('reservations_stats');

            // Liste des collections (optionnel)
            foreach ($db->listCollections() as $col) {
                $collectionsNames[] = $col->getName();
            }

            // 7 derniers jours (>= aujourd’hui-7 à 00:00)
            $sevenDaysAgo = (new \DateTimeImmutable('-7 days'))->setTime(0, 0);
            $cursor = $collection->find(
                [
                    // accepte "date" ou "jour" selon ce que tu enregistres
                    '$or' => [
                        ['date' => ['$gte' => new UTCDateTime($sevenDaysAgo->getTimestamp() * 1000)]],
                        ['jour' => ['$gte' => new UTCDateTime($sevenDaysAgo->getTimestamp() * 1000)]],
                    ],
                ],
                ['sort' => ['date' => -1, 'jour' => -1]]
            );

            // Normalise pour Twig (on convertit UTCDateTime -> \DateTimeImmutable)
            foreach ($cursor as $doc) {
                $film = $doc['film_titre']    ?? $doc['film_title'] ?? 'Inconnu';
                $nb   = $doc['total_places']  ?? $doc['nb_places']  ?? $doc['reservations_count'] ?? 0;

                $dateField = $doc['date'] ?? $doc['jour'] ?? null;
                $asDate = null;
                if ($dateField instanceof UTCDateTime) {
                    $asDate = $dateField->toDateTime(); // \DateTime
                }

                $stats[] = [
                    'film' => (string) $film,
                    'count' => (int) $nb,
                    'date' => $asDate,
                ];
            }
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Erreur MongoDB : ' . $e->getMessage());
        }

        return $this->render('dashboard_stats/index.html.twig', [
            'stats' => $stats,
            'collections' => $collectionsNames,
        ]);
    }
}

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
            $client = new Client('mongodb://localhost:27017');
            $db = $client->selectDatabase('Cinephoria'); // ✅ casse respectée
            $collection = $db->reservations_stats;

            // Liste des collections (optionnel)
            $collections = $db->listCollections();
            foreach ($collections as $collectionItem) {
                $collectionsNames[] = $collectionItem->getName();
            }

            // Requête : documents des 7 derniers jours
            $sevenDaysAgo = new \DateTime('-7 days');

            $cursor = $collection->find([
                'date' => [
                    '$gte' => new UTCDateTime($sevenDaysAgo->getTimestamp() * 1000),
                ]
            ], [
                'sort' => ['date' => -1]
            ]);

            foreach ($cursor as $doc) {
                $stats[] = $doc;
            }

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur MongoDB : ' . $e->getMessage());
        }

        return $this->render('dashboard_stats/index.html.twig', [
            'stats' => $stats,
            'collections' => $collectionsNames,
        ]);
    }

}

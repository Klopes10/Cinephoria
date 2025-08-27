<?php

namespace App\Controller\Admin;

use App\Entity\Film;
use App\Entity\Seance;
use App\Entity\Salle;
use App\Entity\Reservation;
use App\Entity\Avis;
use App\Entity\Contact;
use App\Entity\Incident;
use App\Entity\User;
use App\Entity\Cinema;
use App\Entity\Siege;
use App\Entity\Genre;
use App\Entity\Qualite;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect(
            $adminUrlGenerator->setController(FilmCrudController::class)->generateUrl()
        );
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addJsFile('js/reservation_dynamic.js')
            ->addJsFile('js/reservation_form.js');
    }

    #[Route('/admin/stats-mongodb', name: 'admin_mongo_stats')]
    public function mongoStats(): Response
    {
        $totaux = [];
        $lignes = []; // pour le détail film/date si tu veux lister

        try {
            $uri = $_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017';
            $dbName = $_ENV['MONGODB_DB'] ?? 'cinephoria';

            $client = new Client($uri);
            $collection = $client
                ->selectDatabase($dbName)
                ->selectCollection('reservations_stats');

            $sevenDaysAgo = (new \DateTimeImmutable('-7 days'))->setTime(0, 0);

            $cursor = $collection->find(
                [
                    '$or' => [
                        ['date' => ['$gte' => new UTCDateTime($sevenDaysAgo->getTimestamp() * 1000)]],
                        ['jour' => ['$gte' => new UTCDateTime($sevenDaysAgo->getTimestamp() * 1000)]],
                    ],
                ],
                ['sort' => ['date' => -1, 'jour' => -1]]
            );

            foreach ($cursor as $doc) {
                $titre = $doc['film_titre']    ?? $doc['film_title'] ?? 'Inconnu';
                $nb    = $doc['total_places']  ?? $doc['nb_places']  ?? $doc['reservations_count'] ?? 0;
                $dateField = $doc['date'] ?? $doc['jour'] ?? null;

                $asDate = null;
                if ($dateField instanceof UTCDateTime) {
                    $asDate = $dateField->toDateTime();
                }

                $totaux[$titre] = ($totaux[$titre] ?? 0) + (int)$nb;

                $lignes[] = [
                    'film'  => (string)$titre,
                    'count' => (int)$nb,
                    'date'  => $asDate,
                ];
            }
        } catch (\Throwable $e) {
            $lignes = [];
            $totaux = [];
            $this->addFlash('danger', 'Erreur MongoDB : ' . $e->getMessage());
        }

        // Tu peux avoir une page dédiée EasyAdmin ou une page Twig simple
        return $this->render('admin/mongo_stats.html.twig', [
            'totaux' => $totaux,
            'lignes' => $lignes,
        ]);
    }

    #[Route('/admin/test-mongo', name: 'admin_test_mongo')]
    public function testMongoInsert(): Response
    {
        try {
            $uri = $_ENV['MONGODB_URI'] ?? 'mongodb://mongo:27017';
            $dbName = $_ENV['MONGODB_DB'] ?? 'cinephoria';

            $client = new Client($uri);
            $collection = $client
                ->selectDatabase($dbName)
                ->selectCollection('reservations_stats');

            $document = [
                'film_titre' => 'Test Film',
                'total_places' => 3,
                'date' => new UTCDateTime((new \DateTimeImmutable())->getTimestamp() * 1000),
            ];

            $result = $collection->insertOne($document);

            return new Response("Insertion test MongoDB réussie. ID : " . (string)$result->getInsertedId());
        } catch (\Throwable $e) {
            return new Response("Erreur MongoDB : " . $e->getMessage(), 500);
        }
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Cinéphoria');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        // Lien vers la page stats mongo
        yield MenuItem::linkToRoute('Statistiques MongoDB', 'fas fa-chart-bar', 'admin_mongo_stats');

        yield MenuItem::section('Gestion du contenu');
        yield MenuItem::linkToCrud('Films', 'fas fa-film', Film::class);
        yield MenuItem::linkToCrud('Cinémas', 'fas fa-building', Cinema::class);
        yield MenuItem::linkToCrud('Séances', 'fas fa-clock', Seance::class);
        yield MenuItem::linkToCrud('Salles', 'fas fa-video', Salle::class);
        yield MenuItem::linkToCrud('Sièges', 'fas fa-chair', Siege::class)->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Genres', 'fas fa-tags', Genre::class)->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Qualités', 'fa fa-star', Qualite::class)->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::section('Modération & Activité');
        yield MenuItem::linkToCrud('Avis', 'fas fa-star', Avis::class);
        yield MenuItem::linkToCrud('Réservations', 'fas fa-ticket-alt', Reservation::class);
        yield MenuItem::linkToCrud('Incidents', 'fas fa-exclamation-triangle', Incident::class);
        yield MenuItem::linkToCrud('Contacts', 'fas fa-envelope', Contact::class);

        yield MenuItem::section('Utilisateurs')->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class)->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::section();
        yield MenuItem::linkToUrl('Retour au site', 'fas fa-arrow-left', $this->generateUrl('app_home'));
    }
}

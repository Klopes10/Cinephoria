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
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use MongoDB\BSON\UTCDateTime;

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

    #[Route('/admin/stats-mongodb', name: 'admin_mongo_stats')]
    public function mongoStats(): Response
    {
        $client = new \MongoDB\Client('mongodb://localhost:27017');
        $db = $client->selectDatabase('Cinephoria');
        $collection = $db->reservations_stats;

        $sevenDaysAgo = new \DateTimeImmutable('-7 days');

        $cursor = $collection->find([
            'date' => [
                '$gte' => new UTCDateTime($sevenDaysAgo->getTimestamp() * 1000),
            ]
        ], [
            'sort' => ['date' => -1]
        ]);

        $stats = iterator_to_array($cursor);

        foreach ($stats as &$stat) {
            if (isset($stat['date']) && $stat['date'] instanceof UTCDateTime) {
                $stat['date'] = $stat['date']->toDateTime();
            }
        }

        return $this->render('admin/mongo_stats.html.twig', [
            'stats' => $stats,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Cinéphoria');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToRoute('Statistiques MongoDB', 'fas fa-chart-bar', 'admin_mongo_stats');

        yield MenuItem::section('Gestion du contenu');
        yield MenuItem::linkToCrud('Films', 'fas fa-film', Film::class);
        yield MenuItem::linkToCrud('Cinema', 'fas fa-building', Cinema::class);
        yield MenuItem::linkToCrud('Séances', 'fas fa-clock', Seance::class);
        yield MenuItem::linkToCrud('Salles', 'fas fa-video', Salle::class);
        yield MenuItem::linkToCrud('Sièges', 'fas fa-chair', Siege::class);

        yield MenuItem::section('Modération & Activité');
        yield MenuItem::linkToCrud('Avis', 'fas fa-star', Avis::class);
        yield MenuItem::linkToCrud('Réservations', 'fas fa-ticket-alt', Reservation::class);
        yield MenuItem::linkToCrud('Incidents', 'fas fa-exclamation-triangle', Incident::class);
        yield MenuItem::linkToCrud('Contacts', 'fas fa-envelope', Contact::class);

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            yield MenuItem::section('Utilisateurs');
            yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        }

        yield MenuItem::section();
        yield MenuItem::linkToUrl('Retour au site', 'fas fa-arrow-left', $this->generateUrl('app_home'));
    }
}

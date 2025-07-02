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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class AdminDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect(
            $adminUrlGenerator->setController(FilmCrudController::class)->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Cinéphoria');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

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

        yield MenuItem::section('Utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        

        yield MenuItem::section();
        yield MenuItem::linkToUrl('Retour au site', 'fas fa-arrow-left', $this->generateUrl('app_home'));
    }
}

<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    #[IsGranted('ROLE_USER')]
    public function account(ReservationRepository $reservationRepo): Response
    {
        $user = $this->getUser();

        $reservations = $reservationRepo->findBy(['user' => $user]);

        return $this->render('account/index.html.twig', [
            'reservations' => $reservations
        ]);
    }

}

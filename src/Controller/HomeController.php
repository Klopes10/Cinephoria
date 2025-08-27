<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(FilmRepository $filmRepository): Response
    {
        // Dernier mercredi (si aujourd'hui est mercredi, on prend aujourd'hui)
        $today = new \DateTimeImmutable('today');
        $mostRecentWednesday = ((int) $today->format('N') === 3)
            ? $today
            : new \DateTimeImmutable('last wednesday');

        $start = $mostRecentWednesday->setTime(0, 0, 0);
        $end   = $start->modify('+1 day');

        // Films publiés ce mercredi (badge "coup de cœur" d'abord, puis titre)
        $films = $filmRepository->createQueryBuilder('f')
            ->andWhere('f.datePublication >= :start')
            ->andWhere('f.datePublication < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('f.coupDeCoeur', 'DESC')
            ->addOrderBy('f.titre', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('home/index.html.twig', [
            'films' => $films,
        ]);
    }
}

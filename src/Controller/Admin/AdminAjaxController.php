<?php 

// src/Controller/Admin/AdminAjaxController.php

namespace App\Controller\Admin;

use App\Entity\Film;
use App\Entity\Seance;
use App\Repository\FilmRepository;
use App\Repository\SeanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminAjaxController extends AbstractController
{
    #[Route('/admin/ajax/films', name: 'admin_ajax_films')]
    public function films(Request $request, FilmRepository $filmRepo): JsonResponse
    {
        $cinemaId = $request->query->get('cinemaId');
        $films = $filmRepo->createQueryBuilder('f')
            ->join('f.seances', 's')
            ->where('s.cinema = :cinemaId')
            ->setParameter('cinemaId', $cinemaId)
            ->groupBy('f.id')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($films as $film) {
            $data[] = ['id' => $film->getId(), 'titre' => $film->getTitre()];
        }

        return new JsonResponse($data);
    }

    #[Route('/admin/ajax/seances', name: 'admin_ajax_seances')]
    public function seances(Request $request, SeanceRepository $seanceRepo): JsonResponse
    {
        $filmId = $request->query->get('filmId');
        $cinemaId = $request->query->get('cinemaId');

        $seances = $seanceRepo->createQueryBuilder('s')
            ->where('s.film = :filmId')
            ->andWhere('s.cinema = :cinemaId')
            ->setParameters(['filmId' => $filmId, 'cinemaId' => $cinemaId])
            ->orderBy('s.date', 'ASC')
            ->getQuery()
            ->getResult();

        $data = [];
        foreach ($seances as $seance) {
            $data[] = [
                'id' => $seance->getId(),
                'label' => $seance->getDate()->format('d/m/Y') . ' à ' . $seance->getHeureDebut()->format('H:i')
            ];
        }

        return new JsonResponse($data);
    }
}

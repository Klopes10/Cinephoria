<?php

namespace App\Controller\Admin;

use App\Entity\Cinema;
use App\Entity\Film;
use App\Repository\SeanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SeanceAjaxController extends AbstractController
{
    #[Route('/admin/ajax/films-par-cinema/{cinemaId}', name: 'ajax_films_par_cinema')]
    public function filmsParCinema(int $cinemaId, SeanceRepository $seanceRepo): JsonResponse
    {
        $seances = $seanceRepo->findBy(['cinema' => $cinemaId]);

        $films = [];
        foreach ($seances as $seance) {
            $film = $seance->getFilm();
            if ($film && !isset($films[$film->getId()])) {
                $films[$film->getId()] = $film->getTitre();
            }
        }

        return new JsonResponse($films);
    }
}

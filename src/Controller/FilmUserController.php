<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Film;

final class FilmUserController extends AbstractController
{
    #[Route('/films', name: 'app_films')]
    public function index(FilmRepository $filmRepository): Response
    {
        $films = $filmRepository->findAll();

        return $this->render('film_user/index.html.twig', [
            'films' => $films,
        ]);
    }

    #[Route('/films/{id}', name: "app_films_show")]
    public function show(Film $film): Response
    {
        return $this->render('film_user/show.html.twig',[

            'film' => $film
        ]);
    }
}



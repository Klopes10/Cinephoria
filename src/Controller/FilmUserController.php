<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Film;
use App\Form\AvisTypeForm;
use App\Repository\CinemaRepository;
use App\Repository\FilmRepository;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FilmUserController extends AbstractController
{
    #[Route('/films', name: 'app_films')]
    public function index(
        FilmRepository $filmRepository,
        CinemaRepository $cinemaRepository,
        GenreRepository $genreRepository
    ): Response {
        $films = $filmRepository->findAll();
        $cinemas = $cinemaRepository->findAll();
        $genres = $genreRepository->findAll();

        $villes = ['france' => [], 'belgique' => []];
        foreach ($cinemas as $cinema) {
            $pays = strtolower($cinema->getPays());
            $ville = $cinema->getVille();
            if (isset($villes[$pays]) && !in_array($ville, $villes[$pays])) {
                $villes[$pays][] = $ville;
            }
        }
        foreach ($villes as &$liste) {
            sort($liste);
        }

        return $this->render('film_user/index.html.twig', [
            'films' => $films,
            'villes' => $villes,
            'genres' => $genres,
        ]);
    }

    #[Route('/films/{id<\d+>}', name: 'app_films_show')]
    #[IsGranted('ROLE_USER')]
    public function show(
        Film $film,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $avis = new Avis();
        $avis->setFilm($film);
        $avis->setCreatedAt(new \DateTimeImmutable());

        if ($this->getUser()) {
            $avis->setUser($this->getUser());
        }

        $form = $this->createForm(AvisTypeForm::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $avis->setValide(true); // À adapter selon ta logique de validation
            $em->persist($avis);
            $em->flush();

            $this->addFlash('success', 'Votre avis a été enregistré.');
            return $this->redirectToRoute('app_films_show', ['id' => $film->getId()]);
        }

        return $this->render('film_user/show.html.twig', [
            'film' => $film,
            'avis_form' => $form->createView(),
        ]);
    }

    #[Route('/films/filter', name: 'films_filtered', methods: ['GET'])]
    public function filmsFiltered(Request $request, FilmRepository $filmRepository): Response
    {
        $start = microtime(true);

        $ville = $request->query->get('ville');
        $genre = $request->query->get('genre');
        $jour = $request->query->get('jour');

        $films = $filmRepository->findFiltered($ville, $genre, $jour);

        $duration = round(microtime(true) - $start, 2);
        dump("Temps d'exécution : " . $duration . "s");

        return $this->render('film_user/_films_list.html.twig', [
            'films' => $films,
        ]);
    }
}

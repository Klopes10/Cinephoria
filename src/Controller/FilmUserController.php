<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Film;
use App\Form\AvisTypeForm;
use App\Repository\CinemaRepository;
use App\Repository\FilmRepository;
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
        CinemaRepository $cinemaRepository
    ): Response {
        $films = $filmRepository->findAll();
        $cinemas = $cinemaRepository->findAll();

        $villes = ['france' => [], 'belgique' => []];

        foreach ($cinemas as $cinema) {
            $pays = strtolower($cinema->getPays());
            $ville = $cinema->getVille();

            if (isset($villes[$pays]) && !in_array($ville, $villes[$pays])) {
                $villes[$pays][] = $ville;
            }
        }

        // Tri alphabétique des villes
        foreach ($villes as &$liste) {
            sort($liste);
        }

        return $this->render('film_user/index.html.twig', [
            'films' => $films,
            'villes' => $villes,
        ]);
    }

    #[Route('/films/{id}', name: 'app_films_show')]
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
            $avis->setValide(true); // À modifier selon la politique de modération
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

    #[Route('/films/par-ville', name: 'films_par_ville', methods: ['GET'])]
    public function filmsParVille(Request $request, FilmRepository $filmRepository): Response
    {
        $ville = $request->query->get('ville');

        if (!$ville) {
            return new Response('<p>Ville non définie.</p>', 400);
        }

        // Récupération des films diffusés dans cette ville
        $films = $filmRepository->createQueryBuilder('f')
            ->distinct()
            ->join('f.seances', 's')
            ->join('s.salle', 'sa')
            ->join('sa.cinema', 'c')
            ->where('c.ville = :ville')
            ->setParameter('ville', $ville)
            ->getQuery()
            ->getResult();

        return $this->render('film_user/_films_list.html.twig', [
            'films' => $films,
        ]);
    }
}

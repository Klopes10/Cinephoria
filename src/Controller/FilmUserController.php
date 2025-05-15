<?php

namespace App\Controller;

use App\Repository\FilmRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Film;
use App\Entity\Avis;
use App\Form\AvisTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
            $avis->setValide(true); // Ou false si modération
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
    
}



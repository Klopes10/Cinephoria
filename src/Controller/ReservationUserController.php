<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Seance;
use App\Form\ReservationTypeForm;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ReservationUserController extends AbstractController
{
    #[Route('/reservation/seance/{id}', name: 'app_reservation_seance')]
    #[IsGranted('ROLE_USER')]
    public function reserver(
        Request $request,
        Seance $seance,
        EntityManagerInterface $em
    ): Response {
        $reservation = new Reservation();
        $reservation->setSeance($seance);
        $reservation->setUser($this->getUser());
        $reservation->setCreatedAt(new \DateTimeImmutable());

        // Associe l'utilisateur si connecté (sera null sinon, pas bloquant)
        if ($this->getUser()) {
            $reservation->setUser($this->getUser());
        }

        $form = $this->createForm(ReservationTypeForm::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $placesDemandees = $reservation->getNombrePlaces();
            $placesRestantes = $seance->getPlacesDisponible();
            
            if($placesDemandees > $placesRestantes){
                $this->addFlash('error', 'Pas assez de place disponibles pour cette séance');
                return $this->redirectToRoute('app_films_show',[
                    'id' => $seance->getFilm()->getId(),
                ]);
            }
            $seance->setPlacesDisponible($placesRestantes - $placesDemandees);
            $em->persist($reservation);
            $em->flush();

            $this->addFlash('success', 'Réservation enregistrée avec succès !');

            return $this->redirectToRoute('app_films_show', [
                'id' => $seance->getFilm()->getId(),
            ]);
        }

        return $this->render('reservation_user/form.html.twig', [
            'form' => $form->createView(),
            'seance' => $seance,
        ]);
    }

    
}

<?php 

// src/Controller/Admin/AdminAjaxController.php
namespace App\Controller\Admin;

use App\Repository\SalleRepository;
use App\Entity\Cinema;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/ajax')]
class AdminAjaxController extends AbstractController
{
    #[Route('/salles-by-cinema/{id}', name: 'admin_ajax_salles_by_cinema', methods: ['GET'])]
    public function sallesByCinema(Cinema $cinema, SalleRepository $repo): JsonResponse
    {
        $salles = $repo->createQueryBuilder('s')
            ->andWhere('s.cinema = :cinema')
            ->setParameter('cinema', $cinema)
            ->orderBy('s.nom', 'ASC')
            ->getQuery()->getResult();

        $data = array_map(fn($s) => [
            'id'    => $s->getId(),
            'label' => (string) $s, // __toString() => nom
        ], $salles);

        return $this->json($data);
    }
}

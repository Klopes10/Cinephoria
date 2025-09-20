<?php

namespace App\Controller\Api;

use App\Entity\Incident;
use App\Entity\Salle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class IncidentController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    // --- Liste des salles (pour l'app desktop)
    #[Route('/salles', name: 'salles_index', methods: ['GET'])]
    public function salles(): JsonResponse
    {
        $salles = $this->em->getRepository(Salle::class)->createQueryBuilder('s')
            ->join('s.cinema', 'c')->addSelect('c')
            ->orderBy('c.nom', 'ASC')->addOrderBy('s.nom', 'ASC')
            ->getQuery()->getResult();

        $out = array_map(function (Salle $s) {
            return [
                'id'     => $s->getId(),
                'nom'    => $s->getNom(),
                'cinema' => ['nom' => $s->getCinema()?->getNom()],
            ];
        }, $salles);

        return $this->json($out);
    }

    // --- Incidents d'une salle
    #[Route('/salles/{id}/incidents', name: 'salle_incidents', methods: ['GET'])]
    public function salleIncidents(Salle $salle): JsonResponse
    {
        $incidents = $this->em->getRepository(Incident::class)->findBy(
            ['salle' => $salle],
            ['createdAt' => 'DESC']
        );

        $out = array_map(function (Incident $i) {
            return [
                'id'              => $i->getId(),
                'titre'           => $i->getTitre(),
                'description'     => $i->getDescription(),
                'resolu'          => (bool)$i->isResolu(),
                'createdAt'       => $i->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'dateSignalement' => $i->getDateSignalement()?->format(\DateTimeInterface::ATOM),
            ];
        }, $incidents);

        return $this->json($out);
    }

    // --- Création d'un incident
    #[Route('/incidents', name: 'incident_create', methods: ['POST'])]
    public function create(Request $req): JsonResponse
    {
        $data = json_decode($req->getContent() ?: '{}', true);

        $salleId     = $data['salleId'] ?? null;
        $titre       = trim((string)($data['titre'] ?? ''));
        $description = trim((string)($data['description'] ?? ''));

        if (!$salleId || $titre === '' || $description === '') {
            return $this->json(['message' => 'Champs manquants (salleId, titre, description)'], 400);
        }

        $salle = $this->em->getRepository(Salle::class)->find((int)$salleId);
        if (!$salle) {
            return $this->json(['message' => 'Salle introuvable'], 404);
        }

        $incident = new Incident();
        $incident->setSalle($salle);
        $incident->setTitre($titre);
        $incident->setDescription($description);
        $incident->setDateSignalement(new \DateTimeImmutable()); // obligatoire
        $incident->setResolu(false);

        // createdBy si présent
        if (method_exists($incident, 'setCreatedBy')) {
            $user = $this->getUser();
            if (!$user) {
                return $this->json(['message' => 'Unauthorized'], 401);
            }
            $incident->setCreatedBy($user);
        }

        $this->em->persist($incident);
        $this->em->flush();

        return $this->json([
            'id'      => $incident->getId(),
            'message' => 'Incident créé avec succès'
        ], 201);
    }
}

<?php

namespace App\EventListener;

use App\Entity\Seance;
use App\Entity\Siege;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsEntityListener(
    event: 'postPersist',
    method: 'generateSieges',
    entity: Seance::class
)]
class GenerateSiegesOnSeanceCreatedListener
{
    public function generateSieges(Seance $seance, PostPersistEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        $salle = $seance->getSalle();
        if (count($seance->getSieges()) > 0) {
            return;
        }


        if (!$salle) {
            return;
        }

        $nombrePlaces = $salle->getNombrePlaces();

        for ($i = 1; $i <= $nombrePlaces; $i++) {
            $siege = new Siege();
            $siege->setNumero($i);
        
            // ✅ Les 6 premiers sièges sont toujours PMR
            $siege->setIsPMR($i <= 5);
        
            $siege->setIsReserved(false);
            $siege->setSeance($seance);
        
            $entityManager->persist($siege);
        }
        
        $entityManager->flush();
        return;
        
    }
}

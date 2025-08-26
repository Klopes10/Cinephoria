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
    private const STD_COLS_PER_ROW = 10; // nb sièges par rangée standard
    private const PMR_COUNT        = 5;  // A1..A5 = PMR

    public function generateSieges(Seance $seance, PostPersistEventArgs $args): void
    {
        $em    = $args->getObjectManager();
        $salle = $seance->getSalle();

        if (!$salle) {
            return;
        }

        // Ne régénère pas si la séance a déjà des sièges
        if ($seance->getSieges()->count() > 0) {
            return;
        }

        $nb = (int) $salle->getNombrePlaces();
        if ($nb <= 0) {
            return;
        }

        for ($n = 1; $n <= $nb; $n++) {
            $siege = new Siege();
            $siege->setNumero($n);
            $siege->setSeance($seance);
            $siege->setIsReserved(false);

            if ($n <= self::PMR_COUNT) {
                // PMR : A1..A5
                $siege->setIsPMR(true);
                $siege->setCode('A' . $n);
            } else {
                // Standards, commencent à B
                $index      = $n - self::PMR_COUNT;     // 1,2,3...
                $rowIndex   = intdiv($index - 1, self::STD_COLS_PER_ROW);  // 0=B, 1=C, ...
                $col        = (($index - 1) % self::STD_COLS_PER_ROW) + 1; // 1..10
                $rowLetter  = chr(ord('B') + $rowIndex);
                $siege->setIsPMR(false);
                $siege->setCode($rowLetter . $col);
            }

            $em->persist($siege);
        }

        $em->flush();
    }
}

<?php

namespace App\Service;

use App\Entity\Reservation;
use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

class MongoReservationsLogger
{
    public function __construct(
        private readonly Client $mongoClient,
        private readonly string $mongoDbName = 'cinephoria',
    ) {}

    /**
     * Agrège par (film, jour) avec un upsert : on incrémente total_places.
     */
    public function log(Reservation $reservation): void
    {
        try {
            $db = $this->mongoClient->selectDatabase($this->mongoDbName);
            $col = $db->selectCollection('reservations_stats');

            $seance = $reservation->getSeance();
            $film   = $seance?->getFilm();
            $salle  = $seance?->getSalle();
            $cinema = $salle?->getCinema();

            $filmTitle = $film?->getTitre() ?? 'Inconnu';
            $places    = (int) $reservation->getNombrePlaces();

            // Jour à minuit (timezone PHP), converti en UTCDateTime (ms)
            $day = ($seance?->getDate() ?? new \DateTimeImmutable())->setTime(0, 0);
            $jourBson = new UTCDateTime($day->getTimestamp() * 1000);

            // Clef d’agrégat
            $filter = [
                'film_titre' => $filmTitle,
                'jour'       => $jourBson,
            ];

            // On garde aussi quelques champs descriptifs (ville, pays, seance)
            $update = [
                '$setOnInsert' => [
                    'film_titre' => $filmTitle,
                    'jour'       => $jourBson,
                    'ville'      => $cinema?->getVille(),
                    'pays'       => $cinema?->getPays(),
                ],
                '$inc' => [
                    'total_places' => $places,
                ],
            ];

            $col->updateOne($filter, $update, ['upsert' => true]);
        } catch (\Throwable $e) {
            // on avale l’erreur Mongo pour ne pas casser le tunnel de réservation
            // (tu peux logger si besoin)
        }
    }
}

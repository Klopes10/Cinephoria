<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250730133307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id SERIAL NOT NULL, user_id INT NOT NULL, film_id INT NOT NULL, note_sur5 INT NOT NULL, commentaire TEXT DEFAULT NULL, valide BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F91ABF0A76ED395 ON avis (user_id)');
        $this->addSql('CREATE INDEX IDX_8F91ABF0567F5183 ON avis (film_id)');
        $this->addSql('COMMENT ON COLUMN avis.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE cinema (id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, pays VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, code_postal VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE contact (id SERIAL NOT NULL, nom_utilisateur VARCHAR(255) NOT NULL, titre VARCHAR(255) NOT NULL, description TEXT NOT NULL, date_envoi TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN contact.date_envoi IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE film (id SERIAL NOT NULL, genre_id INT NOT NULL, titre VARCHAR(255) NOT NULL, synopsis TEXT NOT NULL, age_minimum INT DEFAULT NULL, affiche VARCHAR(255) NOT NULL, coup_de_coeur BOOLEAN NOT NULL, note_moyenne DOUBLE PRECISION DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8244BE224296D31F ON film (genre_id)');
        $this->addSql('COMMENT ON COLUMN film.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE genre (id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE incident (id SERIAL NOT NULL, salle_id INT NOT NULL, description TEXT DEFAULT NULL, date_signalement TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, resolu BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D03A11ADC304035 ON incident (salle_id)');
        $this->addSql('COMMENT ON COLUMN incident.date_signalement IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN incident.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE reservation (id SERIAL NOT NULL, user_id INT NOT NULL, seance_id INT NOT NULL, nombre_places INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, prix_total DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_42C84955A76ED395 ON reservation (user_id)');
        $this->addSql('CREATE INDEX IDX_42C84955E3797A94 ON reservation (seance_id)');
        $this->addSql('COMMENT ON COLUMN reservation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE reservation_siege (reservation_id INT NOT NULL, siege_id INT NOT NULL, PRIMARY KEY(reservation_id, siege_id))');
        $this->addSql('CREATE INDEX IDX_24796450B83297E7 ON reservation_siege (reservation_id)');
        $this->addSql('CREATE INDEX IDX_24796450BF006E8B ON reservation_siege (siege_id)');
        $this->addSql('CREATE TABLE salle (id SERIAL NOT NULL, cinema_id INT NOT NULL, nom VARCHAR(255) NOT NULL, nombre_places INT NOT NULL, qualite VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4E977E5CB4CB84B6 ON salle (cinema_id)');
        $this->addSql('COMMENT ON COLUMN salle.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE seance (id SERIAL NOT NULL, film_id INT NOT NULL, salle_id INT NOT NULL, cinema_id INT NOT NULL, date DATE NOT NULL, heure_debut TIME(0) WITHOUT TIME ZONE NOT NULL, heure_fin TIME(0) WITHOUT TIME ZONE NOT NULL, qualite VARCHAR(255) NOT NULL, places_disponible INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, prix DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DF7DFD0E567F5183 ON seance (film_id)');
        $this->addSql('CREATE INDEX IDX_DF7DFD0EDC304035 ON seance (salle_id)');
        $this->addSql('CREATE INDEX IDX_DF7DFD0EB4CB84B6 ON seance (cinema_id)');
        $this->addSql('COMMENT ON COLUMN seance.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN seance.heure_debut IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN seance.heure_fin IS \'(DC2Type:time_immutable)\'');
        $this->addSql('COMMENT ON COLUMN seance.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE siege (id SERIAL NOT NULL, seance_id INT NOT NULL, numero INT NOT NULL, is_pmr BOOLEAN DEFAULT false NOT NULL, is_reserved BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6706B4F7E3797A94 ON siege (seance_id)');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, forname VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, create_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".create_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0567F5183 FOREIGN KEY (film_id) REFERENCES film (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE film ADD CONSTRAINT FK_8244BE224296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11ADC304035 FOREIGN KEY (salle_id) REFERENCES salle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955E3797A94 FOREIGN KEY (seance_id) REFERENCES seance (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation_siege ADD CONSTRAINT FK_24796450B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation_siege ADD CONSTRAINT FK_24796450BF006E8B FOREIGN KEY (siege_id) REFERENCES siege (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5CB4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0E567F5183 FOREIGN KEY (film_id) REFERENCES film (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0EDC304035 FOREIGN KEY (salle_id) REFERENCES salle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0EB4CB84B6 FOREIGN KEY (cinema_id) REFERENCES cinema (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE siege ADD CONSTRAINT FK_6706B4F7E3797A94 FOREIGN KEY (seance_id) REFERENCES seance (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF0A76ED395');
        $this->addSql('ALTER TABLE avis DROP CONSTRAINT FK_8F91ABF0567F5183');
        $this->addSql('ALTER TABLE film DROP CONSTRAINT FK_8244BE224296D31F');
        $this->addSql('ALTER TABLE incident DROP CONSTRAINT FK_3D03A11ADC304035');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955A76ED395');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955E3797A94');
        $this->addSql('ALTER TABLE reservation_siege DROP CONSTRAINT FK_24796450B83297E7');
        $this->addSql('ALTER TABLE reservation_siege DROP CONSTRAINT FK_24796450BF006E8B');
        $this->addSql('ALTER TABLE salle DROP CONSTRAINT FK_4E977E5CB4CB84B6');
        $this->addSql('ALTER TABLE seance DROP CONSTRAINT FK_DF7DFD0E567F5183');
        $this->addSql('ALTER TABLE seance DROP CONSTRAINT FK_DF7DFD0EDC304035');
        $this->addSql('ALTER TABLE seance DROP CONSTRAINT FK_DF7DFD0EB4CB84B6');
        $this->addSql('ALTER TABLE siege DROP CONSTRAINT FK_6706B4F7E3797A94');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE cinema');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE film');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE incident');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE reservation_siege');
        $this->addSql('DROP TABLE salle');
        $this->addSql('DROP TABLE seance');
        $this->addSql('DROP TABLE siege');
        $this->addSql('DROP TABLE "user"');
    }
}

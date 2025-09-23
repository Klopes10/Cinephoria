<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration, rendue idempotente pour Postgres.
 * - Normalisation des types vers INT et TIMESTAMP(0) WITHOUT TIME ZONE
 * - Renommage des index vers les noms attendus par Doctrine (avec IF EXISTS)
 * - Ajout FK seance.qualite_id sécurisé (IF NOT EXISTS via bloc DO)
 */
final class Version20250923142603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize types + rename legacy indexes to Doctrine naming; guard operations with IF EXISTS / DO $$ blocks (PostgreSQL).';
    }

    public function up(Schema $schema): void
    {
        // Types + commentaires (idempotents)
        $this->addSql('ALTER TABLE avis ALTER id TYPE INT');
        $this->addSql('ALTER TABLE avis ALTER user_id TYPE INT');
        $this->addSql('ALTER TABLE avis ALTER film_id TYPE INT');
        $this->addSql('ALTER TABLE avis ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql("COMMENT ON COLUMN avis.created_at IS '(DC2Type:datetime_immutable)'");

        $this->addSql('ALTER TABLE cinema ALTER id TYPE INT');

        $this->addSql('ALTER TABLE contact ALTER id TYPE INT');
        $this->addSql('ALTER TABLE contact ALTER date_envoi TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql("COMMENT ON COLUMN contact.date_envoi IS '(DC2Type:datetime_immutable)'");

        $this->addSql('ALTER TABLE film ALTER id TYPE INT');
        $this->addSql('ALTER TABLE film ALTER genre_id TYPE INT');
        $this->addSql('ALTER TABLE film ALTER date_publication TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql("COMMENT ON COLUMN film.date_publication IS '(DC2Type:datetime_immutable)'");

        // Renommage d’index (si présents) vers les noms Doctrine (idempotent)
        $this->addSql('ALTER INDEX IF EXISTS genre_id_idx RENAME TO IDX_8244BE224296D31F');

        $this->addSql('ALTER TABLE genre ALTER id TYPE INT');

        $this->addSql('ALTER TABLE incident ALTER id TYPE INT');
        $this->addSql('ALTER TABLE incident ALTER salle_id TYPE INT');
        $this->addSql('ALTER TABLE incident ALTER date_signalement TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE incident ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql("COMMENT ON COLUMN incident.date_signalement IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN incident.created_at IS '(DC2Type:datetime_immutable)'");

        $this->addSql('ALTER INDEX IF EXISTS idx_incident_salle_id RENAME TO IDX_3D03A11ADC304035');

        $this->addSql('ALTER TABLE qualite ALTER id TYPE INT');

        $this->addSql('ALTER TABLE reservation ALTER id TYPE INT');
        $this->addSql('ALTER TABLE reservation ALTER user_id TYPE INT');
        $this->addSql('ALTER TABLE reservation ALTER seance_id TYPE INT');
        $this->addSql('ALTER TABLE reservation ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql("COMMENT ON COLUMN reservation.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql('ALTER INDEX IF EXISTS user_id_idx RENAME TO IDX_42C84955A76ED395');

        $this->addSql('ALTER TABLE reservation_siege ALTER reservation_id TYPE INT');
        $this->addSql('ALTER TABLE reservation_siege ALTER siege_id TYPE INT');
        $this->addSql('ALTER INDEX IF EXISTS reservation_id_idx RENAME TO IDX_24796450B83297E7');
        $this->addSql('ALTER INDEX IF EXISTS siege_id_idx RENAME TO IDX_24796450BF006E8B');

        $this->addSql('ALTER TABLE salle ALTER id TYPE INT');
        $this->addSql('ALTER TABLE salle ALTER cinema_id TYPE INT');
        $this->addSql('ALTER TABLE salle ALTER qualite_id TYPE INT');
        $this->addSql('ALTER TABLE salle ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql("COMMENT ON COLUMN salle.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql('ALTER INDEX IF EXISTS cinema_id_idx RENAME TO IDX_4E977E5CB4CB84B6');
        $this->addSql('ALTER INDEX IF EXISTS qualite_id_idx RENAME TO IDX_4E977E5CA6338570');

        $this->addSql('ALTER TABLE seance ALTER id TYPE INT');
        $this->addSql('ALTER TABLE seance ALTER film_id TYPE INT');
        $this->addSql('ALTER TABLE seance ALTER salle_id TYPE INT');
        $this->addSql('ALTER TABLE seance ALTER cinema_id TYPE INT');
        $this->addSql('ALTER TABLE seance ALTER date TYPE DATE');
        $this->addSql('ALTER TABLE seance ALTER heure_debut TYPE TIME(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE seance ALTER heure_fin TYPE TIME(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE seance ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE seance ALTER qualite_id TYPE INT');
        $this->addSql("COMMENT ON COLUMN seance.date IS '(DC2Type:date_immutable)'");
        $this->addSql("COMMENT ON COLUMN seance.heure_debut IS '(DC2Type:time_immutable)'");
        $this->addSql("COMMENT ON COLUMN seance.heure_fin IS '(DC2Type:time_immutable)'");
        $this->addSql("COMMENT ON COLUMN seance.created_at IS '(DC2Type:datetime_immutable)'");

        // Ajout de la FK uniquement si elle n’existe pas
        $this->addSql(<<<'SQL'
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM pg_constraint
    WHERE conname = 'fk_df7dfd0ea6338570'
  ) THEN
    ALTER TABLE seance
      ADD CONSTRAINT FK_DF7DFD0EA6338570
      FOREIGN KEY (qualite_id) REFERENCES qualite (id)
      ON DELETE RESTRICT
      NOT DEFERRABLE INITIALLY IMMEDIATE;
  END IF;
END$$;
SQL);

        $this->addSql('ALTER INDEX IF EXISTS film_id_idx RENAME TO IDX_DF7DFD0E567F5183');
        $this->addSql('ALTER INDEX IF EXISTS salle_id_idx RENAME TO IDX_DF7DFD0EDC304035');
        $this->addSql('ALTER INDEX IF EXISTS idx_seance_qualite RENAME TO IDX_DF7DFD0EA6338570');

        $this->addSql('ALTER TABLE siege ALTER id TYPE INT');
        $this->addSql('ALTER TABLE siege ALTER seance_id TYPE INT');
        $this->addSql('ALTER INDEX IF EXISTS seance_id_idx RENAME TO IDX_6706B4F7E3797A94');

        $this->addSql('ALTER TABLE "user" ALTER id TYPE INT');
        $this->addSql('ALTER TABLE "user" ALTER create_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER reset_password_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql("COMMENT ON COLUMN \"user\".create_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN \"user\".reset_password_expires_at IS '(DC2Type:datetime_immutable)'");

        $this->addSql('ALTER INDEX IF EXISTS user_email_key RENAME TO UNIQ_IDENTIFIER_EMAIL');
    }

    public function down(Schema $schema): void
    {
        // Recréation du schéma public si besoin
        $this->addSql('CREATE SCHEMA IF NOT EXISTS public');

        // Supprimer la FK si elle existe
        $this->addSql(<<<'SQL'
DO $$
BEGIN
  IF EXISTS (
    SELECT 1
    FROM pg_constraint
    WHERE conname = 'fk_df7dfd0ea6338570'
  ) THEN
    ALTER TABLE seance DROP CONSTRAINT FK_DF7DFD0EA6338570;
  END IF;
END$$;
SQL);

        // Inversions des modifications de types
        $this->addSql('ALTER TABLE genre ALTER id TYPE BIGINT');

        $this->addSql('ALTER TABLE seance ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE seance ALTER film_id TYPE BIGINT');
        $this->addSql('ALTER TABLE seance ALTER salle_id TYPE BIGINT');
        $this->addSql('ALTER TABLE seance ALTER cinema_id TYPE BIGINT');
        $this->addSql('ALTER TABLE seance ALTER qualite_id TYPE BIGINT');
        $this->addSql('ALTER TABLE seance ALTER date TYPE DATE');
        $this->addSql('ALTER TABLE seance ALTER heure_debut TYPE TIME(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE seance ALTER heure_fin TYPE TIME(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE seance ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN seance.date IS NULL');
        $this->addSql('COMMENT ON COLUMN seance.heure_debut IS NULL');
        $this->addSql('COMMENT ON COLUMN seance.heure_fin IS NULL');
        $this->addSql('COMMENT ON COLUMN seance.created_at IS NULL');

        // Renommages inverse (avec IF EXISTS)
        $this->addSql('ALTER INDEX IF EXISTS idx_df7dfd0e567f5183 RENAME TO film_id_idx');
        $this->addSql('ALTER INDEX IF EXISTS idx_df7dfd0ea6338570 RENAME TO idx_seance_qualite');
        $this->addSql('ALTER INDEX IF EXISTS idx_df7dfd0edc304035 RENAME TO salle_id_idx');

        $this->addSql('ALTER TABLE avis ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE avis ALTER user_id TYPE BIGINT');
        $this->addSql('ALTER TABLE avis ALTER film_id TYPE BIGINT');
        $this->addSql('ALTER TABLE avis ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN avis.created_at IS NULL');

        $this->addSql('ALTER TABLE salle ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE salle ALTER cinema_id TYPE BIGINT');
        $this->addSql('ALTER TABLE salle ALTER qualite_id TYPE BIGINT');
        $this->addSql('ALTER TABLE salle ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN salle.created_at IS NULL');

        $this->addSql('ALTER INDEX IF EXISTS idx_4e977e5cb4cb84b6 RENAME TO cinema_id_idx');
        $this->addSql('ALTER INDEX IF EXISTS idx_4e977e5ca6338570 RENAME TO qualite_id_idx');

        $this->addSql('ALTER TABLE qualite ALTER id TYPE BIGINT');

        $this->addSql('ALTER TABLE film ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE film ALTER genre_id TYPE BIGINT');
        $this->addSql('ALTER TABLE film ALTER date_publication TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN film.date_publication IS NULL');

        $this->addSql('ALTER INDEX IF EXISTS idx_8244be224296d31f RENAME TO genre_id_idx');

        $this->addSql('ALTER TABLE "user" ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE "user" ALTER create_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE "user" ALTER reset_password_expires_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN "user".create_at IS NULL');
        $this->addSql('COMMENT ON COLUMN "user".reset_password_expires_at IS NULL');

        $this->addSql('ALTER INDEX IF EXISTS uniq_identifier_email RENAME TO user_email_key');

        $this->addSql('ALTER TABLE incident ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE incident ALTER salle_id TYPE BIGINT');
        $this->addSql('ALTER TABLE incident ALTER date_signalement TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE incident ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN incident.date_signalement IS NULL');
        $this->addSql('COMMENT ON COLUMN incident.created_at IS NULL');

        $this->addSql('ALTER INDEX IF EXISTS idx_3d03a11adc304035 RENAME TO idx_incident_salle_id');

        $this->addSql('ALTER TABLE siege ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE siege ALTER seance_id TYPE BIGINT');
        $this->addSql('ALTER INDEX IF EXISTS idx_6706b4f7e3797a94 RENAME TO seance_id_idx');

        $this->addSql('ALTER TABLE cinema ALTER id TYPE BIGINT');

        $this->addSql('ALTER TABLE contact ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE contact ALTER date_envoi TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN contact.date_envoi IS NULL');

        $this->addSql('ALTER TABLE reservation_siege ALTER reservation_id TYPE BIGINT');
        $this->addSql('ALTER TABLE reservation_siege ALTER siege_id TYPE BIGINT');
        $this->addSql('ALTER INDEX IF EXISTS idx_24796450b83297e7 RENAME TO reservation_id_idx');
        $this->addSql('ALTER INDEX IF EXISTS idx_24796450bf006e8b RENAME TO siege_id_idx');

        $this->addSql('ALTER TABLE reservation ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE reservation ALTER user_id TYPE BIGINT');
        $this->addSql('ALTER TABLE reservation ALTER seance_id TYPE BIGINT');
        $this->addSql('ALTER TABLE reservation ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN reservation.created_at IS NULL');

        $this->addSql('ALTER INDEX IF EXISTS idx_42c84955a76ed395 RENAME TO user_id_idx');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250826144907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE siege DROP CONSTRAINT FK_6706B4F7E3797A94');
        $this->addSql('ALTER TABLE siege ADD code VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE siege ALTER is_pmr DROP DEFAULT');
        $this->addSql('ALTER TABLE siege ALTER is_reserved DROP DEFAULT');
        $this->addSql('ALTER TABLE siege ADD CONSTRAINT FK_6706B4F7E3797A94 FOREIGN KEY (seance_id) REFERENCES seance (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE siege DROP CONSTRAINT fk_6706b4f7e3797a94');
        $this->addSql('ALTER TABLE siege DROP code');
        $this->addSql('ALTER TABLE siege ALTER is_pmr SET DEFAULT false');
        $this->addSql('ALTER TABLE siege ALTER is_reserved SET DEFAULT false');
        $this->addSql('ALTER TABLE siege ADD CONSTRAINT fk_6706b4f7e3797a94 FOREIGN KEY (seance_id) REFERENCES seance (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

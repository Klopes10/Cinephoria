<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250826092403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE salle ADD qualite_id INT NOT NULL');
        $this->addSql('ALTER TABLE salle DROP qualite');
        $this->addSql('ALTER TABLE salle ADD CONSTRAINT FK_4E977E5CA6338570 FOREIGN KEY (qualite_id) REFERENCES qualite (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4E977E5CA6338570 ON salle (qualite_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE salle DROP CONSTRAINT FK_4E977E5CA6338570');
        $this->addSql('DROP INDEX IDX_4E977E5CA6338570');
        $this->addSql('ALTER TABLE salle ADD qualite VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE salle DROP qualite_id');
    }
}

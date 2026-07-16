<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716190202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exam_material ADD exam_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE exam_material ADD CONSTRAINT FK_3FDA3AA6578D5E91 FOREIGN KEY (exam_id) REFERENCES exam (id)');
        $this->addSql('CREATE INDEX IDX_3FDA3AA6578D5E91 ON exam_material (exam_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exam_material DROP FOREIGN KEY FK_3FDA3AA6578D5E91');
        $this->addSql('DROP INDEX IDX_3FDA3AA6578D5E91 ON exam_material');
        $this->addSql('ALTER TABLE exam_material DROP exam_id');
    }
}

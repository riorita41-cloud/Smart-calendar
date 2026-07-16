<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716181952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, answer LONGTEXT DEFAULT NULL, order_number INT NOT NULL, material_id INT NOT NULL, INDEX IDX_B6F7494EE308AC6F (material_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EE308AC6F FOREIGN KEY (material_id) REFERENCES exam_material (id)');
        $this->addSql('ALTER TABLE exam_material DROP file_path');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EE308AC6F');
        $this->addSql('DROP TABLE question');
        $this->addSql('ALTER TABLE exam_material ADD file_path VARCHAR(255) DEFAULT NULL');
    }
}

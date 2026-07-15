<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260710064523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exam_material (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, content VARCHAR(255) NOT NULL, file_type VARCHAR(10) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, uploaded_at DATETIME NOT NULL, no VARCHAR(255) NOT NULL, user_id INT NOT NULL, INDEX IDX_3FDA3AA6A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE exam_material ADD CONSTRAINT FK_3FDA3AA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exam_material DROP FOREIGN KEY FK_3FDA3AA6A76ED395');
        $this->addSql('DROP TABLE exam_material');
    }
}

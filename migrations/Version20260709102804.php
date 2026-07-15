<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709102804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avatar (id INT AUTO_INCREMENT NOT NULL, face_type VARCHAR(50) NOT NULL, skin_color VARCHAR(20) NOT NULL, hair_color VARCHAR(20) NOT NULL, hair_style VARCHAR(50) NOT NULL, body_type VARCHAR(50) NOT NULL, clothing VARCHAR(50) NOT NULL, clothing_color VARCHAR(20) NOT NULL, shoes VARCHAR(50) NOT NULL, shoes_color VARCHAR(20) NOT NULL, hat VARCHAR(50) NOT NULL, hat_color VARCHAR(20) NOT NULL, user_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_1677722FA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE avatar ADD CONSTRAINT FK_1677722FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avatar DROP FOREIGN KEY FK_1677722FA76ED395');
        $this->addSql('DROP TABLE avatar');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709183158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avatar ADD mouth_type VARCHAR(50) NOT NULL, ADD eye_type VARCHAR(50) NOT NULL, ADD eyebrow_type VARCHAR(50) NOT NULL, ADD accessories VARCHAR(50) NOT NULL, DROP face_type, DROP body_type, DROP shoes, DROP shoes_color, CHANGE skin_color skin_color VARCHAR(50) NOT NULL, CHANGE hair_color hair_color VARCHAR(50) NOT NULL, CHANGE clothing_color clothing_color VARCHAR(50) NOT NULL, CHANGE user_id user_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avatar ADD face_type VARCHAR(50) NOT NULL, ADD body_type VARCHAR(50) NOT NULL, ADD shoes VARCHAR(50) NOT NULL, ADD shoes_color VARCHAR(20) NOT NULL, DROP mouth_type, DROP eye_type, DROP eyebrow_type, DROP accessories, CHANGE skin_color skin_color VARCHAR(20) NOT NULL, CHANGE hair_color hair_color VARCHAR(20) NOT NULL, CHANGE clothing_color clothing_color VARCHAR(20) NOT NULL, CHANGE user_id user_id INT DEFAULT NULL');
    }
}

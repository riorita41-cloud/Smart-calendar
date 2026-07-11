<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260710034311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avatar DROP clothing, DROP clothing_color, DROP hat, DROP hat_color, DROP mouth_type, DROP eye_type, DROP eyebrow_type, DROP accessories, CHANGE gender gender VARCHAR(20) NOT NULL, CHANGE skin_color skin_color VARCHAR(20) NOT NULL, CHANGE hair_color hair_color VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avatar ADD clothing VARCHAR(50) NOT NULL, ADD clothing_color VARCHAR(50) NOT NULL, ADD hat VARCHAR(50) NOT NULL, ADD hat_color VARCHAR(20) NOT NULL, ADD mouth_type VARCHAR(50) NOT NULL, ADD eye_type VARCHAR(50) NOT NULL, ADD eyebrow_type VARCHAR(50) NOT NULL, ADD accessories VARCHAR(50) NOT NULL, CHANGE gender gender VARCHAR(20) DEFAULT \'male\', CHANGE skin_color skin_color VARCHAR(50) NOT NULL, CHANGE hair_color hair_color VARCHAR(50) NOT NULL');
    }
}

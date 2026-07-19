<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719145254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE study_session (id INT AUTO_INCREMENT NOT NULL, duration_minutes INT DEFAULT 25 NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, is_completed TINYINT DEFAULT 0 NOT NULL, user_id INT NOT NULL, INDEX IDX_E55128B6A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE xp_log (id INT AUTO_INCREMENT NOT NULL, amount INT NOT NULL, reason VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_4C7160E3A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE study_session ADD CONSTRAINT FK_E55128B6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE xp_log ADD CONSTRAINT FK_4C7160E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD xp INT DEFAULT 0 NOT NULL, ADD level INT DEFAULT 1 NOT NULL, ADD streak_days INT DEFAULT 0 NOT NULL, ADD last_activity_date DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE study_session DROP FOREIGN KEY FK_E55128B6A76ED395');
        $this->addSql('ALTER TABLE xp_log DROP FOREIGN KEY FK_4C7160E3A76ED395');
        $this->addSql('DROP TABLE study_session');
        $this->addSql('DROP TABLE xp_log');
        $this->addSql('ALTER TABLE user DROP xp, DROP level, DROP streak_days, DROP last_activity_date');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260725080347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avatar (id INT AUTO_INCREMENT NOT NULL, seed VARCHAR(255) DEFAULT NULL, skin_color VARCHAR(20) NOT NULL, hair_style VARCHAR(50) NOT NULL, hair_color VARCHAR(20) NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_1677722FA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exam (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, subject VARCHAR(100) NOT NULL, exam_date DATETIME NOT NULL, study_days JSON DEFAULT NULL, study_start_time TIME DEFAULT NULL, study_end_time TIME DEFAULT NULL, questions_per_day INT NOT NULL, user_id INT NOT NULL, INDEX IDX_38BBA6C6A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE exam_material (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, file_type VARCHAR(10) NOT NULL, uploaded_at DATETIME NOT NULL, user_id INT NOT NULL, exam_id INT DEFAULT NULL, INDEX IDX_3FDA3AA6A76ED395 (user_id), INDEX IDX_3FDA3AA6578D5E91 (exam_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, text LONGTEXT NOT NULL, answer LONGTEXT DEFAULT NULL, order_number INT NOT NULL, studied TINYINT DEFAULT 0 NOT NULL, material_id INT NOT NULL, INDEX IDX_B6F7494EE308AC6F (material_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE study_schedule (id INT AUTO_INCREMENT NOT NULL, study_date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, questions_count INT NOT NULL, question_ids JSON DEFAULT NULL, is_completed TINYINT NOT NULL, xp_awarded TINYINT DEFAULT 0 NOT NULL, exam_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_F9B10472578D5E91 (exam_id), INDEX IDX_F9B10472A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE study_session (id INT AUTO_INCREMENT NOT NULL, duration_minutes INT DEFAULT 25 NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, is_completed TINYINT DEFAULT 0 NOT NULL, user_id INT NOT NULL, INDEX IDX_E55128B6A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE study_task (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, scheduled_date DATETIME NOT NULL, is_completed TINYINT NOT NULL, xp_awarded TINYINT DEFAULT 0 NOT NULL, exam_id INT DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_2DA6331C578D5E91 (exam_id), INDEX IDX_2DA6331CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, xp INT DEFAULT 0 NOT NULL, level INT DEFAULT 1 NOT NULL, streak_days INT DEFAULT 0 NOT NULL, last_activity_date DATE DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE xp_log (id INT AUTO_INCREMENT NOT NULL, amount INT DEFAULT NULL, reason VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_4C7160E3A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE avatar ADD CONSTRAINT FK_1677722FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE exam ADD CONSTRAINT FK_38BBA6C6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE exam_material ADD CONSTRAINT FK_3FDA3AA6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE exam_material ADD CONSTRAINT FK_3FDA3AA6578D5E91 FOREIGN KEY (exam_id) REFERENCES exam (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EE308AC6F FOREIGN KEY (material_id) REFERENCES exam_material (id)');
        $this->addSql('ALTER TABLE study_schedule ADD CONSTRAINT FK_F9B10472578D5E91 FOREIGN KEY (exam_id) REFERENCES exam (id)');
        $this->addSql('ALTER TABLE study_schedule ADD CONSTRAINT FK_F9B10472A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE study_session ADD CONSTRAINT FK_E55128B6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE study_task ADD CONSTRAINT FK_2DA6331C578D5E91 FOREIGN KEY (exam_id) REFERENCES exam (id)');
        $this->addSql('ALTER TABLE study_task ADD CONSTRAINT FK_2DA6331CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE xp_log ADD CONSTRAINT FK_4C7160E3A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avatar DROP FOREIGN KEY FK_1677722FA76ED395');
        $this->addSql('ALTER TABLE exam DROP FOREIGN KEY FK_38BBA6C6A76ED395');
        $this->addSql('ALTER TABLE exam_material DROP FOREIGN KEY FK_3FDA3AA6A76ED395');
        $this->addSql('ALTER TABLE exam_material DROP FOREIGN KEY FK_3FDA3AA6578D5E91');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EE308AC6F');
        $this->addSql('ALTER TABLE study_schedule DROP FOREIGN KEY FK_F9B10472578D5E91');
        $this->addSql('ALTER TABLE study_schedule DROP FOREIGN KEY FK_F9B10472A76ED395');
        $this->addSql('ALTER TABLE study_session DROP FOREIGN KEY FK_E55128B6A76ED395');
        $this->addSql('ALTER TABLE study_task DROP FOREIGN KEY FK_2DA6331C578D5E91');
        $this->addSql('ALTER TABLE study_task DROP FOREIGN KEY FK_2DA6331CA76ED395');
        $this->addSql('ALTER TABLE xp_log DROP FOREIGN KEY FK_4C7160E3A76ED395');
        $this->addSql('DROP TABLE avatar');
        $this->addSql('DROP TABLE exam');
        $this->addSql('DROP TABLE exam_material');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE study_schedule');
        $this->addSql('DROP TABLE study_session');
        $this->addSql('DROP TABLE study_task');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE xp_log');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

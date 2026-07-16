<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260716083031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exam_material (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, file_type VARCHAR(10) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, uploaded_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_3FDA3AA6A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE study_schedule (id INT AUTO_INCREMENT NOT NULL, study_date DATE NOT NULL, start_time VARCHAR(5) NOT NULL, end_time VARCHAR(5) NOT NULL, questions_count INT NOT NULL, question_ids JSON DEFAULT NULL, is_completed TINYINT NOT NULL, exam_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_F9B10472578D5E91 (exam_id), INDEX IDX_F9B10472A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE exam_material ADD CONSTRAINT FK_3FDA3AA6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE study_schedule ADD CONSTRAINT FK_F9B10472578D5E91 FOREIGN KEY (exam_id) REFERENCES exam (id)');
        $this->addSql('ALTER TABLE study_schedule ADD CONSTRAINT FK_F9B10472A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE avatar ADD seed VARCHAR(255) DEFAULT NULL, DROP clothing, DROP clothing_color, DROP hat, DROP hat_color, DROP mouth_type, DROP eye_type, DROP eyebrow_type, DROP accessories, CHANGE skin_color skin_color VARCHAR(20) NOT NULL, CHANGE hair_color hair_color VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE exam ADD study_days JSON DEFAULT NULL, ADD study_start_time VARCHAR(5) DEFAULT NULL, ADD study_end_time VARCHAR(5) DEFAULT NULL, ADD questions_per_day INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exam_material DROP FOREIGN KEY FK_3FDA3AA6A76ED395');
        $this->addSql('ALTER TABLE study_schedule DROP FOREIGN KEY FK_F9B10472578D5E91');
        $this->addSql('ALTER TABLE study_schedule DROP FOREIGN KEY FK_F9B10472A76ED395');
        $this->addSql('DROP TABLE exam_material');
        $this->addSql('DROP TABLE study_schedule');
        $this->addSql('ALTER TABLE avatar ADD clothing VARCHAR(50) NOT NULL, ADD clothing_color VARCHAR(50) NOT NULL, ADD hat VARCHAR(50) NOT NULL, ADD hat_color VARCHAR(20) NOT NULL, ADD mouth_type VARCHAR(50) NOT NULL, ADD eye_type VARCHAR(50) NOT NULL, ADD eyebrow_type VARCHAR(50) NOT NULL, ADD accessories VARCHAR(50) NOT NULL, DROP seed, CHANGE skin_color skin_color VARCHAR(50) NOT NULL, CHANGE hair_color hair_color VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE exam DROP study_days, DROP study_start_time, DROP study_end_time, DROP questions_per_day');
    }
}

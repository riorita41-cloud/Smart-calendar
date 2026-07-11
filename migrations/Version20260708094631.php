<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260708094631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE study_task ADD description LONGTEXT DEFAULT NULL, ADD scheduled_date DATETIME NOT NULL, ADD is_completed TINYINT NOT NULL, ADD exam_id INT NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE study_task ADD CONSTRAINT FK_2DA6331C578D5E91 FOREIGN KEY (exam_id) REFERENCES exam (id)');
        $this->addSql('ALTER TABLE study_task ADD CONSTRAINT FK_2DA6331CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2DA6331C578D5E91 ON study_task (exam_id)');
        $this->addSql('CREATE INDEX IDX_2DA6331CA76ED395 ON study_task (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE study_task DROP FOREIGN KEY FK_2DA6331C578D5E91');
        $this->addSql('ALTER TABLE study_task DROP FOREIGN KEY FK_2DA6331CA76ED395');
        $this->addSql('DROP INDEX IDX_2DA6331C578D5E91 ON study_task');
        $this->addSql('DROP INDEX IDX_2DA6331CA76ED395 ON study_task');
        $this->addSql('ALTER TABLE study_task DROP description, DROP scheduled_date, DROP is_completed, DROP exam_id, DROP user_id');
    }
}

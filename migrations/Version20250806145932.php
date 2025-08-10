<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250806145932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attendance_session (id INT AUTO_INCREMENT NOT NULL, class_section_id INT NOT NULL, date DATE NOT NULL, is_active TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_D7833BD66E2E11D8 (class_section_id), UNIQUE INDEX unique_section_date (class_section_id, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attendance_session ADD CONSTRAINT FK_D7833BD66E2E11D8 FOREIGN KEY (class_section_id) REFERENCES class_section (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attendance_session DROP FOREIGN KEY FK_D7833BD66E2E11D8');
        $this->addSql('DROP TABLE attendance_session');
    }
}

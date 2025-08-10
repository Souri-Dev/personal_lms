<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250806150118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attendance ADD attendance_session_id INT NOT NULL');
        $this->addSql('ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D91A746B1C7 FOREIGN KEY (attendance_session_id) REFERENCES attendance_session (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6DE30D91A746B1C7 ON attendance (attendance_session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D91A746B1C7');
        $this->addSql('DROP INDEX IDX_6DE30D91A746B1C7 ON attendance');
        $this->addSql('ALTER TABLE attendance DROP attendance_session_id');
    }
}

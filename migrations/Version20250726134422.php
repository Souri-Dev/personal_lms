<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250726134422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE attendance (id INT AUTO_INCREMENT NOT NULL, student_id INT NOT NULL, class_section_id INT NOT NULL, date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', status VARCHAR(50) NOT NULL, INDEX IDX_6DE30D91CB944F1A (student_id), INDEX IDX_6DE30D916E2E11D8 (class_section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D91CB944F1A FOREIGN KEY (student_id) REFERENCES student (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance ADD CONSTRAINT FK_6DE30D916E2E11D8 FOREIGN KEY (class_section_id) REFERENCES class_section (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D91CB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE attendance DROP FOREIGN KEY FK_6DE30D916E2E11D8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE attendance
        SQL);
    }
}

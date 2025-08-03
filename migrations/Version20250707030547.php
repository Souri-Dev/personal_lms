<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707030547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE class_section (id INT AUTO_INCREMENT NOT NULL, class_id INT DEFAULT NULL, section_name VARCHAR(150) NOT NULL, INDEX IDX_E8061D13EA000B10 (class_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE school_class (id INT AUTO_INCREMENT NOT NULL, subject_name VARCHAR(100) NOT NULL, subject_code VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE student (id INT AUTO_INCREMENT NOT NULL, student_number VARCHAR(20) NOT NULL, name VARCHAR(100) NOT NULL, course VARCHAR(100) NOT NULL, section VARCHAR(50) NOT NULL, qr VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B723AF33C9F64A58 (qr), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE students_class_sections (student_id INT NOT NULL, class_section_id INT NOT NULL, INDEX IDX_AF98F5D4CB944F1A (student_id), INDEX IDX_AF98F5D46E2E11D8 (class_section_id), PRIMARY KEY(student_id, class_section_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE class_section ADD CONSTRAINT FK_E8061D13EA000B10 FOREIGN KEY (class_id) REFERENCES school_class (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE students_class_sections ADD CONSTRAINT FK_AF98F5D4CB944F1A FOREIGN KEY (student_id) REFERENCES student (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE students_class_sections ADD CONSTRAINT FK_AF98F5D46E2E11D8 FOREIGN KEY (class_section_id) REFERENCES class_section (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE class_section DROP FOREIGN KEY FK_E8061D13EA000B10
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE students_class_sections DROP FOREIGN KEY FK_AF98F5D4CB944F1A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE students_class_sections DROP FOREIGN KEY FK_AF98F5D46E2E11D8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE class_section
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE school_class
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE student
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE students_class_sections
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}

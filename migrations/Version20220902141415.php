<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220902141415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parse_queue_category DROP FOREIGN KEY FK_89638E5FF7C2794');
        $this->addSql('DROP TABLE parse_queue');
        $this->addSql('DROP TABLE parse_queue_category');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parse_queue (id INT AUTO_INCREMENT NOT NULL, lang_id INT DEFAULT NULL, manufacturer_id INT DEFAULT NULL, origin_filename VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, filename VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, exception_text LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', byte_size INT NOT NULL, INDEX IDX_CA83A06FB213FA4 (lang_id), INDEX IDX_CA83A06FA23B42D (manufacturer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE parse_queue_category (parse_queue_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_89638E5FF7C2794 (parse_queue_id), INDEX IDX_89638E512469DE2 (category_id), PRIMARY KEY(parse_queue_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE parse_queue ADD CONSTRAINT FK_CA83A06FA23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id)');
        $this->addSql('ALTER TABLE parse_queue ADD CONSTRAINT FK_CA83A06FB213FA4 FOREIGN KEY (lang_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE parse_queue_category ADD CONSTRAINT FK_89638E512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parse_queue_category ADD CONSTRAINT FK_89638E5FF7C2794 FOREIGN KEY (parse_queue_id) REFERENCES parse_queue (id) ON DELETE CASCADE');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220908073826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file ADD mime_type VARCHAR(255) NOT NULL, CHANGE manufacturer_id manufacturer_id INT NOT NULL, CHANGE lang_id lang_id INT NOT NULL, CHANGE fileType_id fileType_id INT NOT NULL');
        $this->addSql('DROP INDEX UNIQ_5223F479E208D56 ON file_type');
        $this->addSql('ALTER TABLE file_type CHANGE alis alias VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5223F47E16C6B94 ON file_type (alias)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP mime_type, CHANGE manufacturer_id manufacturer_id INT DEFAULT NULL, CHANGE lang_id lang_id INT DEFAULT NULL, CHANGE fileType_id fileType_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX UNIQ_5223F47E16C6B94 ON file_type');
        $this->addSql('ALTER TABLE file_type CHANGE alias alis VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5223F479E208D56 ON file_type (alis)');
    }
}

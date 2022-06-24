<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220623145437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE catalog_category (catalog_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_349BC7DFCC3C66FC (catalog_id), INDEX IDX_349BC7DF12469DE2 (category_id), PRIMARY KEY(catalog_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, parent INT DEFAULT NULL, title VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, INDEX IDX_64C19C13D8E604F (parent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE catalog_category ADD CONSTRAINT FK_349BC7DFCC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id)');
        $this->addSql('ALTER TABLE catalog_category ADD CONSTRAINT FK_349BC7DF12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C13D8E604F FOREIGN KEY (parent) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_category DROP FOREIGN KEY FK_349BC7DF12469DE2');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C13D8E604F');
        $this->addSql('DROP TABLE catalog_category');
        $this->addSql('DROP TABLE category');
    }
}

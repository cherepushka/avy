<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220629132808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE catalog (id INT AUTO_INCREMENT NOT NULL, manufacturer_id INT DEFAULT NULL, lang_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, origin_filename VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1B2C32473C0BE965 (filename), INDEX IDX_1B2C3247A23B42D (manufacturer_id), INDEX IDX_1B2C3247B213FA4 (lang_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE catalog_category (catalog_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_349BC7DFCC3C66FC (catalog_id), INDEX IDX_349BC7DF12469DE2 (category_id), PRIMARY KEY(catalog_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, parent INT DEFAULT NULL, title VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, INDEX IDX_64C19C13D8E604F (parent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, alias VARCHAR(3) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE manufacturer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3D0AE6DC5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parse_queue (id INT AUTO_INCREMENT NOT NULL, lang_id INT DEFAULT NULL, manufacturer_id INT DEFAULT NULL, origin_filename VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, text LONGTEXT DEFAULT NULL, status ENUM(\'new\', \'parsing\', \'success\', \'failed\'), INDEX IDX_CA83A06FB213FA4 (lang_id), INDEX IDX_CA83A06FA23B42D (manufacturer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE parse_queue_category (parse_queue_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_89638E5FF7C2794 (parse_queue_id), INDEX IDX_89638E512469DE2 (category_id), PRIMARY KEY(parse_queue_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C3247A23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id)');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C3247B213FA4 FOREIGN KEY (lang_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE catalog_category ADD CONSTRAINT FK_349BC7DFCC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_category ADD CONSTRAINT FK_349BC7DF12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C13D8E604F FOREIGN KEY (parent) REFERENCES category (id)');
        $this->addSql('ALTER TABLE parse_queue ADD CONSTRAINT FK_CA83A06FB213FA4 FOREIGN KEY (lang_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE parse_queue ADD CONSTRAINT FK_CA83A06FA23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id)');
        $this->addSql('ALTER TABLE parse_queue_category ADD CONSTRAINT FK_89638E5FF7C2794 FOREIGN KEY (parse_queue_id) REFERENCES parse_queue (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE parse_queue_category ADD CONSTRAINT FK_89638E512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE catalog_category DROP FOREIGN KEY FK_349BC7DFCC3C66FC');
        $this->addSql('ALTER TABLE catalog_category DROP FOREIGN KEY FK_349BC7DF12469DE2');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C13D8E604F');
        $this->addSql('ALTER TABLE parse_queue_category DROP FOREIGN KEY FK_89638E512469DE2');
        $this->addSql('ALTER TABLE catalog DROP FOREIGN KEY FK_1B2C3247B213FA4');
        $this->addSql('ALTER TABLE parse_queue DROP FOREIGN KEY FK_CA83A06FB213FA4');
        $this->addSql('ALTER TABLE catalog DROP FOREIGN KEY FK_1B2C3247A23B42D');
        $this->addSql('ALTER TABLE parse_queue DROP FOREIGN KEY FK_CA83A06FA23B42D');
        $this->addSql('ALTER TABLE parse_queue_category DROP FOREIGN KEY FK_89638E5FF7C2794');
        $this->addSql('DROP TABLE catalog');
        $this->addSql('DROP TABLE catalog_category');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE manufacturer');
        $this->addSql('DROP TABLE parse_queue');
        $this->addSql('DROP TABLE parse_queue_category');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

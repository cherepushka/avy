<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220907143141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, manufacturer_id INT DEFAULT NULL, lang_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, origin_filename VARCHAR(255) NOT NULL, byte_size INT NOT NULL, text LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', fileType_id INT DEFAULT NULL, fileStatus_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8C9F36103C0BE965 (filename), INDEX IDX_8C9F3610A23B42D (manufacturer_id), INDEX IDX_8C9F3610B213FA4 (lang_id), INDEX IDX_8C9F36104BD57433 (fileType_id), INDEX IDX_8C9F3610769648E3 (fileStatus_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_category (file_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_B71C965C93CB796C (file_id), INDEX IDX_B71C965C12469DE2 (category_id), PRIMARY KEY(file_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_status (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C31743A37B00651C (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file_type (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5223F478CDE5729 (type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610B213FA4 FOREIGN KEY (lang_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36104BD57433 FOREIGN KEY (fileType_id) REFERENCES file_type (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610769648E3 FOREIGN KEY (fileStatus_id) REFERENCES file_status (id)');
        $this->addSql('ALTER TABLE file_category ADD CONSTRAINT FK_B71C965C93CB796C FOREIGN KEY (file_id) REFERENCES file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE file_category ADD CONSTRAINT FK_B71C965C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_category DROP FOREIGN KEY FK_349BC7DFCC3C66FC');
        $this->addSql('ALTER TABLE catalog_category DROP FOREIGN KEY FK_349BC7DF12469DE2');
        $this->addSql('ALTER TABLE catalog DROP FOREIGN KEY FK_1B2C3247A23B42D');
        $this->addSql('ALTER TABLE catalog DROP FOREIGN KEY FK_1B2C3247B213FA4');
        $this->addSql('DROP TABLE catalog_category');
        $this->addSql('DROP TABLE catalog');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE catalog_category (catalog_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_349BC7DFCC3C66FC (catalog_id), INDEX IDX_349BC7DF12469DE2 (category_id), PRIMARY KEY(catalog_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE catalog (id INT AUTO_INCREMENT NOT NULL, manufacturer_id INT DEFAULT NULL, lang_id INT DEFAULT NULL, filename VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, origin_filename VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, byte_size INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', text LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_1B2C3247B213FA4 (lang_id), UNIQUE INDEX UNIQ_1B2C32473C0BE965 (filename), INDEX IDX_1B2C3247A23B42D (manufacturer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE catalog_category ADD CONSTRAINT FK_349BC7DFCC3C66FC FOREIGN KEY (catalog_id) REFERENCES catalog (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog_category ADD CONSTRAINT FK_349BC7DF12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C3247A23B42D FOREIGN KEY (manufacturer_id) REFERENCES manufacturer (id)');
        $this->addSql('ALTER TABLE catalog ADD CONSTRAINT FK_1B2C3247B213FA4 FOREIGN KEY (lang_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610A23B42D');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610B213FA4');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36104BD57433');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610769648E3');
        $this->addSql('ALTER TABLE file_category DROP FOREIGN KEY FK_B71C965C93CB796C');
        $this->addSql('ALTER TABLE file_category DROP FOREIGN KEY FK_B71C965C12469DE2');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE file_category');
        $this->addSql('DROP TABLE file_status');
        $this->addSql('DROP TABLE file_type');
    }
}

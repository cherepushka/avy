<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220711073743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD productsExist TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE parse_queue CHANGE status status ENUM(\'new\', \'parsing\', \'success\', \'failed\', \'duplicated\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP productsExist');
        $this->addSql('ALTER TABLE parse_queue CHANGE status status VARCHAR(255) DEFAULT NULL');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220908064005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F3610769648E3');
        $this->addSql('DROP TABLE file_status');
        $this->addSql('DROP INDEX IDX_8C9F3610769648E3 ON file');
        $this->addSql('ALTER TABLE file ADD file_status VARCHAR(255) DEFAULT \'new\' NOT NULL, DROP fileStatus_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE file_status (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_C31743A37B00651C (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE file ADD fileStatus_id INT DEFAULT NULL, DROP file_status');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610769648E3 FOREIGN KEY (fileStatus_id) REFERENCES file_status (id)');
        $this->addSql('CREATE INDEX IDX_8C9F3610769648E3 ON file (fileStatus_id)');
    }
}

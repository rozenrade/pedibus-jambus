<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507131311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_14B78418D17F50A6 ON photo');
        $this->addSql('ALTER TABLE photo ADD photo_alt VARCHAR(255) DEFAULT NULL, DROP uuid');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photo ADD uuid VARCHAR(36) NOT NULL, DROP photo_alt');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_14B78418D17F50A6 ON photo (uuid)');
    }
}

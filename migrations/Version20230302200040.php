<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230302200040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change account_enabled by is_verified which is given by symfony register maker';
    }

    public function up(Schema $schema): void
    {
        // ALTER TABLE user ADD is_verified TINYINT(1) NOT NULL
        $this->addSql('ALTER TABLE user CHANGE account_enabled is_verified TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // ALTER TABLE `user` DROP is_verified
        $this->addSql('ALTER TABLE `user` CHANGE is_verified account_enabled TINYINT(1) NOT NULL');
    }
}

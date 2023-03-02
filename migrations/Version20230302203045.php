<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20230302203045 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set token field nullable true';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user CHANGE token token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` CHANGE token token VARCHAR(255) NOT NULL');
    }
}

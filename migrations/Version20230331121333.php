<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20230331121333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at and updated_at in Trick';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trick ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trick DROP created_at, DROP updated_at');
    }
}

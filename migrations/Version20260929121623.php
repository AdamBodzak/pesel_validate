<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260929121623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create person table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE person (id UUID NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, pesel CHAR(11) NOT NULL, birth_date DATE NOT NULL, gender VARCHAR(6) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_person_created_at ON person (created_at)');
        $this->addSql('CREATE UNIQUE INDEX uniq_person_pesel ON person (pesel)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE person');
    }
}

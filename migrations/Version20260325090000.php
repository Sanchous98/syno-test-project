<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create person table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE person (
                id SERIAL PRIMARY KEY,
                user_id CHAR(15) NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                sex VARCHAR(10) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                date_of_birth DATE NOT NULL,
                job_title VARCHAR(255) NOT NULL
            )
            SQL);

        $this->addSql('CREATE INDEX idx_person_user_id ON person (user_id)');
        $this->addSql('CREATE INDEX idx_person_email ON person (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE person');
    }
}

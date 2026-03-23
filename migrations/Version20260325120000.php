<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Replace user_id index with unique constraint';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_person_user_id ON person');
        $this->addSql('CREATE UNIQUE INDEX uniq_person_user_id ON person (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_person_user_id ON person');
        $this->addSql('CREATE INDEX idx_person_user_id ON person (user_id)');
    }
}

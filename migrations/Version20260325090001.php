<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325090001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create materialized views for statistics';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE MATERIALIZED VIEW mv_age_statistics AS
            SELECT
                1 AS id,
                EXTRACT(YEAR FROM AGE(CURRENT_DATE, MIN(date_of_birth)))::int AS oldest_age,
                EXTRACT(YEAR FROM AGE(CURRENT_DATE, MAX(date_of_birth)))::int AS youngest_age,
                ROUND(AVG(EXTRACT(YEAR FROM AGE(CURRENT_DATE, date_of_birth)))::numeric, 2) AS average_age,
                COUNT(*) FILTER (WHERE date_of_birth < '2000-01-01') AS born_before_2000,
                COUNT(*) FILTER (WHERE date_of_birth >= '2000-01-01') AS born_from_2000
            FROM person
            SQL);

        $this->addSql('CREATE UNIQUE INDEX ON mv_age_statistics (id)');

        $this->addSql(<<<'SQL'
            CREATE MATERIALIZED VIEW mv_job_statistics AS
            SELECT
                job_title,
                COUNT(*)::int AS total_users,
                json_agg(user_id ORDER BY user_id) AS user_ids
            FROM person
            GROUP BY job_title
            SQL);

        $this->addSql('CREATE UNIQUE INDEX ON mv_job_statistics (job_title)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP MATERIALIZED VIEW IF EXISTS mv_job_statistics');
        $this->addSql('DROP MATERIALIZED VIEW IF EXISTS mv_age_statistics');
    }
}

<?php

namespace App\MessageHandler;

use App\Message\ImportCompleted;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RefreshAgeStatisticsHandler
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function __invoke(ImportCompleted $message): void
    {
        $this->connection->executeStatement('REFRESH MATERIALIZED VIEW CONCURRENTLY mv_age_statistics');
    }
}

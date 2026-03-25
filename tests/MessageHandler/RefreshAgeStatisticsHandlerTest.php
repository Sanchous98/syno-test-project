<?php

namespace App\Tests\MessageHandler;

use App\Message\ImportCompleted;
use App\MessageHandler\RefreshAgeStatisticsHandler;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RefreshAgeStatisticsHandlerTest extends TestCase
{
    private Connection&MockObject $connection;
    private RefreshAgeStatisticsHandler $handler;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->handler = new RefreshAgeStatisticsHandler($this->connection);
    }

    public function testRefreshesMaterializedView(): void
    {
        $this->connection->expects($this->once())
            ->method('executeStatement')
            ->with('REFRESH MATERIALIZED VIEW CONCURRENTLY mv_age_statistics');

        ($this->handler)(new ImportCompleted());
    }
}

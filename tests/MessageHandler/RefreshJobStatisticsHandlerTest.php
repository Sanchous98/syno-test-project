<?php

namespace App\Tests\MessageHandler;

use App\Message\ImportCompleted;
use App\MessageHandler\RefreshJobStatisticsHandler;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class RefreshJobStatisticsHandlerTest extends TestCase
{
    private Connection&MockObject $connection;
    private RefreshJobStatisticsHandler $handler;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->handler = new RefreshJobStatisticsHandler($this->connection);
    }

    public function testRefreshesMaterializedView(): void
    {
        $this->connection->expects($this->once())
            ->method('executeStatement')
            ->with('REFRESH MATERIALIZED VIEW CONCURRENTLY mv_job_statistics');

        ($this->handler)(new ImportCompleted());
    }
}

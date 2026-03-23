<?php

namespace App\Tests\MessageHandler;

use App\Entity\AgeStatistics\AgeStatistics;
use App\Message\ImportCompleted;
use App\MessageHandler\RefreshAgeStatisticsHandler;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class RefreshAgeStatisticsHandlerTest extends TestCase
{
    private PersonRepository&Stub $personRepository;
    private ClockInterface&Stub $clock;
    private EntityManagerInterface&MockObject $entityManager;
    private RefreshAgeStatisticsHandler $handler;

    protected function setUp(): void
    {
        $this->personRepository = $this->createStub(PersonRepository::class);
        $this->clock = $this->createStub(ClockInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $doctrine = $this->createStub(ManagerRegistry::class);
        $doctrine->method('getManager')->willReturn($this->entityManager);

        $this->handler = new RefreshAgeStatisticsHandler(
            $this->personRepository,
            $this->clock,
            $doctrine,
        );
    }

    public function testCalculatesAndPersistsAgeStatistics(): void
    {
        $now = new \DateTimeImmutable('2025-01-01');

        $this->clock->method('now')->willReturn($now);

        $this->personRepository->method('getAgeAggregates')->willReturn([
            'youngestBirth' => '1960-06-15',
            'oldestBirth' => '2005-03-20',
            'averageAge' => 35.8,
            'bornBefore2000' => 3,
            'bornFrom2000' => 2,
        ]);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (AgeStatistics $stats) {
                $this->assertSame(64, $stats->oldestAge);    // 1960-06-15 → 2025-01-01 = 64
                $this->assertSame(19, $stats->youngestAge);   // 2005-03-20 → 2025-01-01 = 19
                $this->assertSame(35.8, $stats->averageAge);
                $this->assertSame(3, $stats->bornBefore2000);
                $this->assertSame(2, $stats->bornFrom2000);

                return true;
            }));

        $this->entityManager->expects($this->once())->method('flush');

        ($this->handler)(new ImportCompleted());
    }

    public function testSinglePersonStatistics(): void
    {
        $now = new \DateTimeImmutable('2025-06-01');

        $this->clock->method('now')->willReturn($now);

        $this->personRepository->method('getAgeAggregates')->willReturn([
            'youngestBirth' => '2000-01-01',
            'oldestBirth' => '2000-01-01',
            'averageAge' => 25.0,
            'bornBefore2000' => 0,
            'bornFrom2000' => 1,
        ]);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (AgeStatistics $stats) {
                $this->assertSame(25, $stats->oldestAge);
                $this->assertSame(25, $stats->youngestAge);
                $this->assertSame(25.0, $stats->averageAge);
                $this->assertSame(0, $stats->bornBefore2000);
                $this->assertSame(1, $stats->bornFrom2000);

                return true;
            }));

        $this->entityManager->expects($this->once())->method('flush');

        ($this->handler)(new ImportCompleted());
    }

    public function testCreatedAtUsesClockTime(): void
    {
        $now = new \DateTimeImmutable('2025-03-15 14:30:00');

        $this->clock->method('now')->willReturn($now);

        $this->personRepository->method('getAgeAggregates')->willReturn([
            'youngestBirth' => '1990-01-01',
            'oldestBirth' => '1990-01-01',
            'averageAge' => 35.0,
            'bornBefore2000' => 1,
            'bornFrom2000' => 0,
        ]);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (AgeStatistics $stats) use ($now) {
                $this->assertSame($now, $stats->createdAt);

                return true;
            }));

        ($this->handler)(new ImportCompleted());
    }
}

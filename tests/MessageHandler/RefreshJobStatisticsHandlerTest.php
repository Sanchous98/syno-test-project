<?php

namespace App\Tests\MessageHandler;

use App\Entity\JobStatistics\JobStatistics;
use App\Message\ImportCompleted;
use App\MessageHandler\RefreshJobStatisticsHandler;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

final class RefreshJobStatisticsHandlerTest extends TestCase
{
    private PersonRepository&Stub $personRepository;
    private ClockInterface&Stub $clock;
    private EntityManagerInterface&MockObject $entityManager;
    private RefreshJobStatisticsHandler $handler;

    protected function setUp(): void
    {
        $this->personRepository = $this->createStub(PersonRepository::class);
        $this->clock = $this->createStub(ClockInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $doctrine = $this->createStub(ManagerRegistry::class);
        $doctrine->method('getManager')->willReturn($this->entityManager);

        $this->handler = new RefreshJobStatisticsHandler(
            $this->personRepository,
            $this->clock,
            $doctrine,
        );
    }

    public function testPersistsJobStatisticsForEachTitle(): void
    {
        $now = new \DateTimeImmutable('2025-01-01');
        $this->clock->method('now')->willReturn($now);

        $this->personRepository->method('getJobStatistics')->willReturn([
            'Developer' => [
                'totalUsers' => 2,
                'userIds' => ['USR001', 'USR002'],
            ],
            'Designer' => [
                'totalUsers' => 1,
                'userIds' => ['USR003'],
            ],
        ]);

        $persisted = [];
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(function (JobStatistics $stats) use (&$persisted) {
                $persisted[] = $stats;
            });

        $this->entityManager->expects($this->once())->method('flush');

        ($this->handler)(new ImportCompleted());

        $this->assertSame('Developer', $persisted[0]->jobTitle);
        $this->assertSame(2, $persisted[0]->totalUsers);
        $this->assertSame(['USR001', 'USR002'], $persisted[0]->userIds);
        $this->assertSame($now, $persisted[0]->createdAt);

        $this->assertSame('Designer', $persisted[1]->jobTitle);
        $this->assertSame(1, $persisted[1]->totalUsers);
        $this->assertSame(['USR003'], $persisted[1]->userIds);
        $this->assertSame($now, $persisted[1]->createdAt);
    }

    public function testEmptyJobStatisticsDoesNotPersist(): void
    {
        $this->clock->method('now')->willReturn(new \DateTimeImmutable());

        $this->personRepository->method('getJobStatistics')->willReturn([]);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        ($this->handler)(new ImportCompleted());
    }

    public function testSingleJobWithManyUsers(): void
    {
        $now = new \DateTimeImmutable('2025-06-15');
        $this->clock->method('now')->willReturn($now);

        $userIds = ['U001', 'U002', 'U003', 'U004', 'U005'];

        $this->personRepository->method('getJobStatistics')->willReturn([
            'Manager' => [
                'totalUsers' => 5,
                'userIds' => $userIds,
            ],
        ]);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (JobStatistics $stats) use ($userIds, $now) {
                $this->assertSame('Manager', $stats->jobTitle);
                $this->assertSame(5, $stats->totalUsers);
                $this->assertSame($userIds, $stats->userIds);
                $this->assertSame($now, $stats->createdAt);

                return true;
            }));

        $this->entityManager->expects($this->once())->method('flush');

        ($this->handler)(new ImportCompleted());
    }
}

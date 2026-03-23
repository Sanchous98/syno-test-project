<?php

namespace App\MessageHandler;

use App\Entity\AgeStatistics\AgeStatistics;
use App\Message\ImportCompleted;
use App\Repository\PersonRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RefreshAgeStatisticsHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly ClockInterface $clock,
        ManagerRegistry $doctrine,
    ) {
        $this->entityManager = $doctrine->getManager('age_statistics');
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function __invoke(ImportCompleted $message): void
    {
        $stats = $this->personRepository->getAgeAggregates();

        $now = $this->clock->now();
        $oldestAge = $now->diff(new DateTimeImmutable($stats['youngestBirth']))->y;
        $youngestAge = $now->diff(new DateTimeImmutable($stats['oldestBirth']))->y;

        $this->entityManager->getConnection()->executeStatement('TRUNCATE TABLE age_statistics');

        $this->entityManager->persist(new AgeStatistics(
            oldestAge: $oldestAge,
            youngestAge: $youngestAge,
            averageAge: $stats['averageAge'],
            bornBefore2000: $stats['bornBefore2000'],
            bornFrom2000: $stats['bornFrom2000'],
            createdAt: $now,
        ));

        $this->entityManager->flush();
    }
}

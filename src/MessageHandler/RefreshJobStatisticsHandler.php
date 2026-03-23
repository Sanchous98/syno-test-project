<?php

namespace App\MessageHandler;

use App\Entity\JobStatistics\JobStatistics;
use App\Message\ImportCompleted;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Clock\ClockInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RefreshJobStatisticsHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly ClockInterface $clock,
        ManagerRegistry $doctrine,
    ) {
        $this->entityManager = $doctrine->getManager('job_statistics');
    }

    public function __invoke(ImportCompleted $message): void
    {
        $jobStats = $this->personRepository->getJobStatistics();
        $now = $this->clock->now();

        foreach ($jobStats as $jobTitle => $data) {
            $this->entityManager->persist(new JobStatistics(
                jobTitle: $jobTitle,
                totalUsers: $data['totalUsers'],
                userIds: $data['userIds'],
                createdAt: $now,
            ));
        }

        $this->entityManager->flush();
    }
}

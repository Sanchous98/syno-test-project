<?php

namespace App\Repository\JobStatistics;

use App\Entity\JobStatistics\JobStatistics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobStatistics>
 */
class JobStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobStatistics::class);
    }

    /**
     * @return JobStatistics[]
     */
    public function findLatest(): array
    {
        $latestDate = $this->createQueryBuilder('js')
            ->select('MAX(js.createdAt)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($latestDate === null) {
            return [];
        }

        return $this->createQueryBuilder('js')
            ->where('js.createdAt = :createdAt')
            ->setParameter('createdAt', $latestDate)
            ->getQuery()
            ->getResult();
    }
}

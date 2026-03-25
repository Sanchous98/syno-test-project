<?php

namespace App\Repository\AgeStatistics;

use App\Entity\AgeStatistics\AgeStatistics;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AgeStatistics>
 */
class AgeStatisticsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgeStatistics::class);
    }
}

<?php

namespace App\Entity\AgeStatistics;

use App\Repository\AgeStatistics\AgeStatisticsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgeStatisticsRepository::class, readOnly: true)]
#[ORM\Table(name: 'mv_age_statistics')]
class AgeStatistics
{
    #[ORM\Id]
    #[ORM\Column]
    private(set) int $id;

    #[ORM\Column]
    private(set) int $oldestAge;

    #[ORM\Column]
    private(set) int $youngestAge;

    #[ORM\Column]
    private(set) float $averageAge;

    #[ORM\Column]
    private(set) int $bornBefore2000;

    #[ORM\Column]
    private(set) int $bornFrom2000;
}

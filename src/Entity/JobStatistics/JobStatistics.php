<?php

namespace App\Entity\JobStatistics;

use App\Repository\JobStatistics\JobStatisticsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobStatisticsRepository::class, readOnly: true)]
#[ORM\Table(name: 'mv_job_statistics')]
class JobStatistics
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private(set) string $jobTitle;

    #[ORM\Column]
    private(set) int $totalUsers;

    #[ORM\Column(type: Types::JSON)]
    private(set) array $userIds;
}

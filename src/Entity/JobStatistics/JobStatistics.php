<?php

namespace App\Entity\JobStatistics;

use App\Repository\JobStatistics\JobStatisticsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobStatisticsRepository::class, readOnly: true)]
class JobStatistics
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private(set) int $id;

    #[ORM\Column(length: 255)]
    private(set) string $jobTitle;

    #[ORM\Column]
    private(set) int $totalUsers;

    #[ORM\Column(type: Types::JSON)]
    private(set) array $userIds;

    #[ORM\Column]
    private(set) \DateTimeImmutable $createdAt;

    public function __construct(
        string $jobTitle,
        int $totalUsers,
        array $userIds,
        \DateTimeImmutable $createdAt,
    ) {
        $this->jobTitle = $jobTitle;
        $this->totalUsers = $totalUsers;
        $this->userIds = $userIds;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobTitle(): string
    {
        return $this->jobTitle;
    }

    public function getTotalUsers(): int
    {
        return $this->totalUsers;
    }

    public function getUserIds(): array
    {
        return $this->userIds;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

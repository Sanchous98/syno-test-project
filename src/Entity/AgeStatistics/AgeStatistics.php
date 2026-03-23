<?php

namespace App\Entity\AgeStatistics;

use App\Repository\AgeStatistics\AgeStatisticsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgeStatisticsRepository::class, readOnly: true)]
class AgeStatistics
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
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

    #[ORM\Column]
    private(set) \DateTimeImmutable $createdAt;

    public function __construct(
        int $oldestAge,
        int $youngestAge,
        float $averageAge,
        int $bornBefore2000,
        int $bornFrom2000,
        \DateTimeImmutable $createdAt,
    ) {
        $this->oldestAge = $oldestAge;
        $this->youngestAge = $youngestAge;
        $this->averageAge = $averageAge;
        $this->bornBefore2000 = $bornBefore2000;
        $this->bornFrom2000 = $bornFrom2000;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOldestAge(): int
    {
        return $this->oldestAge;
    }

    public function getYoungestAge(): int
    {
        return $this->youngestAge;
    }

    public function getAverageAge(): float
    {
        return $this->averageAge;
    }

    public function getBornBefore2000(): int
    {
        return $this->bornBefore2000;
    }

    public function getBornFrom2000(): int
    {
        return $this->bornFrom2000;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

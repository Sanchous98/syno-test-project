<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * @param list<string> $userIds
     * @return array<string, Person>
     */
    public function findByUserIds(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $persons = $this->createQueryBuilder('p')
            ->where('p.userId IN (:userIds)')
            ->setParameter('userIds', $userIds)
            ->getQuery()
            ->getResult();

        $indexed = [];
        foreach ($persons as $person) {
            $indexed[$person->getUserId()] = $person;
        }

        return $indexed;
    }

    /**
     * @return array<string, array{totalUsers: int, userIds: list<string>}>
     */
    public function getJobStatistics(): array
    {
        $rows = $this->getEntityManager()
            ->createQuery(<<<'DQL'
            SELECT p.jobTitle, p.userId FROM App\Entity\Person p
            DQL)
            ->toIterable();

        $result = [];

        foreach ($rows as $row) {
            $result[$row['jobTitle']]['userIds'][] = $row['userId'];
        }

        foreach ($result as $jobTitle => $data) {
            $result[$jobTitle]['totalUsers'] = count($data['userIds']);
        }

        return $result;
    }

    /**
     * @return array{youngestBirth: string, oldestBirth: string, averageAge: float, bornBefore2000: int, bornFrom2000: int}
     */
    public function getAgeAggregates(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<'SQL'
            SELECT
                MIN(date_of_birth) as youngest_birth,
                MAX(date_of_birth) as oldest_birth,
                ROUND(AVG(TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE())), 2) as average_age,
                SUM(CASE WHEN date_of_birth < '2000-01-01' THEN 1 ELSE 0 END) as born_before_2000,
                SUM(CASE WHEN date_of_birth >= '2000-01-01' THEN 1 ELSE 0 END) as born_from_2000
            FROM person
            SQL;

        $row = $conn->executeQuery($sql)->fetchAssociative();

        return [
            'youngestBirth' => $row['youngest_birth'],
            'oldestBirth' => $row['oldest_birth'],
            'averageAge' => (float) $row['average_age'],
            'bornBefore2000' => (int) $row['born_before_2000'],
            'bornFrom2000' => (int) $row['born_from_2000'],
        ];
    }
}

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
}

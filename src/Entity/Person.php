<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_person_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_person_email', columns: ['email'])]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 15, options: ['fixed' => true])]
        private string $userId,

        #[ORM\Column(length: 100)]
        private string $firstName,

        #[ORM\Column(length: 100)]
        private string $lastName,

        #[ORM\Column(type: 'enum', enumType: Sex::class)]
        private Sex $sex,

        #[ORM\Column(length: 255)]
        private string $email,

        #[ORM\Column(length: 50)]
        private string $phone,

        #[ORM\Column(type: Types::DATE_IMMUTABLE)]
        private \DateTimeImmutable $dateOfBirth,

        #[ORM\Column(length: 255)]
        private string $jobTitle,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getSex(): Sex
    {
        return $this->sex;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getDateOfBirth(): \DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function getJobTitle(): string
    {
        return $this->jobTitle;
    }

    public function update(
        string $firstName,
        string $lastName,
        Sex $sex,
        string $email,
        string $phone,
        \DateTimeImmutable $dateOfBirth,
        string $jobTitle,
    ): void {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->sex = $sex;
        $this->email = $email;
        $this->phone = $phone;
        $this->dateOfBirth = $dateOfBirth;
        $this->jobTitle = $jobTitle;
    }
}

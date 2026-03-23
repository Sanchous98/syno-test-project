<?php

namespace App\Command;

use App\Entity\Person;
use App\Entity\Sex;
use App\Message\ImportCompleted;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:import-people',
    description: 'Import people from a CSV file',
)]
class ImportPeopleCommand extends Command
{
    private const DEFAULT_BATCH_SIZE = 500;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PersonRepository $personRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the CSV file')
            ->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, 'Number of rows to flush at once', self::DEFAULT_BATCH_SIZE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');
        $batchSize = (int) $input->getOption('batch-size');

        $csv = new SplFileObject($filePath, 'r');
        $csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

        $csv->seek(PHP_INT_MAX);
        $totalRows = $csv->key();
        $csv->rewind();

        $columnNames = $csv->current();

        $progressBar = new ProgressBar($output, $totalRows);
        $progressBar->start();

        $batch = [];

        foreach ($csv as $index => $row) {
            if ($index === 0 || $row === false) {
                continue;
            }

            $batch[] = array_combine($columnNames, $row);

            if (count($batch) >= $batchSize) {
                $this->processBatch($batch);
                $progressBar->advance(count($batch));
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->processBatch($batch);
            $progressBar->advance(count($batch));
        }

        $progressBar->finish();
        $io->newLine(2);
        $io->success('Import complete. Dispatching statistics refresh...');

        $this->messageBus->dispatch(new ImportCompleted());

        return Command::SUCCESS;
    }

    /**
     * @param list<array<string, string>> $batch
     */
    private function processBatch(array $batch): void
    {
        $userIds = array_column($batch, 'User Id');
        $existing = $this->personRepository->findByUserIds($userIds);

        foreach ($batch as $data) {
            $person = $existing[$data['User Id']] ?? null;

            if ($person !== null) {
                $person->update(
                    firstName: $data['First Name'],
                    lastName: $data['Last Name'],
                    sex: Sex::from(strtolower($data['Sex'])),
                    email: $data['Email'],
                    phone: $data['Phone'],
                    dateOfBirth: new \DateTimeImmutable($data['Date of birth']),
                    jobTitle: $data['Job Title'],
                );
            } else {
                $person = new Person(
                    userId: $data['User Id'],
                    firstName: $data['First Name'],
                    lastName: $data['Last Name'],
                    sex: Sex::from(strtolower($data['Sex'])),
                    email: $data['Email'],
                    phone: $data['Phone'],
                    dateOfBirth: new \DateTimeImmutable($data['Date of birth']),
                    jobTitle: $data['Job Title'],
                );
                $this->em->persist($person);
            }
        }

        $this->em->flush();
        $this->em->clear();
    }
}

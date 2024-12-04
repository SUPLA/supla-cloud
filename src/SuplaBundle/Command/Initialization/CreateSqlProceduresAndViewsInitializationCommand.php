<?php
namespace SuplaBundle\Command\Initialization;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Utils\StringUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateSqlProceduresAndViewsInitializationCommand extends Command implements InitializationCommand {
    private const PROCEDURES_PATH = __DIR__ . '/../../Migrations/procedures';
    private const VIEWS_PATH = __DIR__ . '/../../Migrations/views';

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setName('supla:initialize:create-sql-procedures-and-views')
            ->setDescription('Initializes database with all required procedures and views.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $procedures = array_values(array_filter(scandir(self::PROCEDURES_PATH), fn($path) => str_ends_with($path, '.sql')));
        $views = array_values(array_filter(scandir(self::VIEWS_PATH), fn($path) => str_ends_with($path, '.sql')));
        $io = new SymfonyStyle($input, $output);
        if (!$io->isQuiet()) {
            $io->title('Creating SQL procedures and views');
            $io->writeln('Number of scripts to execute: ' . (count($procedures) + count($views)));
            $io->section('Procedures');
        }
        foreach ($procedures as $file) {
            $fullPath = StringUtils::joinPaths(self::PROCEDURES_PATH, $file);
            $this->executeSqlFile($fullPath);
            if (!$io->isQuiet()) {
                $io->writeln('✅ ' . $file);
            }
        }
        if (!$io->isQuiet()) {
            $io->section('Views');
        }
        foreach ($views as $file) {
            $fullPath = StringUtils::joinPaths(self::VIEWS_PATH, $file);
            $this->executeSqlFile($fullPath);
            if (!$io->isQuiet()) {
                $io->writeln('✅ ' . $file);
            }
        }
        if (!$io->isQuiet()) {
            $io->newLine();
        }
        return 0;
    }

    private function executeSqlFile(string $fullPath): void {
        $sql = file_get_contents($fullPath);
        $this->entityManager->getConnection()->executeStatement($sql);
    }
}

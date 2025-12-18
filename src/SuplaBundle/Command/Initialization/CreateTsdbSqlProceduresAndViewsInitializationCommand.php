<?php
namespace SuplaBundle\Command\Initialization;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use SuplaBundle\Utils\StringUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateTsdbSqlProceduresAndViewsInitializationCommand extends Command {
    private const PROCEDURES_PATH = __DIR__ . '/../../Migrations/TsDbProcedures';

    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $doctrineRegistry) {
        parent::__construct();
        $this->entityManager = $doctrineRegistry->getManager('tsdb');
    }

    protected function configure() {
        $this
            ->setName('supla:initialize:create-tsdb-procedures')
            ->setDescription('Initializes TSDB database with all required procedures.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $procedures = array_values(array_filter(scandir(self::PROCEDURES_PATH), fn($path) => str_ends_with($path, '.sql')));
        $io = new SymfonyStyle($input, $output);
        if (!$io->isQuiet()) {
            $io->title('Creating TSDB procedures.');
            $io->writeln('Number of scripts to execute: ' . (count($procedures)));
        }
        foreach ($procedures as $file) {
            $procedureName = pathinfo($file, PATHINFO_FILENAME);
//            $procedurePermissions = $this->getProcedurePermissions($procedureName);
            $fullPath = StringUtils::joinPaths(self::PROCEDURES_PATH, $file);
            $this->executeSqlFile($fullPath);
//            $this->restoreProcedurePermissions($procedureName, $procedurePermissions);
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

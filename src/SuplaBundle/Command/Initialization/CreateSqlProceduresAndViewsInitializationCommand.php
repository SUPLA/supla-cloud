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
        $files = array_values(array_filter(scandir(self::PROCEDURES_PATH), fn($path) => str_ends_with($path, '.sql')));
        $io = new SymfonyStyle($input, $output);
        if (!$io->isQuiet()) {
            $io->title('Creating SQL procedures and views');
            $io->writeln('Number of scripts to execute: ' . count($files));
            $io->newLine();
        }
        foreach ($files as $file) {
            $fullPath = StringUtils::joinPaths(self::PROCEDURES_PATH, $file);
            $sql = file_get_contents($fullPath);
            $this->entityManager->getConnection()->executeStatement($sql);
            if (!$io->isQuiet()) {
                $io->writeln('✅ ' . $file);
            }
        }
        if (!$io->isQuiet()) {
            $io->newLine();
        }
        return 0;
    }
}

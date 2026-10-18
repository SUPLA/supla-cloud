<?php
namespace App\Command\Initialization;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** Explicit deployment step: never grant Cloud's business-origin replacement to Core. */
class GrantSupLanCoreProceduresCommand extends Command {
    public const TECHNICAL_PROCEDURES = [
        'supla_suplan_project', 'supla_suplan_accept_identity', 'supla_suplan_peer_prepare', 'supla_suplan_peer_ack',
        'supla_suplan_peer_distrust', 'supla_suplan_observe_root', 'supla_suplan_peer_demand',
        'supla_suplan_share_demand', 'supla_resource_access_pending', 'supla_suplan_changed_ack', 'supla_suplan_cleanup', 'supla_suplan_resource_cleanup',
    ];

    public function __construct(private EntityManagerInterface $entityManager) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName('supla:initialize:grant-suplan-core-procedures')
            ->setDescription('Grant technical SupLAN EXECUTE to an EXISTING restricted Core account (deployment operator only).')
            ->addArgument('user', InputArgument::REQUIRED)->addArgument('host', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $connection = $this->entityManager->getConnection();
        $user = $input->getArgument('user');
        $host = $input->getArgument('host');
        if (!$connection->fetchOne('SELECT 1 FROM mysql.user WHERE User=? AND Host=?', [$user, $host])) {
            throw new \InvalidArgumentException('Create and restrict the actual Core SQL account before granting EXECUTE.');
        }
        $account = $connection->quote($user) . '@' . $connection->quote($host);
        $database = $connection->quoteIdentifier($connection->getDatabase());
        foreach ($connection->fetchFirstColumn('SHOW GRANTS FOR ' . $account) as $grant) {
            $parts = explode(' ON ', $grant, 2);
            if (count($parts) !== 2) {
                if (str_starts_with($grant, 'GRANT ')) {
                    throw new \InvalidArgumentException('Resolve inherited Core SQL roles before this restricted-privilege setup.');
                }
                continue;
            }
            $scope = explode(' TO ', $parts[1], 2)[0];
            $wide = $scope === '*.*' || $scope === $database . '.*';
            $here = $wide || str_starts_with($scope, $database . '.');
            $dml = preg_match('/\b(ALL PRIVILEGES|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP)\b/i', $parts[0]);
            $business = $scope === $database . '.`supla_resource_access_replace`';
            if ($here && ($dml || (str_contains($parts[0], 'EXECUTE') && ($wide || $business)))) {
                throw new \InvalidArgumentException('Restrict Core DML and enumerate EXECUTE per technical routine before cutover.');
            }
        }
        foreach (self::TECHNICAL_PROCEDURES as $procedure) {
            $connection->executeStatement('GRANT EXECUTE ON PROCEDURE ' . $database . '.' . $connection->quoteIdentifier($procedure) . ' TO ' . $account);
        }
        $output->writeln('Technical SupLAN EXECUTE granted. Verify SELECT and denied table DML before coordinated cutover.');
        return Command::SUCCESS;
    }
}

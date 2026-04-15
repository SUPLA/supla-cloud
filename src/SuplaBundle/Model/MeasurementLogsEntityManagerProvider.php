<?php
namespace SuplaBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

readonly class MeasurementLogsEntityManagerProvider {
    public function __construct(private ManagerRegistry $managerRegistry, private string $databaseForLogs) {
    }

    public function get(): EntityManagerInterface {
        return $this->managerRegistry->getManager($this->getManagerName());
    }

    public function getManagerName(): string {
        switch ($this->databaseForLogs) {
            case 'mariadb':
                return 'logs_mariadb';

            case 'tsdb':
                return 'logs_tsdb';
        }

        throw new InvalidArgumentException(sprintf(
            'Unsupported logs database "%s". Expected one of: mariadb, tsdb.',
            $this->databaseForLogs
        ));
    }

    public function getRepository(string $entityClass): EntityRepository {
        return $this->get()->getRepository($entityClass);
    }

    public function isTsdb(): bool {
        return $this->databaseForLogs === 'tsdb';
    }

    public function isMariaDb(): bool {
        return $this->databaseForLogs === 'mariadb';
    }
}

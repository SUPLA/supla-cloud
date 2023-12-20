<?php
namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Tests\Integration\IntegrationTestCase;

abstract class DatabaseMigrationTestCase extends IntegrationTestCase {
    protected function clearDatabase() {
        $this->executeCommand('doctrine:database:drop --force');
        $this->executeCommand('doctrine:database:create --if-not-exists');
    }

    protected function loadDump(string $name) {
        $dumpFilePath = addcslashes(__DIR__ . '/dumps/test_dump_v' . $name . '.sql', '\\');
        $dump = file_get_contents($dumpFilePath);
        // remove procedures as they can't be imported via doctrine, see https://stackoverflow.com/a/20910558/878514
        $dump = preg_replace('#DELIMITER //.+?DELIMITER ;#s', '', $dump);
        $dump = preg_replace('#DELIMITER ;;.+?DELIMITER ;#s', '', $dump);
        $this->getEntityManager()->getConnection()->executeStatement($dump);
    }

    protected function loadDumpV22() {
        $this->loadDump('2.2.0');
    }

    protected function loadDumpV23() {
        $this->loadDump('2.3.0');
    }

    protected function loadDumpV2336() {
        $this->loadDump('2.3.36');
    }

    protected function loadDumpV2207() {
        $this->loadDump('22.07');
    }

    protected function migrate(string $toVersion = '') {
        $result = $this->executeCommand(trim('doctrine:migrations:migrate ' . $toVersion));
        $this->assertStringContainsString('Migrating up to', $result);
        self::$container->get('doctrine')->resetManager();
    }

    protected function initialize() {
        $this->executeCommand('supla:initialize');
        self::$container->get('doctrine')->resetManager();
    }
}

<?php
namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Tests\Integration\IntegrationTestCase;

abstract class DatabaseMigrationTestCase extends IntegrationTestCase {
    protected function clearDatabase() {
        $this->executeCommand('doctrine:database:drop --force');
        $this->executeCommand('doctrine:database:create --if-not-exists');
    }

    private function loadDump(string $name) {
        $dumpFilePath = addcslashes(__DIR__ . '/dumps/test_dump_v' . $name . '.sql', '\\');
        $dump = file_get_contents($dumpFilePath);
        // remove procedures as they can't be imported via doctrine, see https://stackoverflow.com/a/20910558/878514
        $dump = preg_replace('#DELIMITER //.+?DELIMITER ;#s', '', $dump);
        $this->getEntityManager()->getConnection()->exec($dump);
    }

    protected function loadDumpV22() {
        $this->loadDump('2.2.0');
    }

    protected function migrate(string $toVersion = '') {
        $this->executeCommand(trim('doctrine:migrations:migrate ' . $toVersion));
        $this->container->get('doctrine')->resetManager();
    }
}

<?php

namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Entity\MeasurementLogs\TemperatureLogItem;

/**
 * @see Version20200430113342
 */
class Version20200430113342MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV23();
        $this->migrate();
    }

    public function testHasLogItems() {
        /** @var \SuplaBundle\Entity\MeasurementLogs\TemperatureLogItem $log */
        $log = $this->getEntityManager()->createQuery('SELECT t FROM ' . TemperatureLogItem::class . ' t')
            ->setMaxResults(1)
            ->getSingleResult();
        $this->assertEquals(2, $log->getChannelId());
        $this->assertEquals(38, $log->getTemperature());
    }

    public function testRemovingDuplicates() {
        $logsCount = $this->getEntityManager()->createQuery('SELECT COUNT(t.channel_id) FROM ' . TemperatureLogItem::class . ' t')
            ->getSingleScalarResult();
        $this->assertEquals(2, $logsCount);
    }
}

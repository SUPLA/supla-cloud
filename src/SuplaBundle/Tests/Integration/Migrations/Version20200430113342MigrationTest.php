<?php

namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Entity\TemperatureLogItem;

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
        /** @var TemperatureLogItem $log */
        $log = $this->getEntityManager()->createQuery('SELECT t FROM ' . TemperatureLogItem::class . ' t')->getSingleResult();
        $this->assertEquals(2, $log->getChannelId());
        $this->assertEquals(38, $log->getTemperature());
    }
}

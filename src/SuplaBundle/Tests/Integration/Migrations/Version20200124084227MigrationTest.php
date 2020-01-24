<?php

namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Entity\DirectLink;

/**
 * @see Version20200124084227
 */
class Version20200124084227MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV23();
    }

    public function testReadingIpAddressWithMysqlFunction() {
        $directLink = $this->getEntityManager()->find(DirectLink::class, 1);
        $this->assertEquals('217.99.244.137', $directLink->getLastIpv4());
    }
}

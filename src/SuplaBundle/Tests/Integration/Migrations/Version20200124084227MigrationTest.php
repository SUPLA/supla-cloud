<?php

namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Entity\AuditEntry;
use SuplaBundle\Entity\ClientApp;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\IODevice;

/**
 * @see Version20200124084227
 */
class Version20200124084227MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV23();
        $this->migrate();
    }

    public function testReadingDirectLinkIpAddressWithMysqlFunction() {
        $directLink = $this->getEntityManager()->find(DirectLink::class, 1);
        $this->assertEquals('217.99.244.137', $directLink->getLastIpv4());
    }

    public function testReadingIoDeviceIpAddressesWithMysqlFunction() {
        $device = $this->getEntityManager()->find(IODevice::class, 1);
        $this->assertEquals('0.80.57.249', $device->getRegIpv4());
        $this->assertEquals('224.200.244.137', $device->getLastIpv4());
    }

    public function testReadingAuditEntryIpAddressWithMysqlFunction() {
        $auditEntry = $this->getEntityManager()->find(AuditEntry::class, 1);
        $this->assertEquals('127.0.0.1', $auditEntry->getIpv4());
    }

    public function testReadingClientAppIpAddressesWithMysqlFunction() {
        $clientApp = $this->getEntityManager()->find(ClientApp::class, 1);
        $this->assertEquals('254.250.244.237', $clientApp->getRegIpv4());
        $this->assertEquals('123.173.115.31', $clientApp->getLastAccessIpv4());
    }
}

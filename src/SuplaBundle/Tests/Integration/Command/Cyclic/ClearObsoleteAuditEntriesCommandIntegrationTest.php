<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Tests\Integration\Command\Cyclic;

use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Model\Audit\Audit;
use SuplaBundle\Repository\AuditEntryRepository;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

/**
 * @small
 */
class ClearObsoleteAuditEntriesCommandIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var AuditEntryRepository */
    private $auditEntryRepository;

    protected function initializeDatabaseForTests() {
        $audit = self::$container->get(Audit::class);
        $audit->newEntry(AuditedEvent::DIRECT_LINK_EXECUTION())->buildAndFlush();
        $audit->newEntry(AuditedEvent::SCHEDULE_BROKEN_DISABLED())->buildAndFlush();
        $this->auditEntryRepository = $audit->getRepository();
    }

    public function testNotDeletingUserImmediately() {
        $this->executeCommand('supla:clean:audit-entries');
        $this->assertCount(2, $this->auditEntryRepository->findAll());
    }

    public function testNotDeletingAfterOneDay() {
        TestTimeProvider::setTime('+1 day');
        $this->executeCommand('supla:clean:audit-entries');
        $this->assertCount(2, $this->auditEntryRepository->findAll());
    }

    public function testNotDeletingAllAfterMonthButDeletesDirectLinkExecution() {
        TestTimeProvider::setTime('+1 month');
        $this->executeCommand('supla:clean:audit-entries');
        $this->assertCount(1, $this->auditEntryRepository->findAll());
    }

    public function testDeletingAfter3Months() {
        TestTimeProvider::setTime('+3 months');
        $this->executeCommand('supla:clean:audit-entries');
        $this->assertCount(0, $this->auditEntryRepository->findAll());
    }
}

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

namespace SuplaApiBundle\Tests\Integration\Model\Audit;

use SuplaApiBundle\Model\Audit\Audit;
use SuplaBundle\Entity\AuditEntry;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Tests\Integration\IntegrationTestCase;

class AuditIntegrationTest extends IntegrationTestCase {
    /** @var Audit */
    private $audit;

    /** @before */
    public function init() {
        $this->audit = $this->container->get(Audit::class);
    }

    public function testSavingSimpleAuditEntry() {
        $this->audit->newEntry(AuditedEvent::AUTHENTICATION())->setTextParam('aaa')->buildAndFlush();
        $entries = $this->audit->getRepository()->findAll();
        $this->assertCount(1, $entries);
        /** @var AuditEntry $entry */
        $entry = current($entries);
        $this->assertEquals(AuditedEvent::AUTHENTICATION(), $entry->getEvent());
        $this->assertEquals('aaa', $entry->getTextParam());
        $this->assertTrue($entry->isSuccessful());
    }
}

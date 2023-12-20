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

use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/**
 * @small
 */
class SynchronizeEspUpdatesCommandIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    protected function initializeDatabaseForTests() {
        $this->initializeDatabaseWithMigrations();
    }

    public function testAddingNewEspUpdates() {
        $updates = [SuplaAutodiscoverMock::sampleEspUpdate(), SuplaAutodiscoverMock::sampleEspUpdate()];
        SuplaAutodiscoverMock::mockResponse('esp-updates', $updates);
        $this->executeCommand('supla:esp:sync');
        $existingUpdates = $this->getEntityManager()->getConnection()->fetchAllAssociative('SELECT * FROM esp_update');
        $this->assertCount(2, $existingUpdates);
        $this->assertTrue(boolval($existingUpdates[0]['is_synced']));
        $this->assertEquals($updates[0], array_diff_key($existingUpdates[0], ['id' => '', 'is_synced' => '']));
    }

    /** @depends testAddingNewEspUpdates */
    public function testNotUpdatingWhenFailsToContactAd() {
        SuplaAutodiscoverMock::mockResponse('esp-updates', false);
        $this->executeCommand('supla:esp:sync');
        $existingUpdates = $this->getEntityManager()->getConnection()->fetchAllAssociative('SELECT * FROM esp_update');
        $this->assertCount(2, $existingUpdates);
    }

    /** @depends testAddingNewEspUpdates */
    public function testRemovingUpdates() {
        $updates = [SuplaAutodiscoverMock::sampleEspUpdate()];
        SuplaAutodiscoverMock::mockResponse('esp-updates', $updates);
        $this->executeCommand('supla:esp:sync');
        $existingUpdates = $this->getEntityManager()->getConnection()->fetchAllAssociative('SELECT * FROM esp_update');
        $this->assertCount(1, $existingUpdates);
        $this->assertEquals($updates[0]['latest_software_version'], $existingUpdates[0]['latest_software_version']);
    }
}

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

namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Entity\Main\IODeviceChannel;

/**
 * @see Version20220208164512
 */
class DatabaseV2336MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV2336();
        $this->initialize();
    }

    public function testMigratedCorrectly() {
        $this->configHasBeenChangedCoorrectlyVersion20220208164512();
    }

    /**
     * @see Version20220208164512
     */
    private function configHasBeenChangedCoorrectlyVersion20220208164512() {
        $this->migratedElectricityMeterWithInitialValues();
        $this->migratedElectricityMeterWithoutInitialValues();
        $this->migratedImpulseCounterWithoutInitialValue();
        $this->migratedNumberOfAttemptsToOpenOrClose();
    }

    private function migratedElectricityMeterWithInitialValues() {
        /** @var IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 143);
        $this->assertArrayHasKey('addToHistory', $channel->getUserConfig());
        $this->assertTrue($channel->getUserConfigValue('addToHistory'));
    }

    private function migratedElectricityMeterWithoutInitialValues() {
        /** @var IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 73);
        $this->assertArrayHasKey('addToHistory', $channel->getUserConfig());
        $this->assertFalse($channel->getUserConfigValue('addToHistory'));
    }

    private function migratedImpulseCounterWithoutInitialValue() {
        /** @var IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 195);
        $this->assertArrayHasKey('addToHistory', $channel->getUserConfig());
        $this->assertFalse($channel->getUserConfigValue('addToHistory'));
        $this->assertEquals(0, $channel->getUserConfigValue('initialValue'));
    }

    private function migratedNumberOfAttemptsToOpenOrClose() {
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, 156);
        $this->assertArrayNotHasKey('numberOfAttemptsToOpenOrClose', $channel->getUserConfig());
        $this->assertArrayHasKey('numberOfAttemptsToOpen', $channel->getUserConfig());
        $this->assertArrayHasKey('numberOfAttemptsToClose', $channel->getUserConfig());
        $this->assertEquals(3, $channel->getUserConfigValue('numberOfAttemptsToOpen'));
        $this->assertEquals(3, $channel->getUserConfigValue('numberOfAttemptsToClose'));
    }
}

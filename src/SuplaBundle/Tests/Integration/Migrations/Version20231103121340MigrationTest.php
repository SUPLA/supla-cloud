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
 * @see Version20231103121340
 */
class Version20231103121340MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV2207();
        $this->initialize();
    }

    public function testLocationsAreMoved() {
        $this->assertMovedPowerSwitchAndAtToTheSameLocation();
        $this->assertMovedGateAndSensorToTheSameLocation();
        $this->assertNotMovedUnpairedSensorToAnyNewLocation();
    }

    private function assertMovedPowerSwitchAndAtToTheSameLocation() {
        $powerSwitch = $this->freshEntityById(IODeviceChannel::class, 1);
        $at = $this->freshEntityById(IODeviceChannel::class, 3);
        $this->assertEquals($powerSwitch->getLocation()->getId(), $at->getLocation()->getId());
    }

    private function assertMovedGateAndSensorToTheSameLocation() {
        $gate = $this->freshEntityById(IODeviceChannel::class, 6);
        $sensor = $this->freshEntityById(IODeviceChannel::class, 8);
        $this->assertEquals($gate->getLocation()->getId(), $sensor->getLocation()->getId());
    }

    private function assertNotMovedUnpairedSensorToAnyNewLocation() {
        $sensor = $this->freshEntityById(IODeviceChannel::class, 9);
        $this->assertEquals(1, $sensor->getLocation()->getId());
    }
}

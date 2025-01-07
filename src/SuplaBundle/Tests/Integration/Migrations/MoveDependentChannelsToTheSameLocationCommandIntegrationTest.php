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

class MoveDependentChannelsToTheSameLocationCommandIntegrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV2207();
        $this->initialize();
    }

    public function testMigratedCorrectly() {
        $this->assertMovedPowerSwitchAndAtToTheSameLocation();
        $this->assertMovedGateAndSensorToTheSameLocation();
        $this->assertNotMovedUnpairedSensorToAnyNewLocation();
        $this->assertMovedIcToPowerSwitchLocation();
        $this->assertChangedVisibilityOfMeter();
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

    private function assertMovedIcToPowerSwitchLocation() {
        $powerSwitch = $this->freshEntityById(IODeviceChannel::class, 114);
        $ic = $this->freshEntityById(IODeviceChannel::class, 74);
        $this->assertEquals($ic->getLocation()->getId(), $powerSwitch->getLocation()->getId());
        $this->assertEquals(2, $powerSwitch->getLocation()->getId());
    }

    private function assertChangedVisibilityOfMeter() {
        $powerSwitch = $this->freshEntityById(IODeviceChannel::class, 121);
        $meter = $this->freshEntityById(IODeviceChannel::class, 143);
        $this->assertEquals($powerSwitch->getHidden(), $meter->getHidden());
        $this->assertFalse($powerSwitch->getHidden());
    }
}

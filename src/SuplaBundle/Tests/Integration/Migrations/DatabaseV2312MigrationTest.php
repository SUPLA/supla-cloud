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
use SuplaBundle\Entity\Main\PushNotification;

class DatabaseV2312MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDump('23.12');
        $this->initialize();
    }

    public function testMigratedCorrectly() {
        $this->migratedHvacAutoToHeatCool();
        $this->migratedEmojisInTextColumns();
        $this->migratedRollerShutterTimesToUserConfig();
    }

    /**
     * @see Version20231219083453
     */
    private function migratedHvacAutoToHeatCool(): void {
        $hvacChannel = $this->freshEntityById(IODeviceChannel::class, 98);
        $weeklySchedule = $hvacChannel->getUserConfigValue('weeklySchedule');
        $this->assertEquals('HEAT_COOL', $weeklySchedule['programSettings']['3']['mode']);
    }

    /**
     * @see Version20231221114509
     */
    private function migratedEmojisInTextColumns(): void {
        $channel = $this->freshEntityById(IODeviceChannel::class, 1);
        $this->assertStringContainsString('❤️', $channel->getCaption());
        $channel = $this->freshEntityById(IODeviceChannel::class, 13);
        $this->assertStringContainsString('❤️', $channel->getUserConfigValue('unit'));
        $notification = $this->freshEntityById(PushNotification::class, 1);
        $this->assertStringContainsString('❤️', $notification->getBody());
    }

    /**
     * @see Version20240415113159
     */
    private function migratedRollerShutterTimesToUserConfig(): void {
        /** @var IODeviceChannel $rollerShutterChannel */
        $rollerShutterChannel = $this->freshEntityById(IODeviceChannel::class, 57);
        $this->assertEquals(0, $rollerShutterChannel->getParam1());
        $this->assertEquals(0, $rollerShutterChannel->getParam3());
        $this->assertEquals(12000, $rollerShutterChannel->getUserConfigValue('openingTimeMs'));
        $this->assertEquals(10000, $rollerShutterChannel->getUserConfigValue('closingTimeMs'));
    }
}

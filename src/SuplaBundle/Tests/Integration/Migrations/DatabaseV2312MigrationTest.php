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
        $this->migratedIcConfigFromParams();
        $this->migratedRelaysRelatedChannelsConfigFromParams();
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

    /**
     * @see Version20240802194013
     */
    private function migratedIcConfigFromParams() {
        /** @var IODeviceChannel $icChannel */
        $icChannel = $this->freshEntityById(IODeviceChannel::class, 13);
        $this->assertNull($icChannel->getTextParam1());
        $this->assertNull($icChannel->getTextParam2());
        $this->assertEquals(0, $icChannel->getParam2());
        $this->assertEquals(55, $icChannel->getUserConfigValue('pricePerUnit'));
        $this->assertEquals(100330, $icChannel->getUserConfigValue('initialValue'));
        $this->assertEquals('PLN', $icChannel->getUserConfigValue('currency'));
    }

    /**
     * @see Version20240824185033
     */
    private function migratedRelaysRelatedChannelsConfigFromParams() {
        /** @var IODeviceChannel $powerSwitch */
        $powerSwitch = $this->freshEntityById(IODeviceChannel::class, 47);
        $this->assertEquals(0, $powerSwitch->getParam1());
        $this->assertEquals(78, $powerSwitch->getUserConfigValue('relatedMeterChannelId'));
        $gasMeter = $this->freshEntityById(IODeviceChannel::class, 78);
        $this->assertEquals(0, $gasMeter->getParam4());
        $staircaseTimer = $this->freshEntityById(IODeviceChannel::class, 49);
        $this->assertEquals(250, $staircaseTimer->getParam1());
        $this->assertEquals(0, $staircaseTimer->getParam2());
        $this->assertEquals(80, $staircaseTimer->getUserConfigValue('relatedMeterChannelId'));
        $heatMeter = $this->freshEntityById(IODeviceChannel::class, 80);
        $this->assertEquals(0, $heatMeter->getParam4());
    }
}

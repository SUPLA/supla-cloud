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
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;

/**
 * @see Version20230926065848
 */
class Version20230926065848MigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV2207();
        $this->initialize();
    }

    public function testMovedInvertedLogicSettingFromParam3ToUserConfig() {
        $sensorNotInverted = $this->getEntityManager()->find(IODeviceChannel::class, 24);
        $sensorInverted = $this->getEntityManager()->find(IODeviceChannel::class, 33);
        $this->assertTrue($sensorInverted->getUserConfigValue('invertedLogic'));
        $this->assertFalse($sensorNotInverted->getUserConfigValue('invertedLogic'));
        $this->assertEquals(0, $sensorInverted->getParam3());
        $this->assertEquals(0, $sensorNotInverted->getParam3());
    }

    public function testMovedTemperatureAndHumidityAdjustmentsToUserConfig() {
        $tempHumAdjusted = $this->getEntityManager()->find(IODeviceChannel::class, 64);
        $this->assertEquals(123, $tempHumAdjusted->getUserConfigValue('temperatureAdjustment'));
        $this->assertEquals(-123, $tempHumAdjusted->getUserConfigValue('humidityAdjustment'));
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($tempHumAdjusted);
        $this->assertEquals(1.23, $channelConfig['temperatureAdjustment']);
        $this->assertEquals(-1.23, $channelConfig['humidityAdjustment']);
        $this->assertEquals(0, $tempHumAdjusted->getParam2());
        $this->assertEquals(0, $tempHumAdjusted->getParam3());
        $tempAdjusted = $this->getEntityManager()->find(IODeviceChannel::class, 62);
        $this->assertEquals(12, $tempAdjusted->getUserConfigValue('temperatureAdjustment'));
        $humNotAdjusted = $this->getEntityManager()->find(IODeviceChannel::class, 63);
        $this->assertEquals(0, $humNotAdjusted->getUserConfigValue('humidityAdjustment'));
    }
}

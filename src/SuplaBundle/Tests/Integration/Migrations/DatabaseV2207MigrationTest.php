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

use AppKernel;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SettingsString;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\InstanceSettings;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Repository\SettingsStringRepository;

class DatabaseV2207MigrationTest extends DatabaseMigrationTestCase {
    private const PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH = AppKernel::VAR_PATH . '/local/target-cloud-token';

    /** @before */
    public function prepare() {
        $this->loadDumpV2207();
        file_put_contents(self::PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH, 'the_token_for_test');
        $this->initialize();
    }

    public function testMigratedCorrectly() {
        $this->calculatedSceneEstimatedExecutionTimeVersion20220929090847();
        $this->movingTargetCloudTokenToDatabaseVersion20230612185931();
        $this->calculatingUserHomeCoordinatesVersion20230815145146();
        $this->movingInvertedLogicVersion20230926065848();
        $this->movingLocationsVersion20231103121340();
    }

    /**
     * @see Version20220929090847
     */
    private function calculatedSceneEstimatedExecutionTimeVersion20220929090847() {
        $this->assertEquals(2000, $this->getEntityManager()->find(Scene::class, 1)->getEstimatedExecutionTime());
        $this->assertEquals(62000, $this->getEntityManager()->find(Scene::class, 2)->getEstimatedExecutionTime());
        $this->assertEquals(61000, $this->getEntityManager()->find(Scene::class, 3)->getEstimatedExecutionTime());
        $this->assertEquals(3000, $this->getEntityManager()->find(Scene::class, 4)->getEstimatedExecutionTime());
        $this->assertEquals(32000, $this->getEntityManager()->find(Scene::class, 5)->getEstimatedExecutionTime());
    }

    /**
     * @see Version20230612185931
     */
    private function movingTargetCloudTokenToDatabaseVersion20230612185931() {
        /** @var SettingsStringRepository $repository */
        $repository = $this->getDoctrine()->getRepository(SettingsString::class);
        $this->assertTrue($repository->hasValue(InstanceSettings::TARGET_TOKEN));
        $this->assertEquals('the_token_for_test', $repository->getValue(InstanceSettings::TARGET_TOKEN));
        $this->assertFileDoesNotExist(self::PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH);
    }

    /**
     * @see Version20230815145146
     */
    private function calculatingUserHomeCoordinatesVersion20230815145146() {
        $user = $this->getEntityManager()->find(User::class, 1);
        $this->assertEquals(52.5, $user->getHomeLatitude());
        $this->assertEquals(13.36666, $user->getHomeLongitude());
    }

    /**
     * @see Version20230926065848
     */
    private function movingInvertedLogicVersion20230926065848() {
        $this->assertMovedInvertedLogicSettingFromParam3ToUserConfig();
        $this->assertMovedTemperatureAndHumidityAdjustmentsToUserConfig();
    }

    private function assertMovedInvertedLogicSettingFromParam3ToUserConfig() {
        $sensorNotInverted = $this->getEntityManager()->find(IODeviceChannel::class, 24);
        $sensorInverted = $this->getEntityManager()->find(IODeviceChannel::class, 33);
        $this->assertTrue($sensorInverted->getUserConfigValue('invertedLogic'));
        $this->assertFalse($sensorNotInverted->getUserConfigValue('invertedLogic'));
        $this->assertEquals(0, $sensorInverted->getParam3());
        $this->assertEquals(0, $sensorNotInverted->getParam3());
    }

    private function assertMovedTemperatureAndHumidityAdjustmentsToUserConfig() {
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

    /**
     * @see Version20231103121340
     */
    private function movingLocationsVersion20231103121340() {
        $this->assertMovedPowerSwitchAndAtToTheSameLocation();
        $this->assertMovedGateAndSensorToTheSameLocation();
        $this->assertNotMovedUnpairedSensorToAnyNewLocation();
        $this->assertMovedIcToPowerSwitchLocation();
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
}

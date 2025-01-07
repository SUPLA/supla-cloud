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

namespace SuplaBundle\Tests\Model\UserConfigTranslator;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Model\UserConfigTranslator\HvacThermostatConfigTranslator;
use SuplaBundle\Model\UserConfigTranslator\IODeviceConfigTranslator;

class IODeviceConfigTranslatorTest extends TestCase {
    /** @var IODeviceConfigTranslator */
    private $translator;

    /** @before */
    public function init() {
        $hvacConfigTranslator = $this->createMock(HvacThermostatConfigTranslator::class);
        $this->translator = new IODeviceConfigTranslator($hvacConfigTranslator);
    }

    /** @dataProvider validConfigs */
    public function testValidConfigs(array $config) {
        $device = new IODevice();
        $device->setUserConfig([
            'statusLed' => 'OFF_WHEN_CONNECTED',
            'screenBrightness' => ['level' => 13],
            'buttonVolume' => 14,
            'userInterface' => ['disabled' => false],
            'automaticTimeSync' => false,
            'homeScreen' => ['content' => 'NONE', 'offDelay' => 0],
        ]);
        EntityUtils::setField($device, 'properties', json_encode([
            'homeScreenContentAvailable' => ["NONE", "TEMPERATURE", "HUMIDITY", "TIME", "TIME_DATE"],
        ]));
        $this->translator->setConfig($device, $config);
        $newConfig = $this->translator->getConfig($device);
        $this->assertEquals(array_intersect_key($newConfig, $config), $config);
    }

    public static function validConfigs() {
        return [
            [['statusLed' => 'OFF_WHEN_CONNECTED']],
            [['statusLed' => 'ALWAYS_OFF']],
            [['screenBrightness' => ['level' => 1, 'auto' => false]]],
            [['screenBrightness' => ['level' => 12, 'auto' => false]]],
            [['screenBrightness' => ['level' => 12, 'auto' => true]]],
            [['screenBrightness' => ['level' => -12, 'auto' => true]]],
            [['buttonVolume' => 0]],
            [['buttonVolume' => 55]],
            [['buttonVolume' => 100]],
            [['userInterface' => ['disabled' => true]]],
            [['userInterface' => ['disabled' => false]]],
            [['userInterface' => [
                'disabled' => 'partial',
                'minAllowedTemperatureSetpointFromLocalUI' => 10,
                'maxAllowedTemperatureSetpointFromLocalUI' => 30,
            ]]],
            [['automaticTimeSync' => true]],
            [['automaticTimeSync' => false]],
            [['homeScreen' => ['content' => 'NONE', 'offDelay' => 100]]],
            [['homeScreen' => ['content' => 'TEMPERATURE', 'offDelay' => 300]]],
            [['homeScreen' => ['content' => 'NONE', 'offDelay' => 0]]],
        ];
    }

    /** @dataProvider invalidConfigs */
    public function testInvalidConfigs(array $config) {
        $this->expectException(\InvalidArgumentException::class);
        $this->testValidConfigs($config);
    }

    public static function invalidConfigs() {
        return [
            [['unicorn' => 'UNICORN']],
            [['statusLed' => 'UNICORN']],
            [['screenBrightness' => 'automobil']],
            [['screenBrightness' => -5]],
            [['screenBrightness' => ['level' => -12, 'auto' => false]]],
            [['screenBrightness' => ['level' => 102, 'auto' => false]]],
            [['screenBrightness' => ['level' => 0, 'auto' => false]]],
            [['screenBrightness' => ['level' => '53', 'auto' => false]]],
            [['screenBrightness' => ['level' => 50.3, 'auto' => false]]],
            [['screenBrightness' => ['level' => -101, 'auto' => true]]],
            [['buttonVolume' => 'auto']],
            [['buttonVolume' => -5]],
            [['buttonVolume' => 120]],
            [['buttonVolume' => '50']],
            [['buttonVolume' => 50.3]],
            [['userInterface' => true]],
            [['userInterface' => []]],
            [['userInterface' => ['unicorn' => 'rainbow']]],
            [['userInterface' => ['disabled' => true, 'unicorn' => 'rainbow']]],
            [['userInterface' => [
                'disabled' => 'partial',
                'minAllowedTemperatureSetpointFromLocalUI' => 20,
                'maxAllowedTemperatureSetpointFromLocalUI' => 10,
            ]]],
            [['userInterface' => [
                'disabled' => 'partial',
                'minAllowedTemperatureSetpointFromLocalUI' => -2000,
                'maxAllowedTemperatureSetpointFromLocalUI' => 10,
            ]]],
            [['automaticTimeSync' => 50.3]],
            [['automaticTimeSync' => 'true']],
            [['homeScreen' => ['content' => 'MAIN_AND_AUX_TEMPERATURE', 'offDelay' => 1000]]],
            [['homeScreen' => ['content' => 'UNICORN', 'offDelay' => 10000]]],
            [['homeScreen' => ['content' => 'NONE', 'offDelay' => 4000]]],
            [['homeScreen' => ['content' => 'NONE']]],
            [['homeScreen' => ['offDelay' => 10000]]],
            [['homeScreen' => ['content' => 'NONE', 'offDelay' => 1000, 'extra' => 'unicorn']]],
            [['homeScreen' => 2]],
        ];
    }

    public function testCantSetConfigThatIsNotSetByTheDevice() {
        $this->expectException(\InvalidArgumentException::class);
        $this->translator->setConfig(new IODevice(), ['statusLed' => 'ALWAYS_OFF']);
    }

    public function testIgnoresSettingHomeScreenContentAvailable() {
        $device = new IODevice();
        $this->translator->setConfig($device, ['homeScreenContentAvailable' => 'ALWAYS_OFF']);
        $this->assertEmpty($this->translator->getConfig($device));
    }

    public function testDefaultUserInterfaceConstraints() {
        $device = new IODevice();
        $device->setUserConfig(['userInterface' => ['disabled' => false]]);
        $config = $this->translator->getConfig($device);
        $this->assertArrayHasKey('userInterfaceConstraints', $config);
        $this->assertEquals(-1000, $config['userInterfaceConstraints']['minAllowedTemperatureSetpoint']);
        $this->assertEquals(1000, $config['userInterfaceConstraints']['maxAllowedTemperatureSetpoint']);
    }

    public function testSettingUserInterfacePartialWithoutTemperatures() {
        $device = new IODevice();
        $device->setUserConfig(['userInterface' => ['disabled' => false]]);
        $this->translator->setConfig($device, ['userInterface' => ['disabled' => 'partial']]);
        $config = $this->translator->getConfig($device);
        $this->assertArrayHasKey('userInterface', $config);
        $this->assertEquals(
            $config['userInterfaceConstraints']['minAllowedTemperatureSetpoint'],
            $config['userInterface']['minAllowedTemperatureSetpointFromLocalUI']
        );
        $this->assertEquals(
            $config['userInterfaceConstraints']['maxAllowedTemperatureSetpoint'],
            $config['userInterface']['maxAllowedTemperatureSetpointFromLocalUI']
        );
    }

    public function testSettingHomeScreenOffDelayType() {
        $device = new IODevice();
        $device->setUserConfig(['homeScreen' => ['content' => 'NONE', 'offDelay' => 100, 'offDelayType' => 'ALWAYS_ENABLED']]);
        EntityUtils::setField($device, 'properties', json_encode([
            'homeScreenContentAvailable' => ["NONE", "TEMPERATURE", "HUMIDITY", "TIME", "TIME_DATE"],
        ]));
        $this->translator->setConfig(
            $device,
            ['homeScreen' => ['content' => 'NONE', 'offDelay' => 100, 'offDelayType' => 'ENABLED_WHEN_DARK']]
        );
        $config = $this->translator->getConfig($device);
        $this->assertArrayHasKey('homeScreen', $config);
        $this->assertEquals('ENABLED_WHEN_DARK', $config['homeScreen']['offDelayType']);
    }
}

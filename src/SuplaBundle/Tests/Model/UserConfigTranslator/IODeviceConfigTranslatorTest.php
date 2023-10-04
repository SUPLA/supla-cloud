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
use SuplaBundle\Model\UserConfigTranslator\IODeviceConfigTranslator;

class IODeviceConfigTranslatorTest extends TestCase {
    /** @var IODeviceConfigTranslator */
    private $translator;

    /** @before */
    public function init() {
        $this->translator = new IODeviceConfigTranslator();
    }

    /** @dataProvider validConfigs */
    public function testValidConfigs(array $config) {
        $device = new IODevice();
        $device->setUserConfig([
            'statusLed' => 'OFF_WHEN_CONNECTED',
            'screenBrightness' => 13,
            'buttonVolume' => 14,
            'userInterfaceDisabled' => false,
            'automaticTimeSync' => false,
            'screenSaver' => [],
        ]);
        EntityUtils::setField($device, 'properties', json_encode([
            'screenSaverModesAvailable' => ["OFF", "TEMPERATURE", "HUMIDITY", "TIME", "TIME_DATE"],
        ]));
        $this->translator->setConfig($device, $config);
        $newConfig = $this->translator->getConfig($device);
        $this->assertEquals(array_intersect_key($newConfig, $config), $config);
    }

    public function validConfigs() {
        return [
            [['statusLed' => 'OFF_WHEN_CONNECTED']],
            [['statusLed' => 'ALWAYS_OFF']],
            [['screenBrightness' => 'auto']],
            [['screenBrightness' => 0]],
            [['screenBrightness' => 55]],
            [['screenBrightness' => 100]],
            [['buttonVolume' => 0]],
            [['buttonVolume' => 55]],
            [['buttonVolume' => 100]],
            [['userInterfaceDisabled' => true]],
            [['userInterfaceDisabled' => false]],
            [['automaticTimeSync' => true]],
            [['automaticTimeSync' => false]],
            [['screenSaver' => ['mode' => 'OFF', 'delay' => 1000]]],
            [['screenSaver' => ['mode' => 'TEMPERATURE', 'delay' => 10000]]],
        ];
    }

    /** @dataProvider invalidConfigs */
    public function testInvalidConfigs(array $config) {
        $this->expectException(\InvalidArgumentException::class);
        $this->testValidConfigs($config);
    }

    public function invalidConfigs() {
        return [
            [['unicorn' => 'UNICORN']],
            [['statusLed' => 'UNICORN']],
            [['screenBrightness' => 'automobil']],
            [['screenBrightness' => -5]],
            [['screenBrightness' => 120]],
            [['screenBrightness' => '50']],
            [['screenBrightness' => 50.3]],
            [['buttonVolume' => 'auto']],
            [['buttonVolume' => -5]],
            [['buttonVolume' => 120]],
            [['buttonVolume' => '50']],
            [['buttonVolume' => 50.3]],
            [['userInterfaceDisabled' => 50.3]],
            [['userInterfaceDisabled' => 'true']],
            [['automaticTimeSync' => 50.3]],
            [['automaticTimeSync' => 'true']],
            [['screenSaver' => ['mode' => 'MAIN_AND_AUX_TEMPERATURE', 'delay' => 1000]]],
            [['screenSaver' => ['mode' => 'UNICORN', 'delay' => 10000]]],
            [['screenSaver' => ['mode' => 'OFF', 'delay' => 0]]],
            [['screenSaver' => ['mode' => 'OFF', 'delay' => 8999999]]],
            [['screenSaver' => ['mode' => 'OFF']]],
            [['screenSaver' => ['delay' => 10000]]],
            [['screenSaver' => ['mode' => 'OFF', 'delay' => 1000, 'extra' => 'unicorn']]],
            [['screenSaver' => 2]],
        ];
    }

    public function testCantSetConfigThatIsNotSetByTheDevice() {
        $this->expectException(\InvalidArgumentException::class);
        $this->translator->setConfig(new IODevice(), ['statusLed' => 'ALWAYS_OFF']);
    }

    public function testIgnoresSettingScreenSaverModesAvailable() {
        $device = new IODevice();
        $this->translator->setConfig($device, ['screenSaverModesAvailable' => 'ALWAYS_OFF']);
        $this->assertEmpty($this->translator->getConfig($device));
    }
}

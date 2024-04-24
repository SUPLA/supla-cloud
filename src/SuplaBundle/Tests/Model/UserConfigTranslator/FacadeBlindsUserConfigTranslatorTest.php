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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Model\UserConfigTranslator\FacadeBlindsUserConfigTranslator;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class FacadeBlindsUserConfigTranslatorTest extends TestCase {
    use UnitTestHelper;

    private FacadeBlindsUserConfigTranslator $configTranslator;
    private IODeviceChannel $channel;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new FacadeBlindsUserConfigTranslator();
        $this->channel = new IODeviceChannel();
        $this->channel->setUserConfigValue('facadeBlindType', 'UNKNOWN');
        $this->channel->setUserConfigValue('timeMargin', 0);
    }

    public function testSettingTimes() {
        $this->configTranslator->setConfig($this->channel, ['tiltingTimeS' => 10.1]);
        $this->assertEquals(10100, $this->channel->getUserConfigValue('tiltingTimeMs'));
    }

    public function testSettingBlindType() {
        $this->configTranslator->setConfig($this->channel, ['facadeBlindType' => 'CHANGES_POSITION_WHILE_TILTING']);
        $this->assertEquals('CHANGES_POSITION_WHILE_TILTING', $this->channel->getUserConfigValue('facadeBlindType'));
    }

    /** @dataProvider invalidConfigs */
    public function testSettingInvalidConfigs(array $invalidConfig) {
        $this->expectException(\InvalidArgumentException::class);
        $this->configTranslator->setConfig($this->channel, $invalidConfig);
    }

    public static function invalidConfigs() {
        return [
            [['facadeBlindType' => 'UNICORN']],
            [['timeMargin' => -1]],
            [['timeMargin' => 'UNICORN']],
            [['timeMargin' => null]],
        ];
    }

    public function testGettingConfig() {
        $this->channel->setUserConfigValue('tiltingTimeMs', 10230);
        $config = $this->configTranslator->getConfig($this->channel);
        $expected = [
            'tiltingTimeS' => 10.2,
            'tilt0Angle' => 0,
            'tilt100Angle' => 0,
            'timeMargin' => 0,
            'facadeBlindType' => 'UNKNOWN',
        ];
        $this->assertEquals($expected, $config);
    }

    public function testWaitingForConfigInit() {
        $defaultConfig = $this->configTranslator->getConfig(new IODeviceChannel());
        $this->assertTrue($defaultConfig['waitingForConfigInit']);
    }

    public function testTimeMargin() {
        $this->channel->setUserConfigValue('timeMargin', 0);
        $this->assertEquals(0, $this->configTranslator->getConfig($this->channel)['timeMargin']);
        $this->configTranslator->setConfig($this->channel, ['timeMargin' => 0]);
        $this->configTranslator->setConfig($this->channel, ['timeMargin' => 2]);
        $this->assertEquals(2, $this->configTranslator->getConfig($this->channel)['timeMargin']);
        $this->configTranslator->setConfig($this->channel, ['timeMargin' => 'DEVICE_SPECIFIC']);
        $this->assertEquals('DEVICE_SPECIFIC', $this->configTranslator->getConfig($this->channel)['timeMargin']);
    }

    public function testMotorUpsideDown() {
        $this->configTranslator->setConfig($this->channel, ['motorUpsideDown' => true]);
        $this->assertArrayNotHasKey('motorUpsideDown', $this->configTranslator->getConfig($this->channel));
        $this->channel->setUserConfigValue('motorUpsideDown', true);
        $this->assertArrayHasKey('motorUpsideDown', $this->configTranslator->getConfig($this->channel));
        $this->assertTrue($this->configTranslator->getConfig($this->channel)['motorUpsideDown']);
        $this->configTranslator->setConfig($this->channel, ['motorUpsideDown' => false]);
        $this->assertFalse($this->configTranslator->getConfig($this->channel)['motorUpsideDown']);
    }
}

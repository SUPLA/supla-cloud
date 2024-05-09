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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Model\UserConfigTranslator\OpeningClosingTimeUserConfigTranslator;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class OpeningClosingTimeUserConfigTranslatorTest extends TestCase {
    use UnitTestHelper;

    /** @var OpeningClosingTimeUserConfigTranslator */
    private $configTranslator;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannel */
    private $channel;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new OpeningClosingTimeUserConfigTranslator();
        $this->channel = new IODeviceChannel();
        $this->channel->setUserConfigValue('timeMargin', 0);
    }

    public function testSettingTimes() {
        $this->configTranslator->setConfig($this->channel, ['openingTimeS' => 10, 'closingTimeS' => 20]);
        $this->assertEquals(10000, $this->channel->getUserConfigValue('openingTimeMs'));
        $this->assertEquals(20000, $this->channel->getUserConfigValue('closingTimeMs'));
    }

    public function testSettingLargeTimes() {
        $this->configTranslator->setConfig($this->channel, ['openingTimeS' => 100, 'closingTimeS' => 500]);
        $this->assertEquals(100000, $this->channel->getUserConfigValue('openingTimeMs'));
        $this->assertEquals(500000, $this->channel->getUserConfigValue('closingTimeMs'));
    }

    public function testSettingTooLargeTimes() {
        $this->configTranslator->setConfig($this->channel, ['openingTimeS' => 700, 'closingTimeS' => 700]);
        $this->assertEquals(600000, $this->channel->getUserConfigValue('openingTimeMs'));
        $this->assertEquals(600000, $this->channel->getUserConfigValue('closingTimeMs'));
    }

    public function testMinimumTimeIfNoAutoCalibration() {
        $this->configTranslator->setConfig($this->channel, ['openingTimeS' => 0, 'closingTimeS' => 0]);
        $this->assertEquals(0, $this->channel->getUserConfigValue('openingTimeMs'));
        $this->assertEquals(0, $this->channel->getUserConfigValue('closingTimeMs'));
    }

    public function testAllow0IfAutoCalibration() {
        EntityUtils::setField($this->channel, 'flags', ChannelFunctionBitsFlags::AUTO_CALIBRATION_AVAILABLE);
        $this->configTranslator->setConfig($this->channel, ['openingTimeS' => 0, 'closingTimeS' => 0]);
        $this->assertEquals(0, $this->channel->getUserConfigValue('openingTimeMs'));
        $this->assertEquals(0, $this->channel->getUserConfigValue('closingTimeMs'));
    }

    public function testBoth0IfFirst0() {
        EntityUtils::setField($this->channel, 'flags', ChannelFunctionBitsFlags::AUTO_CALIBRATION_AVAILABLE);
        $this->configTranslator->setConfig($this->channel, ['openingTimeS' => 0, 'closingTimeS' => 1]);
        $this->assertEquals(0, $this->channel->getUserConfigValue('openingTimeMs'));
        $this->assertEquals(0, $this->channel->getUserConfigValue('closingTimeMs'));
        $this->configTranslator->setConfig($this->channel, ['openingTimeS' => 1, 'closingTimeS' => 0]);
        $this->assertEquals(1000, $this->channel->getUserConfigValue('openingTimeMs'));
        $this->assertEquals(0, $this->channel->getUserConfigValue('closingTimeMs'));
    }

    public function testGettingConfigWithFlags() {
        EntityUtils::setField(
            $this->channel,
            'flags',
            ChannelFunctionBitsFlags::AUTO_CALIBRATION_AVAILABLE | ChannelFunctionBitsFlags::RECALIBRATE_ACTION_AVAILABLE
        );
        $this->channel->setUserConfigValue('openingTimeMs', 12300);
        $this->channel->setUserConfigValue('closingTimeMs', 23400);
        $config = $this->configTranslator->getConfig($this->channel);
        $expected = [
            'openingTimeS' => 12.3,
            'closingTimeS' => 23.4,
            'timeSettingAvailable' => true,
            'recalibrateAvailable' => true,
            'autoCalibrationAvailable' => true,
            'timeMargin' => 0,
        ];
        $this->assertEquals($expected, $config);
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

    /** @dataProvider invalidConfigs */
    public function testSettingInvalidConfigs(array $invalidConfig) {
        $this->expectException(\InvalidArgumentException::class);
        $this->configTranslator->setConfig($this->channel, $invalidConfig);
    }

    public static function invalidConfigs() {
        return [
            [['timeMargin' => -1]],
            [['timeMargin' => 'UNICORN']],
            [['timeMargin' => '']],
        ];
    }
}

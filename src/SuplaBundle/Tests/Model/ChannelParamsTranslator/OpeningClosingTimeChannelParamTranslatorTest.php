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

namespace SuplaBundle\Tests\Model\ChannelParamsTranslator;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Model\ChannelParamsTranslator\OpeningClosingTimeChannelParamTranslator;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class OpeningClosingTimeChannelParamTranslatorTest extends TestCase {
    use UnitTestHelper;

    /** @var OpeningClosingTimeChannelParamTranslator */
    private $configTranslator;
    /** @var IODeviceChannel */
    private $channel;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new OpeningClosingTimeChannelParamTranslator();
        $this->channel = new IODeviceChannel();
    }

    public function testSettingTimes() {
        $this->configTranslator->setParamsFromConfig($this->channel, ['openingTimeS' => 10, 'closingTimeS' => 20]);
        $this->assertEquals(100, $this->channel->getParam1());
        $this->assertEquals(200, $this->channel->getParam3());
    }

    public function testMinimumTimeIfNoAutoCalibration() {
        $this->configTranslator->setParamsFromConfig($this->channel, ['openingTimeS' => 0, 'closingTimeS' => 0]);
        $this->assertEquals(0, $this->channel->getParam1());
        $this->assertEquals(0, $this->channel->getParam3());
    }

    public function testAllow0IfAutoCalibration() {
        EntityUtils::setField($this->channel, 'flags', ChannelFunctionBitsFlags::AUTO_CALIBRATION_AVAILABLE);
        $this->configTranslator->setParamsFromConfig($this->channel, ['openingTimeS' => 0, 'closingTimeS' => 0]);
        $this->assertEquals(0, $this->channel->getParam1());
        $this->assertEquals(0, $this->channel->getParam3());
    }

    public function testBoth0IfFirst0() {
        EntityUtils::setField($this->channel, 'flags', ChannelFunctionBitsFlags::AUTO_CALIBRATION_AVAILABLE);
        $this->configTranslator->setParamsFromConfig($this->channel, ['openingTimeS' => 0, 'closingTimeS' => 1]);
        $this->assertEquals(0, $this->channel->getParam1());
        $this->assertEquals(0, $this->channel->getParam3());
        $this->configTranslator->setParamsFromConfig($this->channel, ['openingTimeS' => 1, 'closingTimeS' => 0]);
        $this->assertEquals(10, $this->channel->getParam1());
        $this->assertEquals(0, $this->channel->getParam3());
    }

    public function testGettingConfigWithFlags() {
        EntityUtils::setField(
            $this->channel,
            'flags',
            ChannelFunctionBitsFlags::AUTO_CALIBRATION_AVAILABLE | ChannelFunctionBitsFlags::RECALIBRATE_ACTION_AVAILABLE
        );
        $this->channel->setParam1(123);
        $this->channel->setParam3(234);
        $config = $this->configTranslator->getConfigFromParams($this->channel);
        $expected = [
            'openingTimeS' => 12.3,
            'closingTimeS' => 23.4,
            'bottomPosition' => 0,
            'timeSettingAvailable' => true,
            'recalibrateAvailable' => true,
            'autoCalibrationAvailable' => true,
        ];
        $this->assertEquals($expected, $config);
    }
}

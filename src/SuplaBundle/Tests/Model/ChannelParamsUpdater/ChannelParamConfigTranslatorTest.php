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

namespace SuplaBundle\Tests\Model\ChannelParamsUpdater;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ElectricityMeterSupportBits;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamConfigTranslator;
use SuplaBundle\Model\ChannelStateGetter\ElectricityMeterChannelState;

class ChannelParamConfigTranslatorTest extends TestCase {
    /** @dataProvider paramsConfigsExamples */
    public function testGettingConfigFromParams(ChannelFunction $channelFunction, array $params, array $expectedConfig) {
        $channel = new IODeviceChannel();
        $channel->setFunction($channelFunction);
        $channel->setParam1($params[0] ?? 0);
        $channel->setParam2($params[1] ?? 0);
        $channel->setParam3($params[2] ?? 0);
        $channel->setTextParam1($params[3] ?? null);
        $channel->setTextParam2($params[4] ?? null);
        $channel->setTextParam3($params[5] ?? null);
        $configTranslator = new ChannelParamConfigTranslator();
        $config = $configTranslator->getConfigFromParams($channel);
        $this->assertEquals($expectedConfig, $config);
    }

    public function paramsConfigsExamples() {
        return [
            [ChannelFunction::NONE(), [], []],
        ];
    }
}

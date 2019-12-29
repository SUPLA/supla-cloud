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
use SuplaBundle\Enums\ChannelFunctionBitsActionTrigger;
use SuplaBundle\Model\ChannelParamsTranslator\ActionTriggerParamsTranslator;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;

class ActionTriggerParamsTranslatorTest extends TestCase {
    /** @var ChannelParamConfigTranslator */
    private $configTranslator;

    /** @before */
    public function createTranslator() {
        $this->configTranslator = new ActionTriggerParamsTranslator();
    }

    public function testGettingSupportedBehaviors() {
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'funcList', ChannelFunctionBitsActionTrigger::HOLD | ChannelFunctionBitsActionTrigger::PRESS_3X);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('supportedBehaviors', $config);
        $this->assertEquals(['HOLD', 'PRESS_3X'], $config['supportedBehaviors']);
    }

    public function testCloudCannotChangeSupportedBehaviors() {
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'funcList', ChannelFunctionBitsActionTrigger::HOLD | ChannelFunctionBitsActionTrigger::PRESS_3X);
        $this->configTranslator->setParamsFromConfig($channel, ['supportedBehaviors' => []]);
        $config = $this->configTranslator->getConfigFromParams($channel);
        $this->assertArrayHasKey('supportedBehaviors', $config);
        $this->assertEquals(['HOLD', 'PRESS_3X'], $config['supportedBehaviors']);
    }
}

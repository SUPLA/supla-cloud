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

namespace SuplaBundle\Tests\Integration\Model\ChannelActionExecutor;

use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class SetRgbwParametersChannelActionExecutorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var User */
    private $user;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $this->user = $user;
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::RGBLIGHTING],
        ]);
        $this->channelActionExecutor = $this->container->get(ChannelActionExecutor::class);
        $this->simulateAuthentication($this->user);
    }

    public function testUpdateColorWithHexValue() {
        $this->channelActionExecutor->executeAction(
            $this->device->getChannels()[0],
            ChannelFunctionAction::SET_RGBW_PARAMETERS(),
            ['color' => '0xFF0000', 'color_brightness' => 55]
        );
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-RGBW-VALUE:1,1,1,16711680,55,0', $setCommand);
    }

    public function testUpdateColorWithHueValue() {
        $this->channelActionExecutor->executeAction(
            $this->device->getChannels()[0],
            ChannelFunctionAction::SET_RGBW_PARAMETERS(),
            ['hue' => 0, 'color_brightness' => 55]
        );
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-RGBW-VALUE:1,1,1,16711680,55,0', $setCommand);
    }

    public function testUpdateColorWithIntValue() {
        $this->channelActionExecutor->executeAction(
            $this->device->getChannels()[0],
            ChannelFunctionAction::SET_RGBW_PARAMETERS(),
            ['color' => '16711680', 'color_brightness' => 55]
        );
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-RGBW-VALUE:1,1,1,16711680,55,0', $setCommand);
    }
}
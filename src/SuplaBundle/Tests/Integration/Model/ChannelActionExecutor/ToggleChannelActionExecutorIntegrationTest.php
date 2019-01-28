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
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

class ToggleChannelActionExecutorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;

    /** @before */
    public function createDeviceForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::POWERSWITCH],
            [ChannelType::RELAY, ChannelFunction::DIMMER],
            [ChannelType::RELAY, ChannelFunction::RGBLIGHTING],
        ]);
        $this->channelActionExecutor = $this->container->get(ChannelActionExecutor::class);
        SuplaServerMock::$executedCommands = [];
    }

    public function testTogglePowerSwitchOnOff() {
        SuplaServerMock::mockTheNextResponse("VALUE:1\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::TOGGLE());
        $this->assertCount(2, SuplaServerMock::$executedCommands);
        $setCommand = SuplaServerMock::$executedCommands[1];
        $this->assertEquals('SET-CHAR-VALUE:1,1,1,0', $setCommand);
    }

    public function testTogglePowerSwitchOffOn() {
        SuplaServerMock::mockTheNextResponse("VALUE:0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::TOGGLE());
        $this->assertCount(2, SuplaServerMock::$executedCommands);
        $setCommand = SuplaServerMock::$executedCommands[1];
        $this->assertEquals('SET-CHAR-VALUE:1,1,1,1', $setCommand);
    }

    public function testToggleDimmerOnOff() {
        SuplaServerMock::mockTheNextResponse("VALUE:0,0,10\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[1], ChannelFunctionAction::TOGGLE());
        $this->assertCount(2, SuplaServerMock::$executedCommands);
        $setCommand = SuplaServerMock::$executedCommands[1];
        $this->assertEquals('SET-CHAR-VALUE:1,1,2,0', $setCommand);
    }

    public function testToggleDimmerOffOn() {
        SuplaServerMock::mockTheNextResponse("VALUE:0,0,0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[1], ChannelFunctionAction::TOGGLE());
        $this->assertCount(2, SuplaServerMock::$executedCommands);
        $setCommand = SuplaServerMock::$executedCommands[1];
        $this->assertEquals('SET-CHAR-VALUE:1,1,2,1', $setCommand);
    }

    public function testToggleRgbOnOff() {
        SuplaServerMock::mockTheNextResponse("VALUE:0,10,0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[2], ChannelFunctionAction::TOGGLE());
        $this->assertCount(2, SuplaServerMock::$executedCommands);
        $setCommand = SuplaServerMock::$executedCommands[1];
        $this->assertEquals('SET-CHAR-VALUE:1,1,3,0', $setCommand);
    }

    public function testToggleRgbOfOn() {
        SuplaServerMock::mockTheNextResponse("VALUE:0,10,0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[2], ChannelFunctionAction::TOGGLE());
        $this->assertCount(2, SuplaServerMock::$executedCommands);
        $setCommand = SuplaServerMock::$executedCommands[1];
        $this->assertEquals('SET-CHAR-VALUE:1,1,3,1', $setCommand);
    }
}

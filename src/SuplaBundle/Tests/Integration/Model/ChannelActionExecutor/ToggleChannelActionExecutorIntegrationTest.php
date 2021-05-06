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

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ToggleChannelActionExecutorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var IODeviceChannelGroup */
    private $channelGroup;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::POWERSWITCH],
            [ChannelType::RELAY, ChannelFunction::DIMMER],
            [ChannelType::RELAY, ChannelFunction::RGBLIGHTING],
            [ChannelType::RELAY, ChannelFunction::POWERSWITCH],
        ]);
        $this->channelActionExecutor = $this->container->get(ChannelActionExecutor::class);
        $this->channelGroup = new IODeviceChannelGroup($user, $location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[3],
        ]);
        $this->getEntityManager()->persist($this->channelGroup);
        $this->getEntityManager()->flush();
    }

    public function testTogglePowerSwitchOnOff() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE', "VALUE:1\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::TOGGLE());
        $this->assertCount(3, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,1,0', $setCommand);
    }

    public function testTogglePowerSwitchOffOn() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE', "VALUE:0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::TOGGLE());
        $this->assertCount(3, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,1,1', $setCommand);
    }

    public function testToggleDimmerOnOff() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,0,10\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[1], ChannelFunctionAction::TOGGLE());
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,2,0', $setCommand);
    }

    public function testToggleDimmerOffOn() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,0,0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[1], ChannelFunctionAction::TOGGLE());
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,2,1', $setCommand);
    }

    public function testToggleRgbOnOff() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,10,0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[2], ChannelFunctionAction::TOGGLE());
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,3,0', $setCommand);
    }

    public function testToggleRgbOffOn() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,0,0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[2], ChannelFunctionAction::TOGGLE());
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,3,1', $setCommand);
    }

    public function testToggleOnChannelGroup() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE:1,1,1', "VALUE:0\n");
        SuplaServerMock::mockResponse('GET-CHAR-VALUE:1,1,4', "VALUE:1\n");
        $this->channelActionExecutor->executeAction($this->channelGroup, ChannelFunctionAction::TOGGLE());
        $this->assertContains('SET-CHAR-VALUE:1,1,1,1', SuplaServerMock::$executedCommands);
        $this->assertContains('SET-CHAR-VALUE:1,1,4,0', SuplaServerMock::$executedCommands);
    }
}

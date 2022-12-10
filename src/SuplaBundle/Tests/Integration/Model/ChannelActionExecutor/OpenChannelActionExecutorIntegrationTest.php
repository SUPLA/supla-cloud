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
class OpenChannelActionExecutorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannelGroup */
    private $channelGroupGarageDoor;
    /** @var IODeviceChannelGroup */
    private $channelGroupDoor;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGARAGEDOOR],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGARAGEDOOR],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::VALVEOPENCLOSE, ChannelFunction::VALVEOPENCLOSE],
        ]);
        $this->channelActionExecutor = self::$container->get(ChannelActionExecutor::class);
        $this->channelGroupGarageDoor = new IODeviceChannelGroup($user, $location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[2],
        ]);
        $this->channelGroupDoor = new IODeviceChannelGroup($user, $location, [
            $this->device->getChannels()[1],
            $this->device->getChannels()[3],
        ]);
        $this->getEntityManager()->persist($this->channelGroupGarageDoor);
        $this->getEntityManager()->persist($this->channelGroupDoor);
        $this->getEntityManager()->flush();
    }

    public function testOpeningGarageDoor() {
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::OPEN());
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('ACTION-OPEN:1,1,1', $setCommand);
    }

    public function testOpeningGarageDoorChannelGroup() {
        $this->channelActionExecutor->executeAction($this->channelGroupGarageDoor, ChannelFunctionAction::OPEN());
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('ACTION-CG-OPEN:1,1', $setCommand);
    }

    public function testOpeningDoor() {
        $this->channelActionExecutor->executeAction($this->device->getChannels()[1], ChannelFunctionAction::OPEN());
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,2,1', $setCommand);
    }

    public function testOpeningValve() {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', "VALUE:1,0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[4], ChannelFunctionAction::OPEN());
        $this->assertCount(2, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,5,1', $setCommand);
    }

    public function testCanOpenDoorChannelGroup() {
        $this->channelActionExecutor->executeAction($this->channelGroupDoor, ChannelFunctionAction::OPEN());
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CG-CHAR-VALUE:1,2,1', $setCommand);
    }
}

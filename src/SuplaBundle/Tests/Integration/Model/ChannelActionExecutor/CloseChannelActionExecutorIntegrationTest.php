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
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Model\ChannelParamsUpdater\IODeviceChannelWithParams;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class CloseChannelActionExecutorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var IODeviceChannelGroup */
    private $channelGroup;
    /** @var ChannelParamsUpdater */
    private $updater;
    /** @var User */
    private $user;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $this->user = $user;
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGARAGEDOOR],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGARAGEDOOR],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGARAGEDOOR],
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GARAGEDOOR],
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GARAGEDOOR],
        ]);
        $this->channelActionExecutor = $this->container->get(ChannelActionExecutor::class);
        $this->channelGroup = new IODeviceChannelGroup($user, $location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[1],
        ]);
        $this->updater = $this->container->get(ChannelParamsUpdater::class);
        $this->simulateAuthentication($this->user);
        $this->pairSensor($this->device->getChannels()[0], $this->device->getChannels()[3]);
        $this->pairSensor($this->device->getChannels()[1], $this->device->getChannels()[4]);
        $this->getEntityManager()->persist($this->channelGroup);
        $this->getEntityManager()->flush();
    }

    private function pairSensor(IODeviceChannel $gate, IODeviceChannel $sensor = null) {
        $this->updater->updateChannelParams($gate, new IODeviceChannelWithParams(0, $sensor ? $sensor->getId() : 0));
    }

    public function testCloseGateIfOpened() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE', "VALUE:0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::CLOSE());
        $this->assertCount(3, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,1,1', $setCommand);
    }

    public function testDoNotCloseGateIfClosed() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE', "VALUE:1\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::CLOSE());
        $this->assertCount(2, SuplaServerMock::$executedCommands);
    }

    public function testDoNotCloseIfNoSensor() {
        $this->channelActionExecutor->executeAction($this->device->getChannels()[2], ChannelFunctionAction::CLOSE());
        $this->assertCount(1, SuplaServerMock::$executedCommands);
    }

    public function testOpenGateIfClosed() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE', "VALUE:1\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::OPEN());
        $this->assertCount(3, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('SET-CHAR-VALUE:1,1,1,1', $setCommand);
    }

    public function testDoNotOpenGateIfOpened() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE', "VALUE:0\n");
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::OPEN());
        $this->assertCount(2, SuplaServerMock::$executedCommands);
    }

    public function testDoNotOpenIfNoSensor() {
        $this->channelActionExecutor->executeAction($this->device->getChannels()[2], ChannelFunctionAction::CLOSE());
        $this->assertCount(1, SuplaServerMock::$executedCommands);
    }

    public function testCloseOnChannelGroupWhenOneGateOpened() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE:1,1,4', "VALUE:0\n");
        SuplaServerMock::mockResponse('GET-CHAR-VALUE:1,1,5', "VALUE:1\n");
        $this->channelActionExecutor->executeAction($this->channelGroup, ChannelFunctionAction::CLOSE());
        $this->assertCount(5, SuplaServerMock::$executedCommands);
        $this->assertContains('SET-CHAR-VALUE:1,1,1,1', SuplaServerMock::$executedCommands);
        $this->assertNotContains('SET-CHAR-VALUE:1,1,2,1', SuplaServerMock::$executedCommands);
    }

    public function testOpenOnChannelGroupWhenBothClosed() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE:1,1,4', "VALUE:1\n");
        SuplaServerMock::mockResponse('GET-CHAR-VALUE:1,1,5', "VALUE:1\n");
        $this->channelActionExecutor->executeAction($this->channelGroup, ChannelFunctionAction::OPEN());
        $this->assertCount(6, SuplaServerMock::$executedCommands);
        $this->assertContains('SET-CHAR-VALUE:1,1,1,1', SuplaServerMock::$executedCommands);
        $this->assertContains('SET-CHAR-VALUE:1,1,2,1', SuplaServerMock::$executedCommands);
    }

    public function testOpenOnChannelGroupWhenBothOpened() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE:1,1,4', "VALUE:0\n");
        SuplaServerMock::mockResponse('GET-CHAR-VALUE:1,1,5', "VALUE:0\n");
        $this->channelActionExecutor->executeAction($this->channelGroup, ChannelFunctionAction::OPEN());
        $this->assertCount(4, SuplaServerMock::$executedCommands);
    }
}

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

use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @small */
class CopyActionExecutorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var User */
    private $user;
    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannelGroup */
    private $channelGroup;

    public function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::POWERSWITCH],
        ]);
        $this->channelActionExecutor = self::$container->get(ChannelActionExecutor::class);
        $this->channelGroup = new IODeviceChannelGroup($this->user, $location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[1],
        ]);
        $this->getEntityManager()->persist($this->channelGroup);
        $this->getEntityManager()->flush();
    }

    public function testCopyingFromChannelToChannel() {
        $subject = $this->device->getChannels()[0];
        $this->channelActionExecutor->executeAction($subject, ChannelFunctionAction::COPY(), ['sourceChannelId' => 2]);
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('ACTION-COPY:1,1,1,1,2', $setCommand);
    }

    public function testCopyingFromChannelToChannelGroup() {
        $this->channelActionExecutor->executeAction($this->channelGroup, ChannelFunctionAction::COPY(), ['sourceChannelId' => 2]);
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $setCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('ACTION-CG-COPY:1,1,1,2', $setCommand);
    }

    public function testCopyingFromChannelToChannelWithOtherFunction() {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('the same function');
        $subject = $this->device->getChannels()[0];
        $this->channelActionExecutor->executeAction($subject, ChannelFunctionAction::COPY(), ['sourceChannelId' => 3]);
    }

    public function testCopyingFromNotExistingChannel() {
        $this->expectException(NotFoundHttpException::class);
        $subject = $this->device->getChannels()[0];
        $this->channelActionExecutor->executeAction($subject, ChannelFunctionAction::COPY(), ['sourceChannelId' => 4]);
    }
}

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

namespace SuplaBundle\Tests\Supla;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Supla\SuplaServerMockCommandsCollector;

/**
 * We are testing the mocked implementation here in order to be sure in behaves correctly in integration tests.
 */
class SuplaServerMockTest extends TestCase {
    /** @var SuplaServerMock */
    private $server;

    /** @var User */
    private $user;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannel */
    private $device;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannel */
    private $channel;

    /** @before */
    public function init() {
        SuplaServerMock::$executedCommands = [];
        $this->server = new SuplaServerMock(new SuplaServerMockCommandsCollector(), $this->createMock(LoggerInterface::class));
        $this->user = $this->createMock(User::class);
        $this->user->method('getId')->willReturn(111);
        $this->device = $this->createMock(IODevice::class);
        $this->device->method('getId')->willReturn(222);
        $this->device->method('getUser')->willReturn($this->user);
        $this->device->method('getEnabled')->willReturn(true);
        $this->channel = $this->createMock(IODeviceChannel::class);
        $this->channel->method('getId')->willReturn(333);
        $this->channel->method('getUser')->willReturn($this->user);
        $this->channel->method('getIoDevice')->willReturn($this->device);
    }

    public function testCheckingDeviceConnected() {
        $connected = $this->server->isDeviceConnected($this->device);
        $this->assertTrue($connected);
        $this->assertContains('IS-IODEV-CONNECTED:111,222', SuplaServerMock::$executedCommands);
    }

    public function testCheckingChannelConnected() {
        $connected = $this->server->isChannelConnected($this->channel);
        $this->assertTrue($connected);
        $this->assertContains('IS-CHANNEL-CONNECTED:111,222,333', SuplaServerMock::$executedCommands);
    }

    public function testReconnecting() {
        $this->server->reconnect($this->user);
        $this->assertNotContains('USER-RECONNECT:111', SuplaServerMock::$executedCommands);
        $this->server->flushPostponedCommands();
        $this->assertContains('USER-RECONNECT:111', SuplaServerMock::$executedCommands);
    }

    public function testGetCharValue() {
        $this->server->getCharValue($this->channel);
        $this->assertContains('GET-CHAR-VALUE:111,222,333', SuplaServerMock::$executedCommands);
    }

    public function testCommandsWithinTransaction() {
        $this->server->postponeUserAction('ON-WHATEVER', [], $this->user);
        $this->assertEmpty(SuplaServerMock::$executedCommands);
        $this->server->flushPostponedCommands();
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $this->assertContains('USER-ON-WHATEVER:111', SuplaServerMock::$executedCommands);
    }
}

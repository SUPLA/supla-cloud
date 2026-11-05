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

namespace App\Tests\Supla;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Supla\SuplaServerMock;
use App\Supla\SuplaServerMockCommandsCollector;
use App\Supla\CalcfgActionResult;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * We are testing the mocked implementation here in order to be sure in behaves correctly in integration tests.
 */
class SuplaServerMockTest extends TestCase {
    /** @var SuplaServerMock */
    private $server;

    /** @var User */
    private $user;
    /** @var \App\Entity\Main\IODeviceChannel */
    private $device;
    /** @var \App\Entity\Main\IODeviceChannel */
    private $channel;

    /** @before */
    public function init() {
        SuplaServerMock::$executedCommands = [];
        $this->server = new SuplaServerMock(
            new SuplaServerMockCommandsCollector(),
            $this->createMock(LoggerInterface::class),
            $this->createMock(EntityManagerInterface::class),
            'test',
        );
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

    public function testDevelopmentSleeperSimulatesOfflineQueueWithoutCompletingPassword(): void {
        $device = $this->createMock(IODevice::class);
        $device->method('getId')->willReturn(222);
        $device->method('getUser')->willReturn($this->user);
        $device->method('getName')->willReturn('CALCFG SYNC SLEEPER');
        $device->method('getEnabled')->willReturn(true);
        $device->method('isSleepModeEnabled')->willReturn(true);
        $device->method('isSyncDoneSupported')->willReturn(true);
        $device->method('getFlags')->willReturn([
            'enterConfigurationModeAvailable' => true, 'remoteRestartAvailable' => true,
            'automaticFirmwareUpdatesSupported' => true, 'factoryResetSupported' => true,
            'setCfgModePasswordSupported' => true, 'identifyDeviceAvailable' => true,
        ]);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturn($device);
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');
        $server = new SuplaServerMock(new SuplaServerMockCommandsCollector(), $this->createMock(LoggerInterface::class), $em, 'dev');

        $this->assertFalse($server->isDeviceConnected($device));
        $this->assertSame(CalcfgActionResult::QUEUED, $server->deviceAction($device, 'ENTER-CONFIGURATION-MODE')->status);
        $result = $server->deviceAction($device, 'SET-CFG-MODE-PASSWORD', [base64_encode('new-password')]);
        $this->assertContains($result->status, [CalcfgActionResult::QUEUED, CalcfgActionResult::WAITING_FOR_RESULT]);
        $this->assertSame(222, $result->id);
        $this->assertGreaterThan(0, $result->queuedAt);
        SuplaServerMock::mockResponse('ENTER-CONFIGURATION-MODE', "ERROR:222\n");
        $this->assertFalse($server->deviceAction($device, 'ENTER-CONFIGURATION-MODE')->isAccepted());
    }

    public function testDevelopmentSleeperQueuesEligibleChannelActions(): void {
        $device = $this->createMock(IODevice::class);
        $device->method('getId')->willReturn(222);
        $device->method('getName')->willReturn('CALCFG SYNC SLEEPER');
        $device->method('getUser')->willReturn($this->user);
        $device->method('getEnabled')->willReturn(true);
        $device->method('isSleepModeEnabled')->willReturn(true);
        $device->method('isSyncDoneSupported')->willReturn(true);
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getId')->willReturn(333);
        $channel->method('getUser')->willReturn($this->user);
        $channel->method('getIoDevice')->willReturn($device);
        $channel->method('getFunction')->willReturn(ChannelFunction::SEPTIC_TANK());
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('find')->willReturnCallback(fn($class) => $class === IODevice::class ? $device : $channel);
        $server = new SuplaServerMock(new SuplaServerMockCommandsCollector(), $this->createMock(LoggerInterface::class), $em, 'dev');

        $this->assertFalse($server->isChannelConnected($channel));
        $result = $server->channelAction($channel, 'MUTE-ALARM-SOUND');
        $this->assertSame(CalcfgActionResult::QUEUED, $result->status);
        $this->assertSame(333, $result->id);
        $this->assertFalse($server->channelAction($channel, 'RESTART-SUBDEVICE')->isAccepted());
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

    public function testDeviceActionParsesQueuedAcceptanceAndPreservesTimestamp() {
        SuplaServerMock::mockResponse('ENTER-CONFIGURATION-MODE', "QUEUED:222,1793577600\n");

        $result = $this->server->deviceAction($this->device, 'ENTER-CONFIGURATION-MODE');

        $this->assertInstanceOf(CalcfgActionResult::class, $result);
        $this->assertSame(CalcfgActionResult::QUEUED, $result->status);
        $this->assertSame(222, $result->id);
        $this->assertSame(1793577600, $result->queuedAt);
        $this->assertTrue($result->isAccepted());
    }

    public function testDeviceActionParsesEveryAcceptanceStatus() {
        foreach (
            [
            ["OK:222\n", CalcfgActionResult::ACCEPTED, null],
            ["QUEUED:222,1793577600\n", CalcfgActionResult::QUEUED, 1793577600],
            ["WAITING_FOR_RESULT:222,1793577600\n", CalcfgActionResult::WAITING_FOR_RESULT, 1793577600],
            ] as [$response, $status, $queuedAt]
        ) {
            SuplaServerMock::mockResponse('ENTER-CONFIGURATION-MODE', $response);
            $result = $this->server->deviceAction($this->device, 'ENTER-CONFIGURATION-MODE');
            $this->assertInstanceOf(CalcfgActionResult::class, $result);
            $this->assertSame($status, $result->status);
            $this->assertTrue($result->isAccepted());
            $this->assertSame(222, $result->id);
            $this->assertSame($queuedAt, $result->queuedAt);
        }
    }

    public function testChannelActionParsesEveryAcceptanceStatus() {
        foreach (
            [
            ["OK:333\n", CalcfgActionResult::ACCEPTED, null],
            ["QUEUED:333,1793577600\n", CalcfgActionResult::QUEUED, 1793577600],
            ["WAITING_FOR_RESULT:333,1793577600\n", CalcfgActionResult::WAITING_FOR_RESULT, 1793577600],
            ] as [$response, $status, $queuedAt]
        ) {
            SuplaServerMock::mockResponse('MUTE-ALARM-SOUND', $response);
            $result = $this->server->channelAction($this->channel, 'MUTE-ALARM-SOUND');
            $this->assertInstanceOf(CalcfgActionResult::class, $result);
            $this->assertSame($status, $result->status);
            $this->assertTrue($result->isAccepted());
            $this->assertSame(333, $result->id);
            $this->assertSame($queuedAt, $result->queuedAt);
        }
    }

    public function testIdentifySubdeviceReturnsChannelId(): void {
        $result = $this->server->channelAction($this->channel, 'IDENTIFY-SUBDEVICE');

        $this->assertInstanceOf(CalcfgActionResult::class, $result);
        $this->assertSame(333, $result->id);
        $this->assertContains('IDENTIFY-SUBDEVICE:111,222,333', SuplaServerMock::$executedCommands);
    }

    public function testDeviceActionAcceptsOldCoreNumericOkReply() {
        SuplaServerMock::mockResponse('ENTER-CONFIGURATION-MODE', "OK:222\n");

        $result = $this->server->deviceAction($this->device, 'ENTER-CONFIGURATION-MODE');

        $this->assertInstanceOf(CalcfgActionResult::class, $result);
        $this->assertSame(CalcfgActionResult::ACCEPTED, $result->status);
        $this->assertNull($result->queuedAt);
        $this->assertTrue($result->isAccepted());
    }

    public function testChannelActionParsesWaitingForResult() {
        SuplaServerMock::mockResponse('MUTE-ALARM-SOUND', "WAITING_FOR_RESULT:333,1793577500\r\n");

        $result = $this->server->channelAction($this->channel, 'MUTE-ALARM-SOUND');

        $this->assertInstanceOf(CalcfgActionResult::class, $result);
        $this->assertSame(CalcfgActionResult::WAITING_FOR_RESULT, $result->status);
        $this->assertSame(333, $result->id);
        $this->assertSame(1793577500, $result->queuedAt);
        $this->assertTrue($result->isAccepted());
    }

    public function testDeviceActionRejectsMalformedAcceptanceReplies() {
        $responses = [
            false,
            '',
            "ERROR:222\n",
            "QUEUED:222,nope\n",
            'OK:HURRA',
            'OK:222',
            "OK:222\ngarbage",
            "QUEUED:222,999999999999999999999999\n",
            "OK:0\n",
        ];
        foreach ($responses as $response) {
            SuplaServerMock::mockResponse('ENTER-CONFIGURATION-MODE', $response);
            $result = $this->server->deviceAction($this->device, 'ENTER-CONFIGURATION-MODE');
            $this->assertFalse($result->isAccepted());
            $this->assertSame(CalcfgActionResult::REJECTED, $result->status);
            $this->assertNull($result->id);
            $this->assertNull($result->queuedAt);
        }
    }

    public function testChannelActionReturnsRejectedResultForErrorReply() {
        SuplaServerMock::mockResponse('MUTE-ALARM-SOUND', "UNKNOWN:333\n");

        $result = $this->server->channelAction($this->channel, 'MUTE-ALARM-SOUND');

        $this->assertSame(CalcfgActionResult::REJECTED, $result->status);
        $this->assertFalse($result->isAccepted());
        $this->assertNull($result->id);
        $this->assertNull($result->queuedAt);
    }

    public function testDefaultPairingReplyIsNumericAndMutatesPairingState() {
        $device = $this->createMock(IODevice::class);
        $device->expects($this->once())->method('getId')->willReturn(222);
        $device->expects($this->once())->method('getUser')->willReturn($this->user);
        $this->device = $device;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('find')->with(IODevice::class, '222')->willReturn($device);
        $entityManager->expects($this->once())->method('persist')->with($device);
        $entityManager->expects($this->once())->method('flush');
        $server = new SuplaServerMock(new SuplaServerMockCommandsCollector(), $this->createMock(LoggerInterface::class), $entityManager, 'test');

        $result = $server->deviceAction($device, 'PAIR-SUBDEVICE');

        $this->assertInstanceOf(CalcfgActionResult::class, $result);
        $this->assertSame(222, $result->id);
        $this->assertContains('PAIR-SUBDEVICE:111,222', SuplaServerMock::$executedCommands);
    }

    public function testDeferredPasswordAndOtaRepliesDoNotMutateDeviceProperties() {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('find');
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');
        $server = new SuplaServerMock(new SuplaServerMockCommandsCollector(), $this->createMock(LoggerInterface::class), $entityManager, 'test');

        foreach (
            [
            ['OTA-CHECK-UPDATES', 'WAITING_FOR_RESULT:222,1793577600', CalcfgActionResult::WAITING_FOR_RESULT],
            ['OTA-PERFORM-UPDATE', 'QUEUED:222,1793577600', CalcfgActionResult::QUEUED],
            ['SET-CFG-MODE-PASSWORD', 'WAITING_FOR_RESULT:222,1793577600', CalcfgActionResult::WAITING_FOR_RESULT],
            ] as [$command, $reply, $status]
        ) {
            SuplaServerMock::mockResponse($command, "$reply\n");
            $result = $server->deviceAction($this->device, $command);
            $this->assertInstanceOf(CalcfgActionResult::class, $result);
            $this->assertSame($status, $result->status);
        }
    }

    public function testPasswordActionLogsRedactCommandAndResponseForAllReplyTypes() {
        $password = 'unique-secret-password';
        $encodedPassword = base64_encode($password);
        $loggedContexts = [];
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(4))
            ->method('debug')
            ->willReturnCallback(function ($message, $context) use (&$loggedContexts) {
                $loggedContexts[] = $context;
            });
        $collector = new SuplaServerMockCommandsCollector();
        $server = new SuplaServerMock($collector, $logger, $this->createMock(EntityManagerInterface::class), 'test');

        foreach (["OK:222\n", "WAITING_FOR_RESULT:222,1793577600\n", "ERROR:$encodedPassword\n"] as $response) {
            SuplaServerMock::mockResponse('SET-CFG-MODE-PASSWORD', $response);
            $server->deviceAction($this->device, 'SET-CFG-MODE-PASSWORD', [$encodedPassword]);
        }
        $server->deviceAction($this->device, 'ENTER-CONFIGURATION-MODE');

        $loggedData = json_encode($loggedContexts);
        $this->assertStringNotContainsString($password, $loggedData);
        $this->assertStringNotContainsString($encodedPassword, $loggedData);
        $this->assertStringNotContainsString($encodedPassword, json_encode($collector->getCommands()));
        $this->assertSame('SET-CFG-MODE-PASSWORD:111,222,[REDACTED]', $loggedContexts[0]['command']);
        $this->assertSame('[REDACTED]', $loggedContexts[0]['result']);
        $this->assertSame('ENTER-CONFIGURATION-MODE:111,222', $loggedContexts[3]['command']);
        $this->assertSame("OK:222\n", $loggedContexts[3]['result']);
    }
}

<?php

namespace App\Tests\Supla;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Supla\CalcfgActionResult;
use App\Supla\SuplaServerReal;
use App\Model\LocalSuplaCloud;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SuplaServerRealTest extends TestCase {
    public function testDeviceAndChannelActionsReadFragmentedAndConsecutiveReplies(): void {
        $this->assertActionsWithResponses(
            ["OK:222\n", "QUEUED:333,1793577600\n"],
            function (SuplaServerReal $server, IODevice $device, IODeviceChannel $channel): void {
                $deviceResult = $server->deviceAction($device, 'ENTER-CONFIGURATION-MODE');
                $channelResult = $server->channelAction($channel, 'MUTE-ALARM-SOUND');

                $this->assertInstanceOf(CalcfgActionResult::class, $deviceResult);
                $this->assertSame(222, $deviceResult->id);
                $this->assertInstanceOf(CalcfgActionResult::class, $channelResult);
                $this->assertSame(CalcfgActionResult::QUEUED, $channelResult->status);
                $this->assertSame(333, $channelResult->id);
            },
            true,
        );
    }

    public function testDeviceActionRejectsTruncatedAndLongReplies(): void {
        foreach (["OK:222", 'OK:222' . str_repeat('x', 5000) . "\n", str_repeat('x', 1048577) . "\n"] as $response) {
            $this->assertActionsWithResponses([$response], function (SuplaServerReal $server, IODevice $device): void {
                $this->assertFalse($server->deviceAction($device, 'ENTER-CONFIGURATION-MODE')->isAccepted());
            });
        }
    }

    public function testDeviceActionDisconnectsAfterIncompleteResponseBeforeChannelAction(): void {
        $this->assertActionsWithResponses([["OK:222"], ["OK:333\n"]], function (SuplaServerReal $server, IODevice $device, IODeviceChannel $channel): void {
            $this->assertFalse($server->deviceAction($device, 'ENTER-CONFIGURATION-MODE')->isAccepted());
            $result = $server->channelAction($channel, 'MUTE-ALARM-SOUND');
            $this->assertInstanceOf(CalcfgActionResult::class, $result);
            $this->assertSame(333, $result->id);
        });
    }

    public function testChannelActionDisconnectsAfterIncompleteResponseBeforeDeviceAction(): void {
        $this->assertActionsWithResponses([["OK:333"], ["OK:222\n"]], function (SuplaServerReal $server, IODevice $device, IODeviceChannel $channel): void {
            $this->assertFalse($server->channelAction($channel, 'MUTE-ALARM-SOUND')->isAccepted());
            $result = $server->deviceAction($device, 'ENTER-CONFIGURATION-MODE');
            $this->assertInstanceOf(CalcfgActionResult::class, $result);
            $this->assertSame(222, $result->id);
        });
    }

    public function testDeviceActionDisconnectsAfterResponseTimeout(): void {
        $this->assertActionsWithResponses([["__TIMEOUT__"], ["OK:333\n"]], function (
            SuplaServerReal $server,
            IODevice $device,
            IODeviceChannel $channel
        ): void {
            $this->assertFalse($server->deviceAction($device, 'ENTER-CONFIGURATION-MODE')->isAccepted());
            $result = $server->channelAction($channel, 'MUTE-ALARM-SOUND');
            $this->assertInstanceOf(CalcfgActionResult::class, $result);
            $this->assertSame(333, $result->id);
        });
    }

    public function testDeviceActionDisconnectsAfterSlowTrickleAndReconnectsForNextAction(): void {
        $this->assertActionsWithResponses([["__TRICKLE_CALCFG__"], ["OK:333\n"]], function (
            SuplaServerReal $server,
            IODevice $device,
            IODeviceChannel $channel
        ): void {
            $startedAt = microtime(true);
            $this->assertFalse($server->deviceAction($device, 'ENTER-CONFIGURATION-MODE')->isAccepted());
            $this->assertLessThan(5.8, microtime(true) - $startedAt, 'A slow trickle must not extend the CALCFG response deadline.');
            $result = $server->channelAction($channel, 'MUTE-ALARM-SOUND');
            $this->assertInstanceOf(CalcfgActionResult::class, $result);
            $this->assertSame(333, $result->id);
        });
    }

    public function testSuccessfulCalcfgActionRestoresTimeoutForFollowingCommand(): void {
        $this->assertActionsWithResponses([["__SLOW_CALCFG__", "__SLOW_COMMAND__"]], function (SuplaServerReal $server, IODevice $device): void {
            $result = $server->deviceAction($device, 'ENTER-CONFIGURATION-MODE');
            $this->assertInstanceOf(CalcfgActionResult::class, $result);
            $this->assertSame(222, $result->id);
            $this->assertSame("STATUS:READY\n", $server->executeCommand('GET-STATUS'));
        });
    }

    private function assertActionsWithResponses(array $responses, callable $assertActions, bool $fragment = false): void {
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped('Socket fixture requires pcntl.');
        }

        $socketPath = sys_get_temp_dir() . '/supla-server-' . bin2hex(random_bytes(8)) . '.sock';
        $listener = stream_socket_server('unix://' . $socketPath, $errorCode, $errorMessage);
        $this->assertNotFalse($listener, $errorMessage);
        $pid = pcntl_fork();
        $this->assertNotSame(-1, $pid, 'Unable to fork socket fixture.');
        if ($pid === 0) {
            $connections = isset($responses[0]) && is_array($responses[0]) ? $responses : [$responses];
            foreach ($connections as $connectionResponses) {
                $connection = stream_socket_accept($listener, 3);
                if ($connection === false) {
                    exit(1);
                }
                stream_set_timeout($connection, 3);
                fwrite($connection, "SUPLA SERVER CTRL\n");
                foreach ($connectionResponses as $response) {
                    if (fgets($connection) === false) {
                        exit(2);
                    }
                    if ($response === '__TIMEOUT__') {
                        fwrite($connection, 'OK:222');
                        sleep(6);
                    } elseif ($response === '__TRICKLE_CALCFG__') {
                        foreach (str_split("OK:222\n") as $byte) {
                            @fwrite($connection, $byte);
                            usleep(1100000);
                        }
                    } elseif ($response === '__SLOW_CALCFG__') {
                        sleep(4);
                        fwrite($connection, 'OK:');
                        usleep(200000);
                        fwrite($connection, "222\n");
                    } elseif ($response === '__SLOW_COMMAND__') {
                        sleep(6);
                        fwrite($connection, "STATUS:READY\n");
                    } elseif ($fragment && strlen($response) > 1) {
                        fwrite($connection, substr($response, 0, 3));
                        usleep(20000);
                        fwrite($connection, substr($response, 3));
                    } else {
                        fwrite($connection, $response);
                    }
                }
                fclose($connection);
            }
            fclose($listener);
            exit(0);
        }

        fclose($listener);
        try {
            $user = $this->createMock(User::class);
            $user->method('getId')->willReturn(111);
            $device = $this->createMock(IODevice::class);
            $device->method('getId')->willReturn(222);
            $device->method('getUser')->willReturn($user);
            $channel = $this->createMock(IODeviceChannel::class);
            $channel->method('getId')->willReturn(333);
            $channel->method('getUser')->willReturn($user);
            $channel->method('getIoDevice')->willReturn($device);
            $server = new SuplaServerReal($socketPath, new LocalSuplaCloud('http://supla.local'), $this->createMock(LoggerInterface::class));

            $assertActions($server, $device, $channel);
        } finally {
            pcntl_waitpid($pid, $status);
            @unlink($socketPath);
            $this->assertSame(0, pcntl_wexitstatus($status), 'Socket fixture did not complete successfully.');
        }
    }
}

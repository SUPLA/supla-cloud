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

namespace SuplaBundle\Supla;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\ChannelState;
use SuplaBundle\Entity\Main\ChannelValue;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ElectricityMeterSupportBits;
use SuplaBundle\Enums\HvacIpcActionMode;
use SuplaBundle\Enums\HvacIpcValueFlags;
use SuplaBundle\Enums\RollerShutterStateBits;
use SuplaBundle\Enums\SceneInitiatiorType;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Tests\AnyFieldSetter;

/**
 * SuplaServer implementation to be used during development.
 */
class SuplaServerMock extends SuplaServer {
    private const IS_ALIVE = true;

    public static $mockedResponses = [];

    public static $executedCommands = [];

    private Generator $faker;

    public function __construct(
        private SuplaServerMockCommandsCollector $commandsCollector,
        private LoggerInterface $logger,
        private EntityManagerInterface $em
    ) {
        parent::__construct('', new LocalSuplaCloud('http://supla.local'), $logger);
        $this->faker = Factory::create();
    }

    protected function connect() {
        return self::IS_ALIVE;
    }

    protected function disconnect() {
        return true;
    }

    protected function command($command) {
        $this->commandsCollector->addCommand($command);
        self::$executedCommands[] = $command;
        return $this->tryToHandleCommand($command);
    }

    private function tryToHandleCommand($cmd) {
        foreach (self::$mockedResponses as $command => &$responses) {
            if (preg_match("#$command#i", $cmd)) {
                $response = array_shift($responses);
                if (!$responses) {
                    unset(self::$mockedResponses[$command]);
                }
                return $response;
            }
        }
        $isTests = defined('APPLICATION_ENV') && in_array(APPLICATION_ENV, ['e2e', 'test']);
        if (preg_match('#^IS-(IODEV|CLIENT|CHANNEL)-CONNECTED:(\d+),(\d+),?(\d+)?$#', $cmd, $match)) {
            if ($match[1] === 'CHANNEL') {
                if ($this->faker->boolean($isTests ? 100 : 95)) {
                    return "CONNECTED:$match[3]\n";
                } elseif ($this->faker->boolean()) {
                    if ($this->faker->boolean(33)) {
                        return "CONNECTED_BUT_NOT_AVAILABLE:$match[3]\n";
                    } elseif ($this->faker->boolean(33)) {
                        return "FIRMWARE_UPDATE_ONGOING:$match[3]\n";
                    } else {
                        return "OFFLINE_REMOTE_WAKEUP_NOT_SUPPORTED:$match[3]\n";
                    }
                } else {
                    return "DISCONNECTED:$match[3]\n";
                }
            } elseif ($match[1] !== 'CLIENT' || $this->faker->boolean()) {
                return "CONNECTED:$match[3]\n";
            } else {
                return "DISCONNECTED:$match[3]\n";
            }
        } elseif (preg_match('#^GET-STATUS$#', $cmd, $match)) {
            return "OK\n";
        } elseif (preg_match('#^USER.+:(\d+).*$#', $cmd, $match)) {
            return "OK:$match[1]\n";
        } elseif (preg_match('#^SEND-PUSH:(\d+).*$#', $cmd, $match)) {
            return "OK:$match[1]\n";
        } elseif (preg_match('#^SET-(CG-)?(CHAR|RGBW|RAND-RGBW|DIGIGLASS)-VALUE:.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^ACTION-(CG-)?(.+?):.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^(RESTART-(SUB)?DEVICE|OTA-PERFORM-UPDATE):.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^OTA-CHECK-UPDATES:(\d+),(\d+)$#', $cmd, $match)) {
            $device = $this->em->find(IODevice::class, $match[2]);
            $properties = $device->getProperties();
            if (rand(0, 1) % 2) {
                $properties['otaUpdate'] = ['status' => 'NOT_AVAILABLE', 'timestamp' => time()];
            } else {
                $properties['otaUpdate'] = ['status' => 'AVAILABLE', 'timestamp' => time(), 'version' => '1.2.3', 'url' => '/v123'];
            }
            EntityUtils::setField($device, 'properties', json_encode($properties));
            $this->em->persist($device);
            $this->em->flush();
            return 'OK:HURRA';
        } elseif (preg_match('#^SET-CFG-MODE-PASSWORD:(\d+),(\d+).*$#', $cmd, $match)) {
            $device = $this->em->find(IODevice::class, $match[2]);
            $properties = $device->getProperties();
            if (rand(0, 1) % 2) {
                $properties['setCfgModePassword'] = ['status' => 'TRUE'];
            } else {
                $properties['setCfgModePassword'] = ['status' => 'FALSE'];
            }
            EntityUtils::setField($device, 'properties', json_encode($properties));
            $this->em->persist($device);
            $this->em->flush();
            return 'OK:HURRA';
        } elseif (preg_match('#^(PAIR-SUBDEVICE|DEVICE-SET-TIME|ENTER-CONFIGURATION-MODE):.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^IDENTIFY-(SUB)?DEVICE:.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^(RESET-COUNTERS|RECALIBRATE|TAKE-OCR-PHOTO|MUTE-ALARM-SOUND):(\d+),(\d+),(\d+)$#', $cmd, $match)) {
            return "OK:$match[4]\n";
        } elseif (preg_match('#^(EXECUTE|INTERRUPT|INTERRUPT-AND-EXECUTE)-SCENE:.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^GET-(CHAR)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . rand(0, 1);
        } elseif (preg_match('#^GET-(GPM)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            $channelId = $match[4];
            $value = $this->em->getRepository(ChannelValue::class)->findOneBy(['channel' => $channelId]);
            return 'VALUE:' . ($value ? current(unpack('d', $value->getValue())) : rand(0, 1000000) / rand(2, 5));
        } elseif (preg_match('#^GET-(VALVE)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . rand(0, 1) . ',' . rand(0, 7);
        } elseif (preg_match('#^GET-(CONTAINER)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . rand(0, 101) . ',' . rand(0, 16);
        } elseif (preg_match('#^GET-(RELAY)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            $flag = $isTests ? 0 : rand(0, 1);
            return 'VALUE:' . rand(0, 1) . ',' . $flag;
        } elseif (preg_match('#^GET-(DIGIGLASS)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . rand(0, (1 << 7) - 1);
        } elseif (preg_match('#^PN-GET-LIMIT:(\d+)#', $cmd, $match)) {
            return 'PN-LIMIT:100,' . rand(-10, 100);
        } elseif (preg_match('#^GET-RGBW-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            $values = [rand(0, 0xFFFFFF), rand(0, 100), rand(0, 100)];
            if (rand(0, 1)) {
                $values[1] = 0; // simulate RGB turn off
            }
            if (rand(0, 1)) {
                $values[2] = 0; // simulate DIMMER turn off
            }
            return 'VALUE:' . implode(',', $values);
        } elseif (preg_match('#^GET-TEMPERATURE-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . (rand(-500, 3000) / 100);
        } elseif (preg_match('#^GET-HUMIDITY-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . (rand(0, 1000) / 10);
        } elseif (preg_match('#^GET-DOUBLE-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . (rand(0, 1000000) / 100);
        } elseif (preg_match('#^GET-FACADE-BLIND-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            $tiltPercent = rand(0, 100);
            return sprintf('VALUE:%d,%d,%d', rand(0, 100), $tiltPercent, round($tiltPercent * 180 / 100));
        } elseif (preg_match('#^GET-ROLLERSHUTTER-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return sprintf('VALUE:%d,%d', rand(0, 100), rand(0, RollerShutterStateBits::CALIBRATION_IN_PROGRESS));
        } elseif (preg_match('#^GET-SCENE-SUMMARY:\d+,(\d+)#', $cmd, $match)) {
            $sceneId = $match[1];
            $values = [$sceneId, 0, 0, 0, 0, 0];
            if ($this->faker->boolean(20)) {
                // SUMMARY:%SceneId,%InitiatorType,%InitiatorId,%InitiatorName_Base64,%MillisecondsFromStart,%MillisecondsToEnd
                $values = [
                    $sceneId,
                    rand(0, SceneInitiatiorType::SCENE),
                    rand(0, 10),
                    base64_encode('Unicorn initiator'),
                    rand(0, 1000),
                    rand(0, 10000),
                ];
            }
            return vsprintf('SUMMARY:%d,%d,%d,%d,%d,%d', $values);
        } elseif (preg_match('#^GET-IC-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) { // IMPULSE_COUNTER
            $counter = $this->faker->randomNumber(4);
            $impulsesPerUnit = $this->faker->randomNumber(3) + 1;
            return sprintf(
                'VALUE:%d,%d,%d,%d,%d,%s,%s',
                $this->faker->randomNumber(7), // TotalCost * 100
                $this->faker->randomNumber(7), // PricePerUnit * 10000
                $impulsesPerUnit, // ImpulsesPerUnit
                $counter, // Counter
                round($counter * 1000 / $impulsesPerUnit), // CalculatedValue * 1000
                $this->faker->boolean() ? $this->faker->currencyCode : '', // currency
                $this->faker->boolean() ? base64_encode($this->faker->randomElement(['m³', 'wahnięć', 'l'])) : '' // base-64 unit name
            );
        } elseif (preg_match('#^GET-HVAC-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) { // HVAC
            $mode = $this->faker->randomElement(
                [HvacIpcActionMode::OFF, HvacIpcActionMode::HEAT, HvacIpcActionMode::COOL, HvacIpcActionMode::HEAT_COOL]
            );
            $flags = 0;
            if ($mode === HvacIpcActionMode::HEAT || $mode === HvacIpcActionMode::HEAT_COOL) {
                $flags |= HvacIpcValueFlags::TEMPERATURE_HEAT_SET;
            }
            if ($mode === HvacIpcActionMode::COOL || $mode === HvacIpcActionMode::HEAT_COOL) {
                $flags |= HvacIpcValueFlags::TEMPERATURE_COOL_SET;
            }
            if ($this->faker->boolean()) {
                if ($mode === HvacIpcActionMode::HEAT) {
                    $flags |= HvacIpcValueFlags::HEATING;
                } elseif ($mode === HvacIpcActionMode::COOL) {
                    $flags |= HvacIpcValueFlags::COOLING;
                } elseif ($mode === HvacIpcActionMode::HEAT_COOL) {
                    $flags |= $this->faker->boolean() ? HvacIpcValueFlags::COOLING : HvacIpcValueFlags::HEATING;
                }
            }
            if ($this->faker->boolean()) {
                $flags |= HvacIpcValueFlags::WEEKLY_SCHEDULE;
            }
            if ($this->faker->boolean(30)) {
                $flags |= HvacIpcValueFlags::COUNTDOWN_TIMER;
            }
            if ($this->faker->boolean(10)) {
                $flags |= HvacIpcValueFlags::THERMOMETER_ERROR;
            }
            if ($this->faker->boolean(10)) {
                $flags |= HvacIpcValueFlags::CLOCK_ERROR;
            }
            if ($this->faker->boolean(10)) {
                $flags |= HvacIpcValueFlags::FORCED_OFF_BY_SENSOR;
            }
            if ($this->faker->boolean(10)) {
                $flags |= HvacIpcValueFlags::WEEKLY_SCHEDULE_TEMPORAL_OVERRIDE;
            }
            if ($this->faker->boolean(10)) {
                $flags |= HvacIpcValueFlags::BATTERY_COVER_OPEN;
            }
            if ($this->faker->boolean(10)) {
                $flags |= HvacIpcValueFlags::CALIBRATION_ERROR;
            }
            return sprintf(
                'VALUE:%d,%d,%d,%d,%d,%d,%d',
                $this->faker->numberBetween(0, 100),
                $mode,
                $this->faker->numberBetween(1800, 2200),
                $this->faker->numberBetween(2300, 2600),
                $flags,
                $this->faker->numberBetween(1600, 2400),
                $this->faker->numberBetween(-1, 50),
            );
        } elseif (preg_match('#^GET-EM-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) { // ELECTRICITY_METER
            $fullSupportMask = array_reduce(ElectricityMeterSupportBits::toArray(), function (int $acc, int $bit) {
                return $acc | $bit;
            }, 0);
            return sprintf(
                'VALUE:' . str_repeat('%d,', 37) . '%s',
                $fullSupportMask,
                rand(4950, 5500), // Freq * 100
                rand(22000, 24000), // VoltagePhase1 * 100
                rand(22000, 24000), // VoltagePhase2 * 100
                rand(22000, 24000), // VoltagePhase3 * 100
                rand(0, 30000), // CurrentPhase1 * 1000
                rand(0, 30000), // CurrentPhase2 * 1000
                rand(0, 30000), // CurrentPhase3 * 1000
                rand(0, 30000000), // PowerActivePhase1 * 100000
                rand(0, 30000000), // PowerActivePhase2 * 100000
                rand(0, 30000000), // PowerActivePhase3 * 100000
                rand(0, 10000000), // PowerRectivePhase1 * 100000
                rand(0, 10000000), // PowerRectivePhase2 * 100000
                rand(0, 10000000), // PowerRectivePhase3 * 100000
                rand(0, 10000000), // PowerApparentPhase1 * 100000
                rand(0, 10000000), // PowerApparentPhase2 * 100000
                rand(0, 10000000), // PowerApparentPhase3 * 100000
                rand(0, 100000), // PowerFactorPhase1 * 1000
                rand(0, 100000), // PowerFactorPhase2 * 1000
                rand(0, 100000), // PowerFactorPhase3 * 1000
                rand(0, 3600), // PhaseAnglePhase1 * 10
                rand(0, 3600), // PhaseAnglePhase2 * 10
                rand(0, 3600), // PhaseAnglePhase2 * 10
                rand(0, 100000000), // TotalForwardActiveEnergyPhase1 * 100000
                rand(0, 100000000), // TotalForwardActiveEnergyPhase2 * 100000
                rand(0, 100000000), // TotalForwardActiveEnergyPhase3 * 100000
                rand(0, 10000000), // TotalReverseActiveEnergyPhase1 * 100000
                rand(0, 10000000), // TotalReverseActiveEnergyPhase2 * 100000
                rand(0, 10000000), // TotalReverseActiveEnergyPhase3 * 100000
                rand(0, 10000000), // TotalForwardReactiveEnergyPhase1 * 100000
                rand(0, 10000000), // TotalForwardReactiveEnergyPhase2 * 100000
                rand(0, 10000000), // TotalForwardReactiveEnergyPhase3 * 100000
                rand(0, 10000000), // TotalReverseReactiveEnergyPhase1 * 100000
                rand(0, 10000000), // TotalReverseReactiveEnergyPhase2 * 100000
                rand(0, 10000000), // TotalReverseReactiveEnergyPhase3 * 100000
                rand(0, 10000), // TotalCost * 100
                rand(0, 100000), // PricePerUnit * 10000
                $this->faker->currencyCode
            );
        } elseif (preg_match('#^UPDATE-CHANNEL-STATE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            $channelId = $match[3];
            $channel = $this->em->find(IODeviceChannel::class, $channelId);
            $state = $this->em->find(ChannelState::class, $channelId);
            if (!$state) {
                $state = new ChannelState($channel);
            }
            AnyFieldSetter::set($state, 'state', json_encode([
                'switchCycleCount' => rand(1, 4),
                'ipv4' => $this->faker->ipv4(),
                'mac' => $this->faker->macAddress(),
                'batteryLevel' => rand(1, 100),
                'wifiRSSI' => rand(2, 10),
                'wifiSignalStrength' => rand(2, 100),
                'bridgeNodeOnline' => $this->faker->boolean(),
                'bridgeNodeSignalStrength' => rand(2, 100),
                'uptime' => rand(2, 999999),
                'connectionUptime' => rand(2, 999999),
                'batteryHealth' => rand(2, 100),
                'lastConnectionResetCause' => rand(0, 3),
                'lightSourceLifespan' => rand(0, 100),
            ]));
            $this->em->persist($state);
            $this->em->flush();
            return 'OK:HURRA';
        }
        return false;
    }

    protected function ensureCanConnect(): void {
    }

    public static function mockResponse(string $command, $response) {
        Assertion::keyNotExists(self::$mockedResponses, $command, $command . ' is already mocked.');
        if (!is_array($response)) {
            $response = [$response];
        }
        self::$mockedResponses[$command] = $response;
    }

    public static function reset() {
        self::$executedCommands = [];
        self::$mockedResponses = [];
    }
}

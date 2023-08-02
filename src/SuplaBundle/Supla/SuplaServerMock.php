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
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use SuplaBundle\Enums\ElectricityMeterSupportBits;
use SuplaBundle\Enums\SceneInitiatiorType;
use SuplaBundle\Model\LocalSuplaCloud;

/**
 * SuplaServer implementation to be used during development.
 */
class SuplaServerMock extends SuplaServer {
    private const IS_ALIVE = true;

    public static $mockedResponses = [];

    public static $executedCommands = [];

    /** @var SuplaServerMockCommandsCollector */
    private $commandsCollector;
    /** @var Generator */
    private $faker;

    public function __construct(SuplaServerMockCommandsCollector $commandsCollector, LoggerInterface $logger) {
        parent::__construct('', new LocalSuplaCloud('http://supla.local'), $logger);
        $this->commandsCollector = $commandsCollector;
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
        if (preg_match('#^IS-(IODEV|CLIENT|CHANNEL)-CONNECTED:(\d+),(\d+),?(\d+)?$#', $cmd, $match)) {
            if ($match[1] !== 'CLIENT' || $this->faker->boolean) {
                return "CONNECTED:$match[3]\n";
            } else {
                return "DISCONNECTED:$match[3]\n";
            }
        } elseif (preg_match('#^GET-STATUS$#', $cmd, $match)) {
            return "OK\n";
        } elseif (preg_match('#^USER.+:(\d+).*$#', $cmd, $match)) {
            return "OK:$match[1]\n";
        } elseif (preg_match('#^SET-(CG-)?(CHAR|RGBW|RAND-RGBW|DIGIGLASS)-VALUE:.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^ACTION-(CG-)?(.+?):.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^ENTER-CONFIGURATION-MODE:.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^(RESET-COUNTERS|RECALIBRATE):(\d+),(\d+),(\d+)$#', $cmd, $match)) {
            return "OK:$match[4]\n";
        } elseif (preg_match('#^(EXECUTE|INTERRUPT|INTERRUPT-AND-EXECUTE)-SCENE:.+$#', $cmd, $match)) {
            return 'OK:HURRA';
        } elseif (preg_match('#^GET-(CHAR)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . rand(0, 1);
        } elseif (preg_match('#^GET-(VALVE)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . rand(0, 1) . ',' . rand(0, 3);
        } elseif (preg_match('#^GET-(RELAY)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . rand(0, 1) . ',' . rand(0, 1);
        } elseif (preg_match('#^GET-(DIGIGLASS)-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . rand(0, (1 << 7) - 1);
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
            return 'VALUE:' . (rand(-2000, 2000) / 1000);
        } elseif (preg_match('#^GET-HUMIDITY-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . (rand(0, 1000) / 10);
        } elseif (preg_match('#^GET-DOUBLE-VALUE:(\d+),(\d+),(\d+)#', $cmd, $match)) {
            return 'VALUE:' . (rand(0, 1000000) / 100);
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
                $this->faker->boolean ? $this->faker->currencyCode : '', // currency
                $this->faker->boolean ? base64_encode($this->faker->randomElement(['m³', 'wahnięć', 'l'])) : '' // base-64 unit name
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
}

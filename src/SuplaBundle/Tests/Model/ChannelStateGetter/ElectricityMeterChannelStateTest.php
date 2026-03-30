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

namespace SuplaBundle\Tests\Model\ChannelStateGetter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Enums\ElectricityMeterSupportBits;
use SuplaBundle\Model\ChannelStateGetter\ElectricityMeterChannelState;

class ElectricityMeterChannelStateTest extends TestCase {
    /** @dataProvider clearUnsupportedMeasurementsTestCases */
    public function testClearUnsupportedMeasurements(int $supportMask, array $expectNotCleared) {
        $state = array_merge([$supportMask, 50], range(1, 33), [5, 6, 'PLN']);
        $state = (new ElectricityMeterChannelState($state))->toArray();
        $expectNotCleared[] = 'number';
        $this->assertCount(count($expectNotCleared), $state['phases'][0]);
        $this->assertCount(count($expectNotCleared), $state['phases'][1]);
        $this->assertCount(count($expectNotCleared), $state['phases'][2]);
        foreach ($expectNotCleared as $key) {
            $this->assertArrayHasKey($key, $state['phases'][0]);
            $this->assertArrayHasKey($key, $state['phases'][1]);
            $this->assertArrayHasKey($key, $state['phases'][2]);
        }
    }

    public static function clearUnsupportedMeasurementsTestCases() {
        return [
            [0, []],
            [ElectricityMeterSupportBits::CURRENT, ['current']],
            [ElectricityMeterSupportBits::CURRENT_OVER65A, ['current']],
            [ElectricityMeterSupportBits::POWER_ACTIVE, ['powerActive']],
            [ElectricityMeterSupportBits::POWER_ACTIVE_KW, ['powerActive']],
            [ElectricityMeterSupportBits::POWER_REACTIVE, ['powerReactive']],
            [ElectricityMeterSupportBits::POWER_REACTIVE_KVAR, ['powerReactive']],
            [ElectricityMeterSupportBits::POWER_APPARENT, ['powerApparent']],
            [ElectricityMeterSupportBits::POWER_APPARENT_KVA, ['powerApparent']],
            [ElectricityMeterSupportBits::CURRENT | ElectricityMeterSupportBits::FREQUENCY, ['frequency', 'current']],
            [ElectricityMeterSupportBits::CURRENT_OVER65A | ElectricityMeterSupportBits::FREQUENCY, ['frequency', 'current']],
            [ElectricityMeterSupportBits::TOTAL_FORWARD_REACTIVE_ENERGY, ['totalForwardReactiveEnergy']],
        ];
    }

    #[DataProvider('getPossibleStates')]
    public function testGetStates(array $suplaServerState, callable $callback) {
        $state = new ElectricityMeterChannelState($suplaServerState);
        $arr = $state->toArray();
        $callback($this, $arr);
    }

    public static function getPossibleStates(): array {
        return [
            [
                [
                    ElectricityMeterSupportBits::CURRENT,
                    ...array_fill(0, 4, "0"),
                    "18694317",
                    ...array_fill(0, 31, "0"),
                    "PLN",
                ],
                fn(TestCase $t, array $state) => $t->assertEquals(18694.317, $state['phases'][0]['current']),
            ],
            [
                [
                    ElectricityMeterSupportBits::CURRENT_OVER65A,
                    ...array_fill(0, 4, "0"),
                    "18694317",
                    ...array_fill(0, 31, "0"),
                    "PLN",
                ],
                fn(TestCase $t, array $state) => $t->assertEquals(18694.317, $state['phases'][0]['current']),
            ],
            [
                [
                    ElectricityMeterSupportBits::POWER_ACTIVE,
                    ...array_fill(0, 7, "0"),
                    "18694317",
                    ...array_fill(0, 28, "0"),
                    "PLN",
                ],
                fn(TestCase $t, array $state) => $t->assertEquals(186.94317, $state['phases'][0]['powerActive']),
            ],
            [
                [
                    ElectricityMeterSupportBits::POWER_ACTIVE_KW,
                    ...array_fill(0, 7, "0"),
                    "18694317",
                    ...array_fill(0, 28, "0"),
                    "PLN",
                ],
                fn(TestCase $t, array $state) => $t->assertEquals(186.94317, $state['phases'][0]['powerActive']),
            ],
            [
                [
                    ElectricityMeterSupportBits::POWER_REACTIVE,
                    ...array_fill(0, 10, "0"),
                    "18694317",
                    ...array_fill(0, 25, "0"),
                    "PLN",
                ],
                fn(TestCase $t, array $state) => $t->assertEquals(186.94317, $state['phases'][0]['powerReactive']),
            ],
            [
                [
                    ElectricityMeterSupportBits::POWER_REACTIVE_KVAR,
                    ...array_fill(0, 10, "0"),
                    "18694317",
                    ...array_fill(0, 25, "0"),
                    "PLN",
                ],
                fn(TestCase $t, array $state) => $t->assertEquals(186.94317, $state['phases'][0]['powerReactive']),
            ],
        ];
    }
}

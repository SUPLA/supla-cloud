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
            [
                ElectricityMeterSupportBits::CURRENT | ElectricityMeterSupportBits::FREQUENCY,
                ['frequency', 'current'],
            ],
            [
                ElectricityMeterSupportBits::TOTAL_FORWARD_REACTIVE_ENERGY,
                ['totalForwardReactiveEnergy'],
            ],
        ];
    }
}

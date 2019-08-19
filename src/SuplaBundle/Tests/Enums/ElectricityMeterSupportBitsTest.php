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

namespace SuplaBundle\Tests\Enums;

use SuplaBundle\Enums\ElectricityMeterSupportBits;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class ElectricityMeterSupportBitsTest extends \PHPUnit_Framework_TestCase {
    use UnitTestHelper;

    /** @dataProvider supportedFunctionsTestCases */
    public function testNullifyingUnsupportedFeatures(int $supportMask, array $expectNotNulls) {
        $state = array_combine(
            ElectricityMeterSupportBits::$POSSIBLE_STATE_KEYS,
            range(1, count(ElectricityMeterSupportBits::$POSSIBLE_STATE_KEYS))
        );
        $state = ElectricityMeterSupportBits::nullifyUnsupportedFeatures($supportMask, $state);
        $this->assertGreaterThan(30, count($state));
        unset($state['support']);
        unset($state['totalCost']);
        unset($state['pricePerUnit']);
        unset($state['currency']);
        foreach ($state as $key => $value) {
            if (in_array($key, $expectNotNulls)) {
                $this->assertNotNull($value, 'The value of ' . $key . ' should not be null.');
            } else {
                $this->assertNull($value, 'The value of ' . $key . ' should be null.');
            }
        }
    }

    public function supportedFunctionsTestCases() {
        return [
            [0, []],
            [ElectricityMeterSupportBits::CURRENT, ['currentPhase1', 'currentPhase2', 'currentPhase3']],
            [
                ElectricityMeterSupportBits::CURRENT | ElectricityMeterSupportBits::FREQUENCY,
                ['frequency', 'currentPhase1', 'currentPhase2', 'currentPhase3'],
            ],
            [
                ElectricityMeterSupportBits::TOTAL_FORWARD_REACTIVE_ENERGY,
                ['totalForwardReactiveEnergyPhase1', 'totalForwardReactiveEnergyPhase2', 'totalForwardReactiveEnergyPhase3'],
            ],
        ];
    }

    public function testEveryBitIsExclusive() {
        $bitsSum = 0;
        foreach (ElectricityMeterSupportBits::values() as $bit) {
            $newBitsSum = $bitsSum | $bit->getValue();
            $this->assertNotEquals($newBitsSum, $bitsSum, 'Non exclusive detected on ' . $bit->getKey());
            $bitsSum = $newBitsSum;
        }
    }

    /** @dataProvider transformingValuesFromServerExamples */
    public function testTransformingValuesFromServer(array $state, array $expectedState) {
        $actualState = ElectricityMeterSupportBits::transformValuesFromServer($state);
        $this->assertEquals($expectedState, $actualState);
    }

    public function transformingValuesFromServerExamples() {
        return [
            [['frequency' => 42], ['frequency' => 0.42]],
            [['frequency' => '42'], ['frequency' => 0.42]],
            [['frequency' => 42, 'unicorn' => 42], ['frequency' => 0.42, 'unicorn' => 42]],
            [['frequency' => 42, 'unicornPhase1' => 42], ['frequency' => 0.42, 'unicornPhase1' => 42]],
            [['voltagePhase1' => 42, 'voltagePhase3' => 43], ['voltagePhase1' => 0.42, 'voltagePhase3' => 0.43]],
            [['voltagePhase1' => 42, 'powerActivePhase1' => 43], ['voltagePhase1' => 0.42, 'powerActivePhase1' => 0.00043]],
            [['currentPhase2' => 42], ['currentPhase2' => 0.042]],
            [['powerActivePhase1' => 42], ['powerActivePhase1' => 0.00042]],
            [['powerReactivePhase2' => 42], ['powerReactivePhase2' => 0.00042]],
            [['powerApparentPhase3' => 42], ['powerApparentPhase3' => 0.00042]],
            [['powerFactorPhase1' => 42], ['powerFactorPhase1' => 0.042]],
            [['phaseAnglePhase2' => 42], ['phaseAnglePhase2' => 4.2]],
            [['totalForwardActiveEnergyPhase3' => 42], ['totalForwardActiveEnergyPhase3' => 0.00042]],
            [['totalReverseActiveEnergyPhase1' => 42], ['totalReverseActiveEnergyPhase1' => 0.00042]],
            [['totalForwardReactiveEnergyPhase2' => 42], ['totalForwardReactiveEnergyPhase2' => 0.00042]],
            [['totalReverseReactiveEnergyPhase3' => 42], ['totalReverseReactiveEnergyPhase3' => 0.00042]],
            [['totalCost' => 42], ['totalCost' => 42]], // this enum is NOT responsible for transforming such additional values
        ];
    }
}

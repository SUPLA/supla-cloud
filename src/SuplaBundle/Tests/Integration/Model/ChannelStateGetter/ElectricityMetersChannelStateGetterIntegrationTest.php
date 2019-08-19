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

namespace SuplaBundle\Tests\Integration\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\IODevice;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\ElectricityMeterSupportBits;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ElectricityMetersChannelStateGetterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelStateGetter */
    private $channelStateGetter;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $this->device = $this->createDevice($user->getLocations()[0], [
            [ChannelType::IMPULSECOUNTER, ChannelFunction::ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::GASMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
        ]);
        $this->channelStateGetter = $this->container->get(ChannelStateGetter::class);
    }

    public function testGettingStateForImpulseCounterElectricity() {
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $this->validateImpulseCounterHasAllKeys($state);
    }

    public function testGettingStateForImpulseCounterGas() {
        $state = $this->channelStateGetter->getState($this->device->getChannels()[1]);
        $this->validateImpulseCounterHasAllKeys($state);
    }

    private function validateImpulseCounterHasAllKeys(array $state) {
        $this->assertArrayHasKey('totalCost', $state);
        $this->assertGreaterThan(0, $state['totalCost']);
        $this->assertInternalType('float', $state['totalCost']);
        $this->assertArrayHasKey('pricePerUnit', $state);
        $this->assertInternalType('float', $state['pricePerUnit']);
        $this->assertArrayHasKey('impulsesPerUnit', $state);
        $this->assertInternalType('int', $state['impulsesPerUnit']);
        $this->assertArrayHasKey('counter', $state);
        $this->assertInternalType('int', $state['counter']);
        $this->assertArrayHasKey('calculatedValue', $state);
        $this->assertInternalType('float', $state['calculatedValue']);
        $this->assertArrayHasKey('currency', $state);
        $this->assertArrayHasKey('unit', $state);
        $this->assertArrayNotHasKey('currentPhase1', $state);
    }

    /** @dataProvider transformingImpulseCounterResponsesExamples */
    public function testTransformingImpulseCounterServerResponse(string $serverResponse, array $expectedState) {
        SuplaServerMock::mockResponse('GET-IC-', $serverResponse);
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $missingKeys = array_diff(array_keys($expectedState), array_keys($state));
        $this->assertEmpty($missingKeys, 'The resulting state does not contain some required keys: ' . implode(', ', $missingKeys));
        $this->assertEquals($expectedState, array_intersect_key($state, $expectedState));
    }

    public function transformingImpulseCounterResponsesExamples() {
        return [
            [
                'VALUE:10000,2000000,300,400,500000,PLN,dW5pY29ybgo=',
                [
                    'totalCost' => 100,
                    'pricePerUnit' => 200,
                    'impulsesPerUnit' => 300,
                    'counter' => 400,
                    'calculatedValue' => 500,
                    'currency' => 'PLN',
                    'unit' => 'unicorn',
                ],
            ],
            [
                'VALUE:10000,2000000,300,400,500000,,',
                [
                    'totalCost' => 100,
                    'pricePerUnit' => 200,
                    'impulsesPerUnit' => 300,
                    'counter' => 400,
                    'calculatedValue' => 500,
                    'currency' => null,
                    'unit' => null,
                ],
            ],
            [
                'VALUE:10350,123456,789,4998,1333,,bcKz',
                [
                    'totalCost' => 103.5,
                    'pricePerUnit' => 12.3456,
                    'impulsesPerUnit' => 789,
                    'counter' => 4998,
                    'calculatedValue' => 1.333,
                    'currency' => null,
                    'unit' => 'm³',
                ],
            ],
        ];
    }

    public function testGettingStateForNotImpulseCounterElectricity() {
        $state = $this->channelStateGetter->getState($this->device->getChannels()[2]);
        $this->assertArrayHasKey('totalCost', $state);
        $this->assertGreaterThan(0, $state['totalCost']);
        $this->assertInternalType('float', $state['totalCost']);
        $this->assertArrayHasKey('currentPhase1', $state);
        $this->assertInternalType('float', $state['currentPhase1']);
        $this->assertArrayHasKey('powerActivePhase3', $state);
        $this->assertInternalType('float', $state['powerActivePhase3']);
        $this->assertArrayHasKey('totalForwardReactiveEnergyPhase1', $state);
        $this->assertInternalType('float', $state['totalForwardReactiveEnergyPhase1']);
        $this->assertArrayHasKey('pricePerUnit', $state);
        $this->assertInternalType('float', $state['pricePerUnit']);
        $this->assertArrayHasKey('support', $state);
        $this->assertInternalType('int', $state['support']);
    }

    /** @dataProvider transformingNonImpulseCounterElectricityMeterResponsesExamples */
    public function testTransformingNonImpulseCounterElectricityMeterServerResponse(string $serverResponse, array $expectedState) {
        SuplaServerMock::mockResponse('GET-EM-', $serverResponse);
        $state = $this->channelStateGetter->getState($this->device->getChannels()[2]);
        $missingKeys = array_diff(array_keys($expectedState), array_keys($state));
        $this->assertEmpty($missingKeys, 'The resulting state does not contain some required keys: ' . implode(', ', $missingKeys));
        $this->assertEquals($expectedState, array_intersect_key($state, $expectedState));
    }

    public function transformingNonImpulseCounterElectricityMeterResponsesExamples() {
        $fullSupportMask = array_reduce(ElectricityMeterSupportBits::toArray(), function (int $acc, int $bit) {
            return $acc | $bit;
        }, 0);
        return [
            [
                'VALUE:' . $fullSupportMask . ',' . implode(',', range(2, 37)) . ',PLN',
                [
                    'frequency' => 0.02,
                    'voltagePhase1' => 0.03,
                    'voltagePhase2' => 0.04,
                    'voltagePhase3' => 0.05,
                    'currentPhase1' => 0.006,
                    'currentPhase2' => 0.007,
                    'currentPhase3' => 0.008,
                    'powerActivePhase1' => 0.00009,
                    'powerActivePhase2' => 0.00010,
                    'powerActivePhase3' => 0.00011,
                    'powerReactivePhase1' => 0.00012,
                    'powerReactivePhase2' => 0.00013,
                    'powerReactivePhase3' => 0.00014,
                    'powerApparentPhase1' => 0.00015,
                    'powerApparentPhase2' => 0.00016,
                    'powerApparentPhase3' => 0.00017,
                    'powerFactorPhase1' => 0.018,
                    'powerFactorPhase2' => 0.019,
                    'powerFactorPhase3' => 0.020,
                    'phaseAnglePhase1' => 2.1,
                    'phaseAnglePhase2' => 2.2,
                    'phaseAnglePhase3' => 2.3,
                    'totalForwardActiveEnergyPhase1' => 0.00024,
                    'totalForwardActiveEnergyPhase2' => 0.00025,
                    'totalForwardActiveEnergyPhase3' => 0.00026,
                    'totalReverseActiveEnergyPhase1' => 0.00027,
                    'totalReverseActiveEnergyPhase2' => 0.00028,
                    'totalReverseActiveEnergyPhase3' => 0.00029,
                    'totalForwardReactiveEnergyPhase1' => 0.00030,
                    'totalForwardReactiveEnergyPhase2' => 0.00031,
                    'totalForwardReactiveEnergyPhase3' => 0.00032,
                    'totalReverseReactiveEnergyPhase1' => 0.00033,
                    'totalReverseReactiveEnergyPhase2' => 0.00034,
                    'totalReverseReactiveEnergyPhase3' => 0.00035,
                    'totalCost' => 0.36,
                    'pricePerUnit' => 0.0037,
                    'currency' => 'PLN',
                ],
            ],
            [
                'VALUE:' . (ElectricityMeterSupportBits::POWER_FACTOR | ElectricityMeterSupportBits::TOTAL_FORWARD_ACTIVE_ENERGY)
                . ',' . implode(',', range(2, 37)) . ',PLN',
                [
                    'frequency' => null,
                    'voltagePhase1' => null,
                    'voltagePhase2' => null,
                    'voltagePhase3' => null,
                    'currentPhase1' => null,
                    'currentPhase2' => null,
                    'currentPhase3' => null,
                    'powerActivePhase1' => null,
                    'powerActivePhase2' => null,
                    'powerActivePhase3' => null,
                    'powerReactivePhase1' => null,
                    'powerReactivePhase2' => null,
                    'powerReactivePhase3' => null,
                    'powerApparentPhase1' => null,
                    'powerApparentPhase2' => null,
                    'powerApparentPhase3' => null,
                    'powerFactorPhase1' => 0.018,
                    'powerFactorPhase2' => 0.019,
                    'powerFactorPhase3' => 0.020,
                    'phaseAnglePhase1' => null,
                    'phaseAnglePhase2' => null,
                    'phaseAnglePhase3' => null,
                    'totalForwardActiveEnergyPhase1' => 0.00024,
                    'totalForwardActiveEnergyPhase2' => 0.00025,
                    'totalForwardActiveEnergyPhase3' => 0.00026,
                    'totalReverseActiveEnergyPhase1' => null,
                    'totalReverseActiveEnergyPhase2' => null,
                    'totalReverseActiveEnergyPhase3' => null,
                    'totalForwardReactiveEnergyPhase1' => null,
                    'totalForwardReactiveEnergyPhase2' => null,
                    'totalForwardReactiveEnergyPhase3' => null,
                    'totalReverseReactiveEnergyPhase1' => null,
                    'totalReverseReactiveEnergyPhase2' => null,
                    'totalReverseReactiveEnergyPhase3' => null,
                    'totalCost' => 0.36,
                    'pricePerUnit' => 0.0037,
                    'currency' => 'PLN',
                ],
            ],
            [
                // 3583 = all support bits without totalReverseActiveEnergy
                // @codingStandardsIgnoreLine
                'VALUE:3583,5204,22236,23658,22010,4327,18314,26734,9403971,4414093,22055246,5371061,9606801,8810954,8083181,1105777,6898797,83914,47928,92799,506,1019,95,53064359,57198474,2512057,1338299,1233842,2742607,5130437,4273139,9967795,7681364,8560780,8708398,9814,87466,VEF',
                [
                    'frequency' => 52.04,
                    'voltagePhase1' => 222.36,
                    'voltagePhase2' => 236.58,
                    'voltagePhase3' => 220.1,
                    'currentPhase1' => 4.327,
                    'currentPhase2' => 18.314,
                    'currentPhase3' => 26.734,
                    'powerActivePhase1' => 94.03971,
                    'powerActivePhase2' => 44.14093,
                    'powerActivePhase3' => 220.55246,
                    'powerReactivePhase1' => 53.71061,
                    'powerReactivePhase2' => 96.06801,
                    'powerReactivePhase3' => 88.10954,
                    'powerApparentPhase1' => 80.83181,
                    'powerApparentPhase2' => 11.05777,
                    'powerApparentPhase3' => 68.98797,
                    'powerFactorPhase1' => 83.914,
                    'powerFactorPhase2' => 47.928,
                    'powerFactorPhase3' => 92.799,
                    'phaseAnglePhase1' => 50.6,
                    'phaseAnglePhase2' => 101.9,
                    'phaseAnglePhase3' => 9.5,
                    'totalForwardActiveEnergyPhase1' => 530.64359,
                    'totalForwardActiveEnergyPhase2' => 571.98474,
                    'totalForwardActiveEnergyPhase3' => 25.12057,
                    'totalReverseActiveEnergyPhase1' => null,
                    'totalReverseActiveEnergyPhase2' => null,
                    'totalReverseActiveEnergyPhase3' => null,
                    'totalForwardReactiveEnergyPhase1' => 51.30437,
                    'totalForwardReactiveEnergyPhase2' => 42.73139,
                    'totalForwardReactiveEnergyPhase3' => 99.67795,
                    'totalReverseReactiveEnergyPhase1' => 76.81364,
                    'totalReverseReactiveEnergyPhase2' => 85.6078,
                    'totalReverseReactiveEnergyPhase3' => 87.08398,
                    'totalCost' => 98.14,
                    'pricePerUnit' => 8.7466,
                    'currency' => 'VEF',
                ],
            ],
        ];
    }
}

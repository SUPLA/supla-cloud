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

namespace SuplaBundle\Tests\Integration\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODevice;
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
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_ELECTRICITYMETER],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_GASMETER],
            [ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER],
        ]);
        $this->channelStateGetter = self::$container->get(ChannelStateGetter::class);
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
        $this->assertIsFloat($state['totalCost']);
        $this->assertArrayHasKey('pricePerUnit', $state);
        $this->assertIsFloat($state['pricePerUnit']);
        $this->assertArrayHasKey('impulsesPerUnit', $state);
        $this->assertIsInt($state['impulsesPerUnit']);
        $this->assertArrayHasKey('counter', $state);
        $this->assertIsInt($state['counter']);
        $this->assertArrayHasKey('calculatedValue', $state);
        $this->assertIsFloat($state['calculatedValue']);
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
            [
                // https://github.com/SUPLA/supla-cloud/issues/306
                'VALUE:5698982,152500,2500,9342595,3737038,PLN,a1do',
                [
                    'totalCost' => 56989.82,
                    'pricePerUnit' => 15.25,
                    'impulsesPerUnit' => 2500,
                    'counter' => 9342595,
                    'calculatedValue' => 3737.038,
                    'currency' => 'PLN',
                    'unit' => 'kWh',
                ],
            ],
            [
                // tests for values higher than 2^64 (unsigned long)
                'VALUE:92233720368547758079,92233720368547758079,9223372036854775807,9223372036854775807,92233720368547758079,PLN,a1do',
                [
                    'totalCost' => 922337203685477580.79,
                    'pricePerUnit' => 9223372036854775.8079,
                    'impulsesPerUnit' => 9223372036854775807,
                    'counter' => 9223372036854775807,
                    'calculatedValue' => 92233720368547758.079,
                    'currency' => 'PLN',
                    'unit' => 'kWh',
                ],
            ],
        ];
    }

    public function testGettingStateForNotImpulseCounterElectricity() {
        $state = $this->channelStateGetter->getState($this->device->getChannels()[2]);
        $this->assertArrayHasKey('totalCost', $state);
        $this->assertGreaterThan(0, $state['totalCost']);
        $this->assertIsFloat($state['totalCost']);
        $this->assertArrayHasKey('phases', $state);
        $this->assertCount(3, $state['phases']);
        $this->assertIsArray($state['phases'][0]);
        $this->assertArrayHasKey('number', $state['phases'][0]);
        $this->assertEquals(1, $state['phases'][0]['number']);
        $this->assertArrayHasKey('powerActive', $state['phases'][0]);
        $this->assertIsFloat($state['phases'][0]['powerActive']);
        $this->assertArrayHasKey('totalForwardReactiveEnergy', $state['phases'][1]);
        $this->assertIsFloat($state['phases'][1]['totalForwardReactiveEnergy']);
        $this->assertArrayHasKey('pricePerUnit', $state);
        $this->assertIsFloat($state['pricePerUnit']);
        $this->assertArrayHasKey('support', $state);
        $this->assertIsInt($state['support']);
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
                    'phases' => [
                        [
                            'number' => 1,
                            'frequency' => 0.02,
                            'voltage' => 0.03,
                            'current' => 0.006,
                            'powerActive' => 0.00009,
                            'powerReactive' => 0.00012,
                            'powerApparent' => 0.00015,
                            'powerFactor' => 0.018,
                            'phaseAngle' => 2.1,
                            'totalForwardActiveEnergy' => 0.00024,
                            'totalReverseActiveEnergy' => 0.00027,
                            'totalForwardReactiveEnergy' => 0.0003,
                            'totalReverseReactiveEnergy' => 0.00033,
                        ],
                        [
                            'number' => 2,
                            'frequency' => 0.02,
                            'voltage' => 0.04,
                            'current' => 0.007,
                            'powerActive' => 0.0001,
                            'powerReactive' => 0.00013,
                            'powerApparent' => 0.00016,
                            'powerFactor' => 0.019,
                            'phaseAngle' => 2.2,
                            'totalForwardActiveEnergy' => 0.00025,
                            'totalReverseActiveEnergy' => 0.00028,
                            'totalForwardReactiveEnergy' => 0.00031,
                            'totalReverseReactiveEnergy' => 0.00034,
                        ],
                        [
                            'number' => 3,
                            'frequency' => 0.02,
                            'voltage' => 0.05,
                            'current' => 0.008,
                            'powerActive' => 0.00011,
                            'powerReactive' => 0.00014,
                            'powerApparent' => 0.00017,
                            'powerFactor' => 0.02,
                            'phaseAngle' => 2.3,
                            'totalForwardActiveEnergy' => 0.00026,
                            'totalReverseActiveEnergy' => 0.00029,
                            'totalForwardReactiveEnergy' => 0.00032,
                            'totalReverseReactiveEnergy' => 0.00035,
                        ],
                    ],
                    'totalCost' => 0.36,
                    'pricePerUnit' => 0.0037,
                    'currency' => 'PLN',
                ],
            ],
            [
                'VALUE:' . (ElectricityMeterSupportBits::POWER_FACTOR | ElectricityMeterSupportBits::TOTAL_FORWARD_ACTIVE_ENERGY)
                . ',' . implode(',', range(2, 37)) . ',PLN',
                [
                    'phases' => [
                        [
                            'number' => 1,
                            'powerFactor' => 0.018,
                            'totalForwardActiveEnergy' => 0.00024,
                        ],
                        [
                            'number' => 2,
                            'powerFactor' => 0.019,
                            'totalForwardActiveEnergy' => 0.00025,
                        ],
                        [
                            'number' => 3,
                            'powerFactor' => 0.020,
                            'totalForwardActiveEnergy' => 0.00026,
                        ],
                    ],
                    'totalCost' => 0.36,
                    'pricePerUnit' => 0.0037,
                    'currency' => 'PLN',
                ],
            ],
            [
                // 3583 = all support bits without totalReverseActiveEnergy
                // @codingStandardsIgnoreLine
                'VALUE:3583,5204,22236,23658,22010,4327,18314,26734,9403971,4414093,22055246,5371061,9606801,8810954,8083181,1105777,6898797,83914,47928,92799,506,1019,95,5306664359,57198474,2512057,1338299,1233842,2742607,5130437,4273139,9967795,7681364,8560780,8708398,9814,87466,VEF',
                [
                    'phases' => [
                        [
                            'number' => 1,
                            'frequency' => 52.04,
                            'voltage' => 222.36,
                            'current' => 4.327,
                            'powerActive' => 94.03971,
                            'powerReactive' => 53.71061,
                            'powerApparent' => 80.83181,
                            'powerFactor' => 83.914,
                            'phaseAngle' => 50.6,
                            'totalForwardActiveEnergy' => 53066.64359,
                            'totalForwardReactiveEnergy' => 51.30437,
                            'totalReverseReactiveEnergy' => 76.81364,
                        ],
                        [
                            'number' => 2,
                            'frequency' => 52.04,
                            'voltage' => 236.58,
                            'current' => 18.314,
                            'powerActive' => 44.14093,
                            'powerReactive' => 96.06801,
                            'powerApparent' => 11.05777,
                            'powerFactor' => 47.928,
                            'phaseAngle' => 101.9,
                            'totalForwardActiveEnergy' => 571.98474,
                            'totalForwardReactiveEnergy' => 42.73139,
                            'totalReverseReactiveEnergy' => 85.6078,
                        ],
                        [
                            'number' => 3,
                            'frequency' => 52.04,
                            'voltage' => 220.1,
                            'current' => 26.734,
                            'powerActive' => 220.55246,
                            'powerReactive' => 88.10954,
                            'powerApparent' => 68.98797,
                            'powerFactor' => 92.799,
                            'phaseAngle' => 9.5,
                            'totalForwardActiveEnergy' => 25.12057,
                            'totalForwardReactiveEnergy' => 99.67795,
                            'totalReverseReactiveEnergy' => 87.08398,
                        ],
                    ],
                    'totalCost' => 98.14,
                    'pricePerUnit' => 8.7466,
                    'currency' => 'VEF',
                ],
            ],
            [
                // 7 = frequency & voltage & current
                // @codingStandardsIgnoreLine
                'VALUE:7,5204,22236,23658,22010,-4327,-18314,-26734,9403971,4414093,22055246,5371061,9606801,8810954,8083181,1105777,6898797,83914,47928,92799,506,1019,95,53064359,57198474,2512057,1338299,1233842,2742607,5130437,4273139,9967795,7681364,8560780,8708398,9814,87466,VEF',
                [
                    'phases' => [
                        [
                            'number' => 1,
                            'frequency' => 52.04,
                            'voltage' => 222.36,
                            'current' => -4.327,
                        ],
                        [
                            'number' => 2,
                            'frequency' => 52.04,
                            'voltage' => 236.58,
                            'current' => -18.314,
                        ],
                        [
                            'number' => 3,
                            'frequency' => 52.04,
                            'voltage' => 220.1,
                            'current' => -26.734,
                        ],
                    ],
                ],
            ],
        ];
    }
}

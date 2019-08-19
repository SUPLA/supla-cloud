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
class OnOffChannelStateGetterIntegrationTest extends IntegrationTestCase {
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

    private function validateImpulseCounterHasAllKeys(array $state): void {
        $this->assertArrayHasKey('totalCost', $state);
        $this->assertGreaterThan(0, $state['totalCost']);
        $this->assertArrayHasKey('pricePerUnit', $state);
        $this->assertArrayHasKey('impulsesPerUnit', $state);
        $this->assertArrayHasKey('counter', $state);
        $this->assertArrayHasKey('calculatedValue', $state);
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
        $this->assertArrayHasKey('currentPhase1', $state);
        $this->assertArrayHasKey('powerActivePhase3', $state);
        $this->assertArrayHasKey('totalForwardReactiveEnergyPhase1', $state);
        $this->assertArrayHasKey('pricePerUnit', $state);
    }

    /** @dataProvider transformingNonImpulseCounterElectricityMeterResponsesExamples */
    public function testTransformingNonImpulseCounterElectricityMeterServerResponse(string $serverResponse, array $expectedState) {
        SuplaServerMock::mockResponse('GET-EM-', $serverResponse);
        $state = $this->channelStateGetter->getState($this->device->getChannels()[2]);
        $missingKeys = array_diff(array_keys($expectedState), array_keys($state));
        $this->assertEmpty($missingKeys, 'The resulting state does not contain some required keys: ' . implode(', ', $missingKeys));
        foreach ($expectedState as $key => $value) {
            $this->assertEquals($value, $state[$key], $key . ' is different.', floatval($value) * 0.1);
        }
    }

    public function transformingNonImpulseCounterElectricityMeterResponsesExamples() {
        $fullSupportMask = array_reduce(ElectricityMeterSupportBits::toArray(), function (int $acc, int $bit) {
            return $acc | $bit;
        }, 0);
        return [
            [
                'VALUE:' . $fullSupportMask . ',' . implode(',', range(2, 37)) . ',PLN',
                [
                    'voltagePhase1' => 0.03,
                    'voltagePhase2' => 0.04,
                    'voltagePhase3' => 0.05,
                    'currentPhase1' => 0.006,
                    'currentPhase2' => 0.007,
                    'currentPhase3' => 0.008,
                    'powerActivePhase1' => 0.0001,
                    'powerActivePhase2' => 0.0001,
                    'powerActivePhase3' => 0.0001,
                    'powerReactivePhase1' => 0.00012,
                    'powerReactivePhase2' => 0.00013,
                    'powerReactivePhase3' => 0.00014,
                    'powerApparentPhase1' => 0.00015,
                    'powerApparentPhase2' => 0.00016,
                    'powerApparentPhase3' => 0.00017,
                    'powerFactorPhase1' => 0.018,
                    'powerFactorPhase2' => 0.019,
                    'powerFactorPhase3' => 0.02,
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
        ];
    }
}

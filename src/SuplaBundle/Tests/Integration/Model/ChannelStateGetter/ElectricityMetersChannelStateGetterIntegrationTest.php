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

    public function testGettingStateForNotImpulseCounterElectricity() {
        $state = $this->channelStateGetter->getState($this->device->getChannels()[2]);
        $this->assertArrayHasKey('totalCost', $state);
        $this->assertGreaterThan(0, $state['totalCost']);
        $this->assertArrayHasKey('currentPhase1', $state);
        $this->assertArrayHasKey('powerActivePhase3', $state);
        $this->assertArrayHasKey('totalForwardReactiveEnergyPhase1', $state);
        $this->assertArrayHasKey('pricePerUnit', $state);
    }

    public function testTransformingServerResponse() {
        SuplaServerMock::mockResponse('GET-IC-', 'VALUE:10000,2000000,300,400,500000,PLN,dW5pY29ybgo=');
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $expectedState = [
            'totalCost' => 100,
            'pricePerUnit' => 200,
            'impulsesPerUnit' => 300,
            'counter' => 400,
            'calculatedValue' => 500,
            'currency' => 'PLN',
            'unit' => 'unicorn',
        ];
        $this->assertEquals($expectedState, array_intersect_key($state, $expectedState));
    }
}

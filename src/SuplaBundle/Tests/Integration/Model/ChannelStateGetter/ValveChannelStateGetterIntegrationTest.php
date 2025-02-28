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

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ValveChannelStateGetterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var ChannelStateGetter */
    private $channelStateGetter;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::VALVEOPENCLOSE, ChannelFunction::VALVEOPENCLOSE],
            [ChannelType::VALVEPERCENTAGE, ChannelFunction::VALVEPERCENTAGE],
        ]);
        $this->channelStateGetter = self::$container->get(ChannelStateGetter::class);
    }

    /** @dataProvider valveStates */
    public function testGetValveState($channelIndex, $serverResponse, $expectedState) {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', "VALUE:$serverResponse\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[$channelIndex]);
        $expectedState['connected'] = true;
        $expectedState['connectedCode'] = 'CONNECTED';
        $this->assertEquals($expectedState, $state);
    }

    public function valveStates() {
        return [
            [0, '1,2', ['closed' => true, 'manuallyClosed' => true, 'flooding' => false]],
            [0, '1,3', ['closed' => true, 'manuallyClosed' => true, 'flooding' => true]],
            [0, '0,0', ['closed' => false, 'manuallyClosed' => false, 'flooding' => false]],
            [1, '1,1', ['closed' => 1, 'manuallyClosed' => false, 'flooding' => true]],
            [1, '50,1', ['closed' => 50, 'manuallyClosed' => false, 'flooding' => true]],
            [1, 'bleh', []],
        ];
    }
}

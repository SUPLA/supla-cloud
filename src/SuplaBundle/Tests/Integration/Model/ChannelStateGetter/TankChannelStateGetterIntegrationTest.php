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
class TankChannelStateGetterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var ChannelStateGetter */
    private $channelStateGetter;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::CONTAINER, ChannelFunction::SEPTIC_TANK],
        ]);
        $this->channelStateGetter = self::$container->get(ChannelStateGetter::class);
    }

    /** @dataProvider valveStates */
    public function testGetValveState($serverResponse, $expectedState) {
        SuplaServerMock::mockResponse('GET-CONTAINER-VALUE', "VALUE:$serverResponse\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $expectedState['connected'] = true;
        $expectedState['connectedCode'] = 1;
        $this->assertEquals($expectedState, $state);
    }

    public function valveStates() {
        return [
            ['0', ['fillLevel' => null]],
            ['1', ['fillLevel' => 0]],
            ['20', ['fillLevel' => 19]],
            ['101', ['fillLevel' => 100]],
        ];
    }
}

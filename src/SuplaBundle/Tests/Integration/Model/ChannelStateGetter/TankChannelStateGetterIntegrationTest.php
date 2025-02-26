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
        $expectedState['connectedCode'] = 'CONNECTED';
        $this->assertEquals($expectedState, array_intersect_key($state, $expectedState));
    }

    public function valveStates() {
        // @codingStandardsIgnoreStart
        return [
            ['0', ['fillLevel' => null]],
            ['0,0', ['fillLevel' => null]],
            ['1,0', ['fillLevel' => 0]],
            ['1', ['fillLevel' => 0]],
            ['20,0', ['fillLevel' => 19]],
            ['101,0', ['fillLevel' => 100]],
            ['20', ['fillLevel' => 19, 'warningLevel' => false, 'alarmLevel' => false, 'invalidSensorState' => false, 'soundAlarmOn' => false]],
            ['20,1', ['fillLevel' => 19, 'warningLevel' => true, 'alarmLevel' => false, 'invalidSensorState' => false, 'soundAlarmOn' => false]],
            ['20,7', ['fillLevel' => 19, 'warningLevel' => true, 'alarmLevel' => true, 'invalidSensorState' => true, 'soundAlarmOn' => false]],
        ];
        // @codingStandardsIgnoreEnd
    }
}

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
use Psr\Log\NullLogger;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\ChannelStateGetter\HvacChannelStateGetter;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Supla\SuplaServerMockCommandsCollector;
use SuplaBundle\Tests\Integration\Traits\SuplaAssertions;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class HvacStateGetterTest extends TestCase {
    use UnitTestHelper;
    use SuplaAssertions;

    /** @dataProvider stateExamples */
    public function testGettingState(string $suplaServerResponse, array $expectedState) {
        $stateGetter = new HvacChannelStateGetter();
        $stateGetter->setSuplaServer(new SuplaServerMock(new SuplaServerMockCommandsCollector(), new NullLogger()));
        $channel = $this->createSubjectMock(IODeviceChannel::class);
        $channel->method('getUser')->willReturn($this->createEntityMock(User::class));
        $channel->method('getIoDevice')->willReturn($this->createEntityMock(IODevice::class));
        SuplaServerMock::mockResponse('GET-HVAC-VALUE:1,1,1', $suplaServerResponse);
        $state = $stateGetter->getState($channel);
        $this->assertEquals($expectedState, array_intersect_key($state, $expectedState));
    }

    public function stateExamples() {
        return [
            ['VALUE:0,1,2,3,0', [
                'heating' => false,
                'cooling' => false,
                'manual' => true,
                'countdownTimer' => false,
                'thermometerError' => false,
                'clockError' => false,
                'forcedOffBySensor' => false,
                'weeklyScheduleTemporalOverride' => false,
                'mode' => 'OFF',
                'temperatureHeat' => null,
                'temperatureCool' => null,
            ]],
            ['VALUE:0,1,2,3,1', ['temperatureHeat' => 0.02, 'temperatureCool' => null]],
            ['VALUE:0,1,2,3,3', ['heating' => false, 'temperatureHeat' => 0.02, 'temperatureCool' => 0.03]],
            ['VALUE:0,1,2,3,7', ['heating' => true, 'cooling' => false, 'temperatureHeat' => 0.02, 'temperatureCool' => 0.03]],
            ['VALUE:0,1,2,3,8', ['heating' => false, 'cooling' => true, 'temperatureHeat' => null, 'temperatureCool' => null]],
            ['VALUE:0,1,2,3,16', ['manual' => false]],
            ['VALUE:0,1,2,3,384', ['thermometerError' => true, 'clockError' => true]],
            ['VALUE:0,1,2,3,512', ['forcedOffBySensor' => true]],
            ['VALUE:0,1,2,3,2048', ['weeklyScheduleTemporalOverride' => true]],
        ];
    }
}

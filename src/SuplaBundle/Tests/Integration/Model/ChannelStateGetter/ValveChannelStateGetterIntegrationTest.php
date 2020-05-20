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
class ValveChannelStateGetterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelStateGetter */
    private $channelStateGetter;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::VALVEOPENCLOSE, ChannelFunction::VALVEOPENCLOSE],
        ]);
        $this->channelStateGetter = self::$container->get(ChannelStateGetter::class);
    }

    public function testGetValveState() {
        SuplaServerMock::mockResponse('GET-CHAR-VALUE', "VALUE:1\n");
        SuplaServerMock::mockResponse('GET-VALVE-MANUALLY-CLOSED-VALUE', "VALUE:1\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $this->assertArrayHasKey('hi', $state);
        $this->assertEquals(true, $state['hi']);
        $this->assertArrayHasKey('manuallyClosed', $state);
        $this->assertEquals(true, $state['manuallyClosed']);
    }
}

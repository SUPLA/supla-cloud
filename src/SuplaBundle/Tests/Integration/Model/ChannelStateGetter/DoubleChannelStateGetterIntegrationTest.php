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
class DoubleChannelStateGetterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var ChannelStateGetter */
    private $channelStateGetter;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::WINDSENSOR, ChannelFunction::WINDSENSOR],
            [ChannelType::DISTANCESENSOR, ChannelFunction::DEPTHSENSOR],
        ]);
        $this->channelStateGetter = self::$container->get(ChannelStateGetter::class);
    }

    public function testGetGettingWindDouble() {
        SuplaServerMock::mockResponse('GET-DOUBLE-VALUE', "VALUE:2.3847\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $this->assertArrayHasKey('value', $state);
        $this->assertEquals(2.3847, $state['value']);
    }

    public function testGetGettingDepthDouble() {
        SuplaServerMock::mockResponse('GET-DOUBLE-VALUE', "VALUE:2.3847\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[1]);
        $this->assertArrayHasKey('depth', $state);
        $this->assertArrayNotHasKey('value', $state);
        $this->assertEquals(2.3847, $state['depth']);
    }
}

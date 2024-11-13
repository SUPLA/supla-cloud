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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelStateGetter\ExtendedChannelStateGetter;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ExtendedChannelStateGetterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    private ?IODevice $device;
    private ?IODeviceChannel $channel;
    private ?ExtendedChannelStateGetter $channelStateGetter;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::POWERSWITCH],
        ]);
        $this->channel = $this->device->getChannels()[0];
        $this->channelStateGetter = self::$container->get(ExtendedChannelStateGetter::class);
    }

    public function testNotBatteryPowered() {
        SuplaServerMock::mockResponse('GET-CHANNEL-STATE', 'STATE:0,0,0,0,0,0,0,0');
        $state = $this->channelStateGetter->getState($this->channel);
        $this->assertArrayHasKey('isBatteryPowered', $state);
        $this->assertFalse($state['isBatteryPowered']);
    }

    public function testBatteryPowered() {
        SuplaServerMock::mockResponse('GET-CHANNEL-STATE', 'STATE:0,0,0,0,0,0,1,0');
        $state = $this->channelStateGetter->getState($this->channel);
        $this->assertArrayHasKey('isBatteryPowered', $state);
        $this->assertTrue($state['isBatteryPowered']);
    }

    public function testIpAndMacAddress() {
        SuplaServerMock::mockResponse('GET-CHANNEL-STATE', 'STATE:0,0,0,127.89.22.33,AA:BB:CC,0,0,0');
        $state = $this->channelStateGetter->getState($this->channel);
        $this->assertArrayHasKey('ipv4Address', $state);
        $this->assertArrayHasKey('macAddress', $state);
        $this->assertEquals('127.89.22.33', $state['ipv4Address']);
        $this->assertEquals('AA:BB:CC', $state['macAddress']);
    }

    public function testBatteryLevel() {
        SuplaServerMock::mockResponse('GET-CHANNEL-STATE', 'STATE:0,0,0,0,0,66,0,0,0');
        $state = $this->channelStateGetter->getState($this->channel);
        $this->assertArrayHasKey('batteryLevel', $state);
        $this->assertEquals(66, $state['batteryLevel']);
    }
}

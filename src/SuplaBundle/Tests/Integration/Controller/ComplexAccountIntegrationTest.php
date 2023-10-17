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

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/**
 * Also known as @wsosniak case.
 *
 * It appears that when API is queried for channel group with "channels,iodevice,location" includes, the channel's location causes many
 * problems. It's because: group has channels, channel has location, location has iodevices, iodevices has channels, channels has
 * locations. You know.
 *
 * More such requests are tested in the following cases.
 *
 * @see https://forum.supla.org/viewtopic.php?p=50477#p50477
 * @small
 */
class ComplexAccountIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    const DEVICES_COUNT = 12;

    /** @var User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $aloneLocation = $this->createLocation($this->user);
        for ($i = 0; $i < self::DEVICES_COUNT; $i++) {
            $location = $this->createLocation($this->user);
            $device = $this->createDevice($location, [
                [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
                [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
                [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
                [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
                [ChannelType::RELAY, ChannelFunction::POWERSWITCH],
                [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
                [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
                [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
            ]);
            for ($k = 0; $k < 8; $k++) {
                $channelWithExplicitLocation = $device->getChannels()[$k];
                $channelWithExplicitLocation->setLocation($aloneLocation);
                $this->getEntityManager()->persist($channelWithExplicitLocation);
            }
            for ($j = 0; $j < 10; $j++) {
                $group1 = new IODeviceChannelGroup($this->user, $aloneLocation, [
                    $device->getChannels()[0],
                    $device->getChannels()[1],
                ]);
                $group2 = new IODeviceChannelGroup($this->user, $location, [
                    $device->getChannels()[2],
                    $device->getChannels()[3],
                ]);
                $this->getEntityManager()->persist($group1);
                $this->getEntityManager()->persist($group2);
            }
            $this->getEntityManager()->flush();
        }
    }

    public function testSerializingChannelGroupWithLocationThatContainsChannelsWithTheirOwnLocationsDoesNotGoCrazy() {
        $client = $this->createAuthenticatedClientDebug($this->user);
        $client->enableProfiler();
        $client->apiRequestV22('GET', '/api/channel-groups/1?include=channels,iodevice,location');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('location', $content);
        $profile = $client->getProfile();
        $this->assertNotNull($profile);
        $this->assertGreaterThan(1, $profile->getCollector('db')->getQueryCount());
        $this->assertLessThan(50, $profile->getCollector('db')->getQueryCount());
    }

    public function testGettingChannelGroups() {
        $client = $this->createAuthenticatedClientDebug($this->user);
        $client->enableProfiler();
        $client->apiRequestV22('GET', '/api/channel-groups');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('channelsIds', $content[0]);
        $profile = $client->getProfile();
        $this->assertNotNull($profile);
        $this->assertGreaterThan(1, $profile->getCollector('db')->getQueryCount());
        $this->assertLessThan(250, $profile->getCollector('db')->getQueryCount());
    }

    public function testGettingChannelGroupsV24() {
        $client = $this->createAuthenticatedClientDebug($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('GET', '/api/channel-groups');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('channelsIds', $content[0]);
        $this->assertArrayHasKey('relationsCount', $content[0]);
        $profile = $client->getProfile();
        $this->assertNotNull($profile);
        $this->assertGreaterThan(1, $profile->getCollector('db')->getQueryCount());
        $this->assertLessThan(10, $profile->getCollector('db')->getQueryCount());
    }

    public function testSerializingIoDevices() {
        $client = $this->createAuthenticatedClientDebug($this->user);
        $client->enableProfiler();
        $client->apiRequestV23('GET', '/api/iodevices?include=channels,location');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(self::DEVICES_COUNT, $content);
        $this->assertArrayHasKey('channels', $content[0]);
        $this->assertArrayHasKey('id', $content[0]['channels'][0]);
        $this->assertArrayHasKey('location', $content[0]);
        $this->assertArrayHasKey('id', $content[0]['location']);
        $profile = $client->getProfile();
        $this->assertNotNull($profile);
        $this->assertGreaterThan(1, $profile->getCollector('db')->getQueryCount());
        $this->assertLessThan(100, $profile->getCollector('db')->getQueryCount());
    }

    public function testSerializingIoDevicesIn24() {
        $client = $this->createAuthenticatedClientDebug($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('GET', '/api/iodevices?include=channels,location');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(self::DEVICES_COUNT, $content);
        $this->assertArrayHasKey('channels', $content[0]);
        $this->assertArrayHasKey('id', $content[0]['channels'][0]);
        $this->assertArrayHasKey('location', $content[0]);
        $this->assertArrayHasKey('id', $content[0]['location']);
        $this->assertArrayHasKey('relationsCount', $content[0]);
        $profile = $client->getProfile();
        $this->assertNotNull($profile);
        $this->assertGreaterThan(1, $profile->getCollector('db')->getQueryCount());
        $this->assertLessThan(30, $profile->getCollector('db')->getQueryCount());
    }
}

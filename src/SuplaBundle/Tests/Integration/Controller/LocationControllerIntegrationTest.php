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

use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class LocationControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->user->getLocations()[0];
        $this->device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
        ]);
        $group = new IODeviceChannelGroup($this->user, $this->location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[1],
        ]);
        $this->getEntityManager()->persist($group);
        $channelWithExplicitLocation = $this->device->getChannels()[0];
        $channelWithExplicitLocation->setLocation($this->location);
        $this->getEntityManager()->persist($channelWithExplicitLocation);
        $this->getEntityManager()->flush();
    }

    public function testGettingLocationsList() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $locationData = $content[0];
        $this->assertArrayHasKey('id', $locationData);
        $this->assertArrayHasKey('channelsIds', $locationData);
        $this->assertArrayHasKey('channelGroupsIds', $locationData);
        $this->assertArrayNotHasKey('channels', $locationData);
        $this->assertArrayNotHasKey('channelGroups', $locationData);
        $this->assertCount(1, $locationData['channelsIds']);
        $this->assertCount(1, $locationData['channelGroupsIds']);
    }

    public function testGettingLocationsListWithIncludes() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/locations?include=channels,channelGroups');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $locationData = $content[0];
        $this->assertArrayHasKey('id', $locationData);
        $this->assertArrayHasKey('channelsIds', $locationData);
        $this->assertArrayHasKey('channelGroupsIds', $locationData);
        $this->assertArrayHasKey('channels', $locationData);
        $this->assertArrayHasKey('channelGroups', $locationData);
        $this->assertCount(1, $locationData['channels']);
        $this->assertCount(1, $locationData['channelGroups']);
    }
}

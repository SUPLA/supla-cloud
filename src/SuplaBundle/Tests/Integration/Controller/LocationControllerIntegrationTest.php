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
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\User;
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
    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var \SuplaBundle\Entity\Main\Location */
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

    public function testGettingLocationsListApi24() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $locationData = $content[0];
        $this->assertArrayHasKey('id', $locationData);
        $this->assertArrayNotHasKey('channelsIds', $locationData);
        $this->assertArrayNotHasKey('channelGroupsIds', $locationData);
        $this->assertArrayNotHasKey('channels', $locationData);
        $this->assertArrayNotHasKey('channelGroups', $locationData);
        $this->assertArrayHasKey('relationsCount', $locationData);
        $relationsCount = $locationData['relationsCount'];
        $this->assertEquals(1, $relationsCount['channels']);
        $this->assertEquals(1, $relationsCount['channelGroups']);
        $this->assertEquals(1, $relationsCount['ioDevices']);
        $this->assertEquals(1, $relationsCount['accessIds']);
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

    public function testCreatingLocation() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Location #', $content['caption']);
    }

    public function testCreatingLocationWithPlLocale() {
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->user->setLocale('pl_PL');
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/locations');
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Lokalizacja #', $content['caption']);
    }

    public function testDeletingLocation() {
        $location = $this->createLocation($this->user);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('DELETE', '/api/locations/' . $location->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
    }

    public function testDeletingLocationWithIoDevice() {
        $location = $this->createLocation($this->user);
        $ioDevice = $this->createDeviceSonoff($location);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('DELETE', '/api/locations/' . $location->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Remove all the associated devices', $content['message']);
        $this->assertEquals($ioDevice->getId(), $content['details']['relatedIds']);
    }

    public function testDeletingLocationWithScene() {
        $location = $this->createLocation($this->user);
        $scene = new Scene($location);
        $this->persist($scene);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('DELETE', '/api/locations/' . $location->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Remove all the associated scenes', $content['message']);
        $this->assertEquals($scene->getId(), $content['details']['relatedIds']);
    }
}

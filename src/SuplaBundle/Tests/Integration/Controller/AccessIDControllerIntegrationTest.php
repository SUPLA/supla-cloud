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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class AccessIDControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    public function testCreatingAid() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/accessids');
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Access Identifier #', $content['caption']);
    }

    /** @depends testCreatingAid */
    public function testGettingAids() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/accessids?include=activeNow');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content);
        $first = $content[0];
        $this->assertArrayHasKey('activeNow', $first);
        $this->assertTrue($first['activeNow']);
    }

    public function testUpdatingAccessId() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'caption' => 'Unicorn',
            'enabled' => true,
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Unicorn', $content['caption']);
        $this->assertTrue($content['enabled']);
        $this->assertArrayNotHasKey('password', $content);
    }

    /** @depends testUpdatingAccessId */
    public function testUpdatingOnlyCaptionDoesNotChangeEnabled() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'caption' => 'Unicorn 2',
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Unicorn 2', $content['caption']);
        $this->assertTrue($content['enabled']);
    }

    public function testUpdatingActiveFromAndTo() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'activeFrom' => '+5 minutes',
            'activeTo' => (new \DateTime('2020-01-01'))->format(\DateTime::ISO8601),
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertNotNull($content['activeFrom']);
        $this->assertNotNull($content['activeTo']);
        $this->assertEquals('2020-01-01T00:00:00+00:00', $content['activeTo']);
    }

    /** @depends testUpdatingActiveFromAndTo */
    public function testClearingActiveTo() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'activeTo' => null,
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertNotNull($content['activeFrom']);
        $this->assertNull($content['activeTo']);
    }

    public function testInvalidActiveFrom() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'activeFrom' => 'Unicorn',
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testUpdatingActiveHours() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'activeHours' => [1 => [2, 4], 4 => [2]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertNotNull($content['activeHours']);
        $this->assertEquals([1 => [2, 4], 4 => [2]], $content['activeHours']);
    }

    public function testUpdatingLocations() {
        $location1 = $this->createLocation($this->user);
        $location2 = $this->createLocation($this->user);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'locationsIds' => [$location1->getId(), $location2->getId()],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $location1 = $this->freshEntity($location1);
        $location2 = $this->freshEntity($location2);
        $this->assertEquals([1], EntityUtils::mapToIds($location1->getAccessIds()));
        $this->assertEquals([1], EntityUtils::mapToIds($location2->getAccessIds()));
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'locationsIds' => [$location2->getId()],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $location1 = $this->freshEntity($location1);
        $location2 = $this->freshEntity($location2);
        $this->assertEmpty($location1->getAccessIds());
        $this->assertEquals([1], EntityUtils::mapToIds($location2->getAccessIds()));
    }

    public function testUpdatingClientApps() {
        $clientApp1 = $this->createClientApp($this->user);
        $clientApp2 = $this->createClientApp($this->user);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'clientAppsIds' => [$clientApp1->getId(), $clientApp2->getId()],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $clientApp1 = $this->freshEntity($clientApp1);
        $clientApp2 = $this->freshEntity($clientApp2);
        $this->assertEquals(1, $clientApp1->getAccessId()->getId());
        $this->assertEquals(1, $clientApp2->getAccessId()->getId());
        $client->apiRequestV24('PUT', '/api/accessids/1', [
            'clientAppsIds' => [$clientApp2->getId()],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $clientApp1 = $this->freshEntity($clientApp1);
        $clientApp2 = $this->freshEntity($clientApp2);
        $this->assertEmpty($clientApp1->getAccessId());
        $this->assertEquals(1, $clientApp2->getAccessId()->getId());
    }

    public function testCreatingLocationWithPlLocale() {
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->user->setLocale('pl_PL');
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/accessids');
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Identyfikator Dostępu #', $content['caption']);
    }
}

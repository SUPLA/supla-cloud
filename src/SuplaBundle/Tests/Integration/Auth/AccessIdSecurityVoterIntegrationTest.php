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

namespace SuplaBundle\Tests\Integration\Auth;

use DateTime;
use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Repository\ApiClientRepository;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

class AccessIdSecurityVoterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;
    /** @var Location */
    private $location1;
    /** @var Location */
    private $location2;
    /** @var AccessID */
    private $accessId;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location1 = $this->createLocation($this->user);
        $this->location2 = $this->createLocation($this->user);
        $this->createDevice($this->location1, [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]]);
        $this->createDevice($this->location2, [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]]);
        $this->accessId = new AccessID($this->user);
        $this->accessId->setPassword('abcd');
        $this->accessId->setCaption('AID');
        $this->getEntityManager()->persist($this->accessId);
        $token = new AccessToken();
        EntityUtils::setField($token, 'client', self::$container->get(ApiClientRepository::class)->getWebappClient());
        EntityUtils::setField($token, 'user', $this->user);
        EntityUtils::setField($token, 'expiresAt', (new DateTime('2035-01-01T00:00:00'))->getTimestamp());
        EntityUtils::setField($token, 'token', 'abc');
        EntityUtils::setField($token, 'scope', (string)(new OAuthScope(OAuthScope::getSupportedScopes())));
        EntityUtils::setField($token, 'accessId', $this->accessId);
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    /** @small */
    public function testGettingChannelInfoOfChannelNotIncludedInAccessIdIsFailed() {
        $client = $this->createInsulatedClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer abc', 'HTTPS' => true]);
        $client->request('GET', '/api/channels/1');
        $response = $client->getResponse();
        $this->assertStatusCode(403, $response);
    }

    /** @small */
    public function testGettingChannelsListWithNoLocationsAccessId() {
        $client = $client = $this->createInsulatedClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer abc', 'HTTPS' => true]);
        $client->request('GET', '/api/channels');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertEmpty(json_decode($response->getContent(), true));
    }

    public function testGettingChannelInfoOfChannelIncludedInAccessIdIsSuccessful() {
        $this->accessId->updateLocations([$this->location1]);
        $this->getEntityManager()->persist($this->accessId);
        $this->getEntityManager()->flush();
        $client = $client = $this->createInsulatedClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer abc', 'HTTPS' => true]);
        $client->request('GET', '/api/channels/1');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
    }

    public function testGettingChannelsListWithOneLocationInAccessId() {
        $this->accessId->updateLocations([$this->location2]);
        $this->getEntityManager()->persist($this->accessId);
        $this->getEntityManager()->flush();
        $client = $client = $this->createInsulatedClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer abc', 'HTTPS' => true]);
        $client->request('GET', '/api/channels');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals(2, $content[0]['id']);
    }

    public function testGettingDevicesListWithOneLocationInAccessId() {
        $this->accessId->updateLocations([$this->location1]);
        $this->getEntityManager()->persist($this->accessId);
        $this->getEntityManager()->flush();
        $client = $client = $this->createInsulatedClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer abc', 'HTTPS' => true]);
        $client->request('GET', '/api/v2.3.0/iodevices');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals(1, $content[0]['id']);
    }
}

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

use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Repository\ApiClientRepository;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

class AccessIdSecurityVoterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;
    /** @var AccessID */
    private $accessId;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
        $this->device = $this->createDevice($this->location, [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]]);
        $this->accessId = new AccessID($this->user);
        $this->accessId->setPassword('abcd');
        $this->accessId->setCaption('AID');
        $this->getEntityManager()->persist($this->accessId);
        $token = new AccessToken();
        EntityUtils::setField($token, 'client', $this->container->get(ApiClientRepository::class)->getWebappClient());
        EntityUtils::setField($token, 'user', $this->user);
        EntityUtils::setField($token, 'expiresAt', (new \DateTime('2035-01-01T00:00:00'))->getTimestamp());
        EntityUtils::setField($token, 'token', 'abc');
        EntityUtils::setField($token, 'scope', (string)(new OAuthScope(OAuthScope::getSupportedScopes())));
        EntityUtils::setField($token, 'accessId', $this->accessId);
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    public function testGettingChannelInfoOfChannelNotIncludedInAccessIdIsFailed() {
        $client = $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer abc', 'HTTPS' => true]);
        $channel = $this->device->getChannels()[0];
        $client->request('GET', '/api/channels/' . $channel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(403, $response);
    }

    public function testGettingChannelInfoOfChannelIncludedInAccessIdIsSuccessful() {
        $this->accessId->updateLocations([$this->location]);
        $this->getEntityManager()->persist($this->accessId);
        $this->getEntityManager()->flush();
        $client = $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer abc', 'HTTPS' => true]);
        $channel = $this->device->getChannels()[0];
        $client->request('GET', '/api/channels/' . $channel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
    }
}

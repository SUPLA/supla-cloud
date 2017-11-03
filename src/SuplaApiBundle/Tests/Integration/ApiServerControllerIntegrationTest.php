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

namespace SuplaApiBundle\Tests\Integration;

use SuplaApiBundle\Entity\Client;
use SuplaApiBundle\Model\ApiVersions;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

class ApiServerControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;

    protected function setUp() {
        $this->user = $this->createConfirmedUserWithApiAccess();
    }

    public function testGettingServerInfoWithoutAuthentication() {
        $client = self::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/api/server-info');
        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testGettingServerInfoAsOauthUser() {
        $client = $this->createAuthenticatedApiClient($this->user);
        $client->request('GET', '/api/server-info');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals($this->container->getParameter('supla_server'), $content->data->address);
        $this->assertNotEmpty($content->data->time);
        $this->assertFalse(property_exists($content->data, 'username')); // added in v2.2
    }

    public function testGettingServerInfoAsWebUser() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/web-api/server-info');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals($this->container->getParameter('supla_server'), $content->data->address);
        $this->assertNotEmpty($content->data->time);
        $this->assertFalse(property_exists($content->data, 'username')); // added in v2.2
    }

    public function testGettingServerInfoAsOauthUserForVersion22() {
        $client = $this->createAuthenticatedApiClient($this->user);
        $client->request('GET', '/api/server-info', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals($this->container->getParameter('supla_server'), $content->address);
        $this->assertEquals('supler@supla.org', $content->username);
        $this->assertNotEmpty($content->time);
    }

    public function testGettingServerInfoAsWebUserForVersion22() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/web-api/server-info', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertEquals($this->container->getParameter('supla_server'), $content->address);
        $this->assertEquals('supler@supla.org', $content->username);
        $this->assertNotEmpty($content->time);
    }

    public function testGettingApiEndpointAsWebUser() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/server-info');
        $this->assertStatusCode(401, $client->getResponse());
    }

    public function testGettingWebApiEndpointAsOAuthUser() {
        $client = $this->createAuthenticatedApiClient($this->user);
        $client->request('GET', '/web-api/server-info');
        $this->assertStatusCode(401, $client->getResponse());
    }
}

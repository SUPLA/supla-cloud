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

use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ServerControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    public function testGettingServerInfoWithoutAuthentication() {
        $client = self::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/api/server-info');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('username', $content);
    }

    public function testGettingServerInfoWithoutAuthentication22() {
        $client = self::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/api/server-info', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['authenticated']);
        $this->assertArrayNotHasKey('username', $content);
    }

    public function testGettingServerInfoWithoutAuthentication22InUrl() {
        $client = self::createClient();
        $client->followRedirects(true);
        $client->request('GET', '/api/v2.2.0/server-info');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertFalse($content['authenticated']);
        $this->assertArrayNotHasKey('username', $content);
        $this->assertArrayNotHasKey('serverStatus', $content);
    }

    public function testGettingServerInfo() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/server-info');
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertEquals(self::$container->getParameter('supla_server'), $content->data->address);
        $this->assertNotEmpty($content->data->time);
        $this->assertFalse(property_exists($content->data, 'username')); // added in v2.2
    }

    public function testGettingServerInfoForVersion22() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/server-info', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertEquals(self::$container->getParameter('supla_server'), $content->address);
        $this->assertEquals('supler@supla.org', $content->username);
        $this->assertNotEmpty($content->time);
    }

    public function testGettingServerInfoForVersion22InUrl() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/v2.2.0/server-info');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertEquals(self::$container->getParameter('supla_server'), $content->address);
        $this->assertEquals('supler@supla.org', $content->username);
        $this->assertNotEmpty($content->time);
    }

    public function testGettingServerInfoForVersion24() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/v2.4.0/server-info', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertEquals(self::$container->getParameter('supla_server'), $content->address);
        $this->assertEquals('supler@supla.org', $content->username);
        $this->assertNotEmpty($content->time);
        $this->assertEquals('OK', $content->serverStatus);
    }
}

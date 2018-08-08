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

namespace SuplaApiBundle\Tests\Integration\Controller;

use SuplaApiBundle\Model\ApiVersions;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;

class ApiTokensControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
    }

    public function testIssuingTokenForWebapp() {
        $client = self::createClient([], ['HTTPS' => true]);
        $client->request('POST', '/api/webapp-tokens', [
            'username' => 'supler@supla.org',
            'password' => 'supla123',
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $content);
        $this->assertArrayHasKey('refresh_token', $content);
        return $content;
    }

    public function testNotIssuingTokenForWebappIfWrongPassword() {
        $client = self::createClient([], ['HTTPS' => true]);
        $client->request('POST', '/api/webapp-tokens', [
            'username' => 'supler@supla.org',
            'password' => 'supla1233',
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(401, $response);
    }

    public function testAccessingApiWithIssuedWebappToken() {
        $token = $this->testIssuingTokenForWebapp()['access_token'];
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'HTTPS' => true]);
        $client->followRedirects();
        $client->request('GET', '/api/server-info', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent());
        $this->assertTrue($content->authenticated);
    }

    public function testAccessingApiWithInvalidToken() {
        $token = $this->testIssuingTokenForWebapp()['access_token'];
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token . 'a', 'HTTPS' => true]);
        $client->followRedirects();
        $client->request('GET', '/api/server-info', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testAccessingWebappOnlyApiWithIssuedWebappToken() {
        $token = $this->testIssuingTokenForWebapp()['access_token'];
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'HTTPS' => true]);
        $client->followRedirects();
        $client->request('GET', '/api/oauth-personal-tokens', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRefreshingWebappToken() {
        $tokenData = $this->testIssuingTokenForWebapp();
        $client = self::createClient([], ['HTTPS' => true]);
        $client->request('POST', '/api/webapp-tokens', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $tokenData['refresh_token'],
        ]);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $content);
        $this->assertArrayHasKey('refresh_token', $content);
        $this->assertNotEquals($content['access_token'], $tokenData['access_token']);
        $this->assertNotEquals($content['refresh_token'], $tokenData['refresh_token']);
        return $content;
    }

    public function testRefreshingWebappTokenWithInvalidToken() {
        $tokenData = $this->testIssuingTokenForWebapp();
        $client = self::createClient([], ['HTTPS' => true]);
        $client->request('POST', '/api/webapp-tokens', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $tokenData['refresh_token'] . 'a',
        ]);
        $this->assertStatusCode(401, $client->getResponse());
    }

    public function testAccessingWebappOnlyApiWithRefreshedWebappToken() {
        $token = $this->testRefreshingWebappToken()['access_token'];
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'HTTPS' => true]);
        $client->followRedirects();
        $client->request('GET', '/api/oauth-personal-tokens', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testIssuingTokenForWebappViaWebappAuthBroker() {
        $client = self::createClient([], ['HTTPS' => true]);
        $client->request('POST', '/api/webapp-auth', [
            'username' => 'supler@supla.org',
            'password' => 'supla123',
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $content);
        $this->assertArrayHasKey('refresh_token', $content);
        return $content;
    }
}

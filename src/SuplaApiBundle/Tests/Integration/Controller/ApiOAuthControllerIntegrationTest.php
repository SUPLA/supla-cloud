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

use SuplaApiBundle\Auth\OAuthScope;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;

class ApiOAuthControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
    }

    public function testCreatingPersonalAccessToken() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequest('POST', '/api/oauth-personal-tokens', [
            'name' => 'My Sample Token',
            'scope' => (string)(new OAuthScope('account_r')),
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $content);
        $this->assertArrayHasKey('name', $content);
        $this->assertArrayHasKey('scope', $content);
        $this->assertEquals('My Sample Token', $content['name']);
        return $content;
    }

    public function testPersonalAccessTokenIsGrantedImplicitScopes() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequest('POST', '/api/oauth-personal-tokens', [
            'name' => 'My Sample Token',
            'scope' => (string)(new OAuthScope('account_rw')),
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('scope', $content);
        $this->assertEquals('account_rw account_r', $content['scope']);
    }

    public function testCannotCreatePersonalAccessTokenWithRestapiScope() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequest('POST', '/api/oauth-personal-tokens', [
            'name' => 'My Sample Token',
            'scope' => (string)(new OAuthScope('restapi')),
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testListOfTokensDoesNotContainTokens() {
        $this->testCreatingPersonalAccessToken();
        $client = $this->createAuthenticatedClient();
        $client->apiRequest('GET', '/api/oauth-personal-tokens');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertArrayHasKey('id', $content[0]);
        $this->assertArrayHasKey('name', $content[0]);
        $this->assertArrayHasKey('scope', $content[0]);
        $this->assertArrayNotHasKey('token', $content[0]);
    }

    public function testCannotAskForPersonalTokenExposure() {
        $this->testCreatingPersonalAccessToken();
        $client = $this->createAuthenticatedClient();
        $client->apiRequest('GET', '/api/oauth-personal-tokens?include=token');
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testAccessApiWithPersonalAccessToken() {
        $token = $this->testCreatingPersonalAccessToken()['token'];
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'HTTPS' => true]);
        $client->followRedirects();
        $client->request('GET', '/api/users/current');
        $this->assertStatusCode(200, $client->getResponse());
        $client->request('GET', '/api/iodevices');
        $this->assertStatusCode(403, $client->getResponse());
        $client->request('GET', '/api/unicorns');
        $this->assertStatusCode(404, $client->getResponse());
    }
}

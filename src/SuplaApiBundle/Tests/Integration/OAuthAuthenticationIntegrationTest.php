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

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use SuplaApiBundle\Entity\OAuth\ApiClient;
use SuplaApiBundle\Entity\OAuth\AuthCode;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use Symfony\Component\DomCrawler\Crawler;

class OAuthAuthenticationIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var ApiClient */
    private $client;

    /** @var User */
    private $user;

    /** @before */
    public function init() {
        $clientManager = $this->container->get(ClientManagerInterface::class);
        $client = $clientManager->createClient();
        $client->setRedirectUris(['https://unicorns.pl']);
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE]);
        $clientManager->updateClient($client);
        $this->client = $client;
        $this->user = $this->createConfirmedUser();
    }

    private function makeOAuthAuthorizeRequest(array $params = []) {
        $params = array_merge([
            'client_id' => $this->client->getPublicId(),
            'redirect_uri' => 'https://unicorns.pl',
            'response_type' => 'code',
            'scope' => 'account_r offline_access',
        ], $params);
        $client = $this->createClient();
        $client->followRedirects();
        $client->request('GET', '/oauth/v2/auth?' . http_build_query($params));
        /** @var Crawler $crawler */
        $crawler = $client->apiRequest('POST', '/oauth/v2/auth_login', ['_username' => 'supler@supla.org', '_password' => 'supla123']);
        $form = $crawler->selectButton('accepted')->form();
        $client->submit($form);
    }

    public function testGrantingAccess() {
        $this->makeOAuthAuthorizeRequest();
        $user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertCount(1, $user->getApiClientAuthorizations());
        $authorization = $user->getApiClientAuthorizations()[0];
        $this->assertEquals($this->client->getId(), $authorization->getApiClient()->getId());
        $this->assertEquals('account_r offline_access', $authorization->getScope());
    }

    private function issueTokenBasedOnAuthCode($authCode = null) {
        if (!$authCode) {
            $authCodes = $this->getDoctrine()->getRepository(AuthCode::class)->findByClient($this->client);
            $this->assertCount(1, $authCodes);
            $authCode = $authCodes[0];
        }
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->getPublicId(),
            'client_secret' => $this->client->getSecret(),
            'redirect_uri' => 'https://unicorns.pl',
            'code' => $authCode->getToken(),
        ];
        $client = $this->createClient();
        $client->followRedirects();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent(), true);
        return $response;
    }

    public function testIssuingTokenBasedOnAuthCode() {
        $this->makeOAuthAuthorizeRequest();
        $response = $this->issueTokenBasedOnAuthCode();
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
        $this->assertArrayHasKey('scope', $response);
        $this->assertEquals('account_r offline_access', $response['scope']);
    }

    public function testNotIssuingRefreshTokenIfOfflineAccessNotRequested() {
        $this->makeOAuthAuthorizeRequest(['scope' => 'account_r']);
        $response = $this->issueTokenBasedOnAuthCode();
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayNotHasKey('refresh_token', $response);
        $this->assertArrayHasKey('scope', $response);
        $this->assertEquals('account_r', $response['scope']);
    }

    public function testCannotAskForTokenWithUnsupportedScope() {
        $this->makeOAuthAuthorizeRequest(['scope' => 'restapi']);
        $authCodes = $this->getDoctrine()->getRepository(AuthCode::class)->findByClient($this->client);
        $this->assertEmpty($authCodes);
    }

//    public function testAutomaticallyGrantForAlreadyAuthorizedClients() {
//        $this->makeOAuthAuthorizeRequest();
//
//    }

    public function testAccessingApiWithGivenToken() {
        $this->makeOAuthAuthorizeRequest(['scope' => 'account_r']);
        $response = $this->issueTokenBasedOnAuthCode();
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $response['access_token'], 'HTTPS' => true]);
        $client->followRedirects();
        $client->request('GET', '/api/users/current');
        $this->assertStatusCode(200, $client->getResponse());
        $client->request('GET', '/api/iodevices');
        $this->assertStatusCode(403, $client->getResponse());
        $client->request('GET', '/api/unicorns');
        $this->assertStatusCode(404, $client->getResponse());
    }
}

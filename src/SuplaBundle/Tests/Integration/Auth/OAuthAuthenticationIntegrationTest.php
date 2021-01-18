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

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\OAuth\ApiClientAuthorization;
use SuplaBundle\Entity\OAuth\AuthCode;
use SuplaBundle\Entity\User;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
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
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $clientManager->updateClient($client);
        $this->client = $client;
        $this->user = $this->createConfirmedUser();
    }

    private function makeOAuthAuthorizeRequest(array $params = [], array $testCase = []): TestClient {
        $testCase = array_merge(['grant' => true, 'login' => true], $testCase);
        $params = array_merge([
            'client_id' => $this->client->getPublicId(),
            'redirect_uri' => 'https://unicorns.pl',
            'response_type' => 'code',
            'scope' => 'account_r offline_access',
        ], $params);
        $client = $this->createClient();
        $client->followRedirects();
        $client->request('GET', '/oauth/v2/auth?' . http_build_query($params));
        if ($testCase['login']) {
            /** @var Crawler $crawler */
            $crawler = $client->apiRequest('POST', '/oauth/v2/auth_login', ['_username' => 'supler@supla.org', '_password' => 'supla123']);
            if ($testCase['grant']) {
                $client->followRedirects(false);
                $form = $crawler->selectButton('accepted')->form();
                $client->submit($form);
            }
        }
        return $client;
    }

    public function testGrantingAccess() {
        $this->makeOAuthAuthorizeRequest();
        $user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertCount(1, $user->getApiClientAuthorizations());
        $authorization = $user->getApiClientAuthorizations()[0];
        $this->assertEquals($this->client->getId(), $authorization->getApiClient()->getId());
        $this->assertEquals('account_r offline_access', $authorization->getScope());
    }

    public function testRedirectionAfterGrantingAccess() {
        $client = $this->makeOAuthAuthorizeRequest(['state' => 'horse']);
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirection());
        $targetUrl = $response->headers->get('Location');
        $this->assertContains('https://unicorns.pl?', $targetUrl);
        $this->assertContains('state=horse', $targetUrl);
        $this->assertContains('code=', $targetUrl);
    }

    public function testLogsOutAfterGrantingAccess() {
        $this->makeOAuthAuthorizeRequest();
        $client = $this->makeOAuthAuthorizeRequest(['client_id' => '1_local'], ['login' => false]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertContains('ask-for-target-cloud', $response->getContent());
    }

    private function issueTokenBasedOnAuthCode($authCode = null, array $params = []) {
        if (!$authCode) {
            $authCodes = $this->getDoctrine()->getRepository(AuthCode::class)->findByClient($this->client);
            $this->assertCount(1, $authCodes);
            $authCode = $authCodes[0];
        }
        $params = array_merge([
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->getPublicId(),
            'client_secret' => $this->client->getSecret(),
            'redirect_uri' => 'https://unicorns.pl',
            'code' => $authCode->getToken(),
        ], $params);
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
        $this->assertArrayHasKey('target_url', $response);
        $this->assertEquals('account_r offline_access', $response['scope']);
    }

    public function testErrorWhenWrongSecret() {
        $this->makeOAuthAuthorizeRequest();
        $authCodes = $this->getDoctrine()->getRepository(AuthCode::class)->findByClient($this->client);
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->client->getPublicId(),
            'client_secret' => $this->client->getSecret() . 'a',
            'redirect_uri' => 'https://unicorns.pl',
            'code' => $authCodes[0]->getToken(),
        ];
        $client = $this->createClient();
        $client->followRedirects();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertStatusCode(400, $client->getResponse());
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

    public function testAutomaticallyGrantForAlreadyAuthorizedClients() {
        $this->makeOAuthAuthorizeRequest();
        $this->makeOAuthAuthorizeRequest([], ['grant' => false]);
        $authCodes = $this->getDoctrine()->getRepository(AuthCode::class)->findByClient($this->client);
        $this->assertCount(2, $authCodes);
    }

    public function testAutomaticallyGrantForAlreadyAuthorizedClientsIfRequestedLessScope() {
        $this->makeOAuthAuthorizeRequest();
        $this->makeOAuthAuthorizeRequest(['scope' => 'account_r'], ['grant' => false]);
        $authCodes = $this->getDoctrine()->getRepository(AuthCode::class)->findByClient($this->client);
        $this->assertCount(2, $authCodes);
    }

    public function testRequiredRegrantingForAlreadyAuthorizedClientsIfRequestedBiggerScope() {
        $this->makeOAuthAuthorizeRequest();
        $this->makeOAuthAuthorizeRequest(['scope' => 'account_rw'], ['grant' => false]);
        $authCodes = $this->getDoctrine()->getRepository(AuthCode::class)->findByClient($this->client);
        $this->assertCount(1, $authCodes);
    }

    public function testRegrantingMergesScopes() {
        $this->makeOAuthAuthorizeRequest();
        $this->makeOAuthAuthorizeRequest(['scope' => 'account_rw']);
        $authCodes = $this->getDoctrine()->getRepository(AuthCode::class)->findByClient($this->client);
        $this->assertCount(2, $authCodes);
        $token = $this->issueTokenBasedOnAuthCode($authCodes[1]);
        $this->assertEquals('account_rw account_r', $token['scope']);
        $user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertCount(1, $user->getApiClientAuthorizations());
        $authorization = $user->getApiClientAuthorizations()[0];
        $this->assertEquals($this->client->getId(), $authorization->getApiClient()->getId());
        $this->assertEquals('account_r offline_access account_rw', $authorization->getScope());
    }

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

    public function testAccessingApiWithExpiredToken() {
        $this->makeOAuthAuthorizeRequest(['scope' => 'account_r']);
        $response = $this->issueTokenBasedOnAuthCode();
        $token = $this->getEntityManager()->find(AccessToken::class, 2);
        EntityUtils::setField($token, 'expiresAt', strtotime('-1hour'));
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $response['access_token'], 'HTTPS' => true]);
        $client->request('GET', '/api/users/current');
        $this->assertStatusCode(401, $client->getResponse());
    }

    public function testRefreshingToken() {
        $this->makeOAuthAuthorizeRequest(['scope' => 'offline_access']);
        $response = $this->issueTokenBasedOnAuthCode();
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->client->getPublicId(),
            'client_secret' => $this->client->getSecret(),
            'refresh_token' => $response['refresh_token'],
        ];
        $client = $this->createClient();
        $client->followRedirects();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertStatusCode(200, $client->getResponse());
        $refreshResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('access_token', $refreshResponse);
    }

    public function testCannotRefreshTokenWhenAppIsUnauthorizedByUser() {
        $this->makeOAuthAuthorizeRequest(['scope' => 'offline_access']);
        $response = $this->issueTokenBasedOnAuthCode();
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $this->client->getPublicId(),
            'client_secret' => $this->client->getSecret(),
            'refresh_token' => $response['refresh_token'],
        ];

        $webapp = $this->createAuthenticatedClient();
        $authorizationRepository = $this->getDoctrine()->getRepository(ApiClientAuthorization::class);
        $authorization = $authorizationRepository->findOneByUserAndApiClient($this->user, $this->client);
        $webapp->apiRequest('DELETE', '/api/oauth-authorized-clients/' . $authorization->getId());
        $this->assertStatusCode(204, $webapp->getResponse());

        $client = $this->createClient();
        $client->followRedirects();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertStatusCode('4XX', $client->getResponse());
    }

    public function testCannotAccessApiWithGivenTokenWhenAppIsUnauthorizedByUser() {
        $this->makeOAuthAuthorizeRequest(['scope' => 'account_r']);
        $response = $this->issueTokenBasedOnAuthCode();

        $webapp = $this->createAuthenticatedClient();
        $authorizationRepository = $this->getDoctrine()->getRepository(ApiClientAuthorization::class);
        $authorization = $authorizationRepository->findOneByUserAndApiClient($this->user, $this->client);
        $webapp->apiRequest('DELETE', '/api/oauth-authorized-clients/' . $authorization->getId());
        $this->assertStatusCode(204, $webapp->getResponse());

        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $response['access_token'], 'HTTPS' => true]);
        $client->followRedirects();
        $client->request('GET', '/api/users/current');
        $this->assertStatusCode(401, $client->getResponse());
    }

    public function testEnablingMqttWhenMqttBrokerScopeGiven() {
        $this->assertFalse($this->user->isMqttBrokerEnabled());
        $this->makeOAuthAuthorizeRequest(['scope' => 'mqtt_broker']);
        $response = $this->issueTokenBasedOnAuthCode();
        $this->assertArrayHasKey('access_token', $response);
        $this->assertArrayNotHasKey('refresh_token', $response);
        $this->assertArrayHasKey('scope', $response);
        $this->assertEquals('mqtt_broker', $response['scope']);
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertTrue($this->user->isMqttBrokerEnabled());
        $this->assertContains('USER-MQTT-SETTINGS-CHANGED:' . $this->user->getId(), SuplaServerMock::$executedCommands);
    }
}

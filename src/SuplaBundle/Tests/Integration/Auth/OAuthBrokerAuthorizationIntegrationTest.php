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
use PHPUnit\Framework\ExpectationFailedException;
use SuplaBundle\Entity\Main\OAuth\AuthCode;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\TargetSuplaCloudRequestForwarder;
use SuplaBundle\Supla\SuplaAutodiscover;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\TestSuplaHttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

/** @small */
class OAuthBrokerAuthorizationIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    /** @var SuplaAutodiscoverMock */
    private $autodiscover;

    /** @var ClientManagerInterface */
    private $clientManager;
    /** @var User */
    private $user;

    public function initializeDatabaseForTests() {
        $this->autodiscover = self::$container->get(SuplaAutodiscover::class);
        $this->clientManager = self::$container->get(ClientManagerInterface::class);
        $this->user = $this->createConfirmedUser();
    }

    public function testDisplaysNormalLoginFormIfLocalClientExists() {
        $localClient = $this->clientManager->createClient();
        $localClient->setName('Local App');
        $this->clientManager->updateClient($localClient);
        $client = $this->createHttpsInsulatedClient();
        $crawler = $client->request('GET', $this->oauthAuthorizeUrl($localClient->getPublicId()));
        $routerView = $crawler->filter('router-view')->getNode(0);
        $askForTargetCloud = $routerView->getAttribute(':ask-for-target-cloud');
        $this->assertEquals('false', $askForTargetCloud);
    }

    public function testDisplaysBrokerLoginFormIfLocalClientDoesNotExist() {
        $client = $this->createHttpsInsulatedClient();
        $crawler = $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $routerView = $crawler->filter('router-view')->getNode(0);
        $askForTargetCloud = $routerView->getAttribute(':ask-for-target-cloud');
        $this->assertEquals('true', $askForTargetCloud);
    }

    public function testDisplaysNormalLoginFormIfLocalClientDoesNotExistButCloudIsNotBroker() {
        SuplaAutodiscoverMock::$isBroker = false;
        self::ensureKernelShutdown();
        $client = self::createClient(['debug' => false], ['HTTPS' => true, 'HTTP_Accept' => 'text/html']);
        $client->followRedirects();
        $crawler = $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $errorPage = $crawler->filter('error-page')->getNode(0);
        $this->assertNotNull($errorPage);
    }

    public function testRedirectsToGivenTargetCloudIfAutodiscoverKnowsIt() {
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_local';
        $client = $this->createHttpsClient(false);
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $client->followRedirect();
        TestSuplaHttpClient::mockHttpRequest('https://target.cloud/.+/server-info', ['version' => '2.3.0']);
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'target.cloud']);
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://target.cloud/oauth/v2/auth?', $targetUrl);
        $this->assertStringContainsString('client_id=1_local', $targetUrl);
        $this->assertStringContainsString('scope=account_r', $targetUrl);
        $this->assertStringContainsString('ad_username=ala%40supla.org', $targetUrl);
        $this->assertStringContainsString('state=some-state', $targetUrl);
    }

    public function testDoesNotRedirectToGivenTargetCloudIfItDoesNotWork() {
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_local';
        $client = $this->createHttpsClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function () {
                return [null, Response::HTTP_SERVICE_UNAVAILABLE];
            };
        $crawler = $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'target.cloud']);
        $routerView = $crawler->filter('router-view')->getNode(0);
        $errorName = $routerView->getAttribute('error');
        $this->assertEquals('private_cloud_fail', $errorName);
    }

    public function testRedirectsToTargetCloudByAutodiscoveredUsername() {
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$userMapping['ala@supla.org'] = 'target.cloud';
        $client = $this->createHttpsClient(false);
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $client->followRedirect();
        $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org']);
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect());
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://target.cloud/oauth/v2/auth?', $targetUrl);
        $this->assertStringContainsString('client_id=1_local', $targetUrl);
        $this->assertStringContainsString('scope=account_r', $targetUrl);
        $this->assertStringContainsString('ad_username=ala%40supla.org', $targetUrl);
        $this->assertStringContainsString('state=some-state', $targetUrl);
    }

    public function testDisplaysErrorIfTargetCloudIsNotRegisteredInAutodiscover() {
        $client = $this->createHttpsInsulatedClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $crawler = $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'target.cloud']);
        $routerView = $crawler->filter('router-view')->getNode(0);
        $errorName = $routerView->getAttribute('error');
        $this->assertEquals('autodiscover_fail', $errorName);
    }

    public function testDisplaysErrorIfTargetCloudUrlIsWrong() {
        $client = $this->createHttpsInsulatedClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $crawler = $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org', 'targetCloud' => 'xxx']);
        $routerView = $crawler->filter('router-view')->getNode(0);
        $errorName = $routerView->getAttribute('error');
        $this->assertEquals('autodiscover_fail', $errorName);
    }

    public function testDisplaysErrorIfUserCannotBeAutodiscovered() {
        $client = $this->createHttpsInsulatedClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        $crawler = $client->request('POST', '/oauth/v2/broker_login', ['_username' => 'ala@supla.org']);
        $routerView = $crawler->filter('router-view')->getNode(0);
        $askForTargetCloud = $routerView->getAttribute('error');
        $this->assertEquals('autodiscover_fail', $askForTargetCloud);
    }

    public function testCreatesNewApiClientForPublicClientInTargetCloudDuringTheFirstRequest() {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'unicorn',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        $client = $this->createHttpsClient(false);
        $client->request('GET', $this->oauthAuthorizeUrl('1_local'));
        $client->followRedirect(); // to login form
        $response = $client->getResponse();
        $this->assertTrue($response->isRedirect()); // to new client id
        $targetUrl = $response->headers->get('Location');
        /** @var \SuplaBundle\Entity\Main\OAuth\ApiClient $createdClient */
        $createdClient = $this->clientManager->findClientBy(['name' => 'unicorn']);
        $this->assertStringContainsString('https://supla.local/oauth/v2/auth?', $targetUrl);
        $this->assertStringContainsString('client_id=' . $createdClient->getPublicId(), $targetUrl);
        $this->assertEquals('Cool app', $createdClient->getDescription());
        $this->assertEquals('1_public', $createdClient->getPublicClientId());
        $this->assertEquals(['https://cool.app'], $createdClient->getRedirectUris());
    }

    public function testForwardsIssueTokenRequestBasedOnAuthCode() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC.' . base64_encode('https://target.cloud'),
            'state' => 'unicorn',
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint, array $data) use ($params, &$targetCalled) {
                $this->assertEquals('https://target.cloud', $address);
                $this->assertEquals('/oauth/v2/token', $endpoint);
                $this->assertEquals('1_local', $data['client_id']);
                $this->assertEquals('target-secret', $data['client_secret']);
                $this->assertEquals($params['code'], $data['code']);
                $this->assertEquals($params['redirect_uri'], $data['redirect_uri']);
                $this->assertEquals($params['state'], $data['state']);
                $targetCalled = true;
                return ['OK', Response::HTTP_OK];
            };
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertTrue($targetCalled);
    }

    public function testMapsPublicIdToTargetItWhenTheTargetIsAlreadyHit() {
        $client = $this->clientManager->createClient();
        $client->setRedirectUris(['https://unicorns.pl']);
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $this->clientManager->updateClient($client);
        $authCode = new AuthCode();
        $authCode->setClient($client);
        $authCode->setUser($this->user);
        $authCode->setScope('account_r');
        $authCode->setExpiresAt(time() + 60);
        $authCode->setRedirectUri('https://unicorns.pl');
        $authCode->setToken('abcd.' . base64_encode('http://supla.local'));
        $this->getEntityManager()->persist($authCode);

        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['http://supla.local']['1_public'] =
            ['clientId' => $client->getPublicId(), 'secret' => $client->getSecret()];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => $authCode->getRedirectUri(),
            'code' => $authCode->getToken(),
            'state' => 'unicorn',
        ];

        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint, array $data) use ($authCode, $client, $params, &$targetCalled) {
                $this->assertEquals('http://supla.local', $address);
                $this->assertEquals('/oauth/v2/token', $endpoint);
                $this->assertEquals($client->getPublicId(), $data['client_id']);
                $this->assertEquals($client->getSecret(), $data['client_secret']);
                $this->assertEquals($authCode->getToken(), $data['code']);
                $this->assertEquals($authCode->getRedirectUri(), $data['redirect_uri']);
                $this->assertEquals($params['state'], $data['state']);
                $targetCalled = true;
                return ['OK', Response::HTTP_OK];
            };
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertTrue($targetCalled);
    }

    public function testReturnsErrorForInvalidClientId() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '2_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://unicorns.pl',
            'code' => 'ABC.' . base64_encode('http://supla.local'),
        ];
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testDoesNotForwardAuthAnywhereIfNotBroker() {
        $this->expectException(ExpectationFailedException::class);
        SuplaAutodiscoverMock::$isBroker = false;
        $this->testForwardsIssueTokenRequestBasedOnAuthCode();
    }

    public function testReturnsErrorIfAutodiscoverDoesNotKnowTargetCloudGivenInAuthCode() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use ($params, &$targetCalled) {
            $targetCalled = true;
            return ['OK', Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertFalse($targetCalled);
        $this->assertFalse($response->isSuccessful());
    }

    public function testReturnsErrorIfPublicSecretDoesNotMatch() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'wrong-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use ($params, &$targetCalled) {
            $targetCalled = true;
            return ['OK', Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertFalse($targetCalled);
        $this->assertFalse($response->isSuccessful());
    }

    public function testReturnsErrorIfPublicClientIdDoesNotExist() {
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use ($params, &$targetCalled) {
            $targetCalled = true;
            return ['OK', Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertFalse($targetCalled);
        $this->assertFalse($response->isSuccessful());
    }

    public function testReturnsErrorForInvalidSyntaxOfAuthCode() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'authorization_code',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'redirect_uri' => 'https://cool.app',
            'code' => 'ABC',
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use ($params, &$targetCalled) {
            $targetCalled = true;
            return ['OK', Response::HTTP_OK];
        };
        $client = $this->createHttpsClient();
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertFalse($targetCalled);
        $this->assertFalse($response->isSuccessful());
    }

    public function testForwardsIssueTokenRequestBasedOnRefreshToken() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'refresh_token' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint, array $data) use ($params, &$targetCalled) {
                $this->assertEquals('https://target.cloud', $address);
                $this->assertEquals('/oauth/v2/token', $endpoint);
                $this->assertEquals('1_local', $data['client_id']);
                $this->assertEquals('target-secret', $data['client_secret']);
                $this->assertEquals($params['grant_type'], 'refresh_token');
                $this->assertEquals($params['refresh_token'], $data['refresh_token']);
                $targetCalled = true;
                return ['OK', Response::HTTP_OK];
            };
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $this->assertTrue($targetCalled);
    }

    public function testForwardsIssueTokenRequestErrorBasedOnRefreshToken() {
        SuplaAutodiscoverMock::$publicClients['1_public'] = ['secret' => 'public-secret'];
        SuplaAutodiscoverMock::$clientMapping['https://target.cloud']['1_public'] = ['clientId' => '1_local', 'secret' => 'target-secret'];
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => '1_public',
            'client_secret' => 'public-secret',
            'refresh_token' => 'ABC.' . base64_encode('https://target.cloud'),
        ];
        $targetCalled = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function () use ($params, &$targetCalled) {
                return [null, Response::HTTP_SERVICE_UNAVAILABLE];
            };
        $client = $this->createHttpsClient(false);
        $client->apiRequest('POST', '/oauth/v2/token', $params);
        $response = $client->getResponse();
        $this->assertStatusCode(503, $response);
    }

    public function testForcesReauthorizationIfUserIsAlreadyLoggedInButHitsPublicId() {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'Unicorn App',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        $client = $this->createHttpsClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_local'));
        /** @var Crawler $crawler */
        $crawler = $client->apiRequest('POST', '/oauth/v2/auth_login', ['_username' => 'supler@supla.org', '_password' => 'supla123']);
        $this->assertCount(1, $crawler->filter('oauth-authorize-form'));
        // now, try again with the public id
        $crawler = $client->request('GET', $this->oauthAuthorizeUrl('1_public'));
        // the broker login form should be displayed
        $routerView = $crawler->filter('router-view')->getNode(0);
        $askForTargetCloud = $routerView->getAttribute(':ask-for-target-cloud');
        $this->assertEquals('true', $askForTargetCloud);
        $clientName = $routerView->getAttribute('client-name');
        $this->assertEquals('Unicorn App', $clientName);
    }

    public function testForcesReauthrozatoinIfUserIsAlreadyLoggedInButHitsIdNotMappedYet() {
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = '1_local';
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['2_public']['clientId'] = '2_local';
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'Unicorn App',
            'description' => 'Cool app',
            'redirectUris' => ['https://cool.app'],
        ];
        SuplaAutodiscoverMock::$publicClients['2_public'] = SuplaAutodiscoverMock::$publicClients['1_public'];
        $client = $this->createHttpsClient();
        $client->request('GET', $this->oauthAuthorizeUrl('1_local'));
        /** @var Crawler $crawler */
        $crawler = $client->apiRequest('POST', '/oauth/v2/auth_login', ['_username' => 'supler@supla.org', '_password' => 'supla123']);
        $this->assertCount(1, $crawler->filter('oauth-authorize-form'));
        // now, try again with the not-mapped-yet client id
        $crawler = $client->request('GET', $this->oauthAuthorizeUrl('2_local'));
        $this->assertStatusCode(200, $client->getResponse());
        // the normal login form with newely mapped client should be displayed
        $routerView = $crawler->filter('router-view')->getNode(0);
        $this->assertNotNull($routerView);
        $askForTargetCloud = $routerView->getAttribute(':ask-for-target-cloud');
        $this->assertEquals('false', $askForTargetCloud);
        $clientName = $routerView->getAttribute('client-name');
        $this->assertEquals('Unicorn App', $clientName);
    }

    /** @large */
    public function testUpdatesMappedClientNameAndDescriptionIfUpdatedInAd() {
        $localClient = $this->clientManager->createClient();
        $localClient->setName('Local App');
        $localClient->setPublicClientId('1_public');
        $this->clientManager->updateClient($localClient);
        SuplaAutodiscoverMock::$clientMapping['https://supla.local']['1_public']['clientId'] = $localClient->getPublicId();
        SuplaAutodiscoverMock::$publicClients['1_public'] = [
            'name' => 'butterfly',
            'description' => ['pl' => 'Spoko apka', 'en' => 'Cooler app'],
            'redirectUris' => ['https://cooler.app'],
        ];
        $client = $this->createHttpsClient();
        $client->request('GET', $this->oauthAuthorizeUrl($localClient->getPublicId()));
        $client->apiRequest('POST', '/oauth/v2/auth_login', ['_username' => 'supler@supla.org', '_password' => 'supla123']);
        $createdClient = $this->clientManager->findClientByPublicId($localClient->getPublicId());
        $this->assertNotNull($createdClient);
        $this->assertEquals('butterfly', $createdClient->getName());
        $this->assertEquals(['pl' => 'Spoko apka', 'en' => 'Cooler app'], $createdClient->getDescription());
        $this->assertEquals(['https://cooler.app'], $createdClient->getRedirectUris());
    }

    private function oauthAuthorizeUrl($clientId, $redirectUri = 'https://app.com/auth', $scope = 'account_r', $responseType = 'code') {
        return '/oauth/v2/auth?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => $scope,
                'response_type' => $responseType,
                'state' => 'some-state',
            ]);
    }
}

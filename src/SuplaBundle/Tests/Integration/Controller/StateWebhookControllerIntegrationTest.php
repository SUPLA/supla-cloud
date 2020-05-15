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

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\StateWebhook;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Repository\StateWebhookRepository;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class StateWebhookControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var ApiClient */
    private $client;

    /** @var User */
    private $user;
    /** @var StateWebhookRepository */
    private $stateWebhookRepository;

    public function initializeDatabaseForTests() {
        $clientManager = $this->container->get(ClientManagerInterface::class);
        $client = $clientManager->createClient();
        $client->setRedirectUris(['https://unicorns.pl']);
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);
        $clientManager->updateClient($client);
        $this->client = $client;
        $this->user = $this->createConfirmedUser();
        $token = new AccessToken();
        $token->setClient($this->client);
        $token->setUser($this->user);
        $token->setToken('ABC');
        $token->setScope('state_webhook');
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
        $this->stateWebhookRepository = $this->container->get('doctrine')->getRepository(StateWebhook::class);
    }

    public function testDoesNotHaveWebhookAtTheBeginning() {
        $hook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->client, $this->user);
        $this->assertNull($hook->getId());
    }

    public function testCreatingStateWebhook() {
        /** @var TestClient $client */
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $hookData = [
            'url' => 'https://unicorns.pl',
            'functions' => ['LIGHTSWITCH'],
            'authToken' => 'XXX',
            'refreshToken' => 'YYY',
            'expiresAt' => time() + 100000,
        ];
        $client->apiRequestV23('PUT', '/api/integrations/state-webhook', $hookData);
        $this->assertStatusCode('2XX', $client->getResponse());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('url', $response);
        $hook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->client, $this->user);
        $this->assertNotNull($hook->getId());
        $this->assertEquals($hookData['url'], EntityUtils::getField($hook, 'url'));
        $this->assertEquals(ChannelFunction::LIGHTSWITCH, EntityUtils::getField($hook, 'functionsIds'));
        $this->assertEquals($hookData['authToken'], EntityUtils::getField($hook, 'authToken'));
        $this->assertEquals($hookData['refreshToken'], EntityUtils::getField($hook, 'refreshToken'));
        $this->assertEquals($hookData['expiresAt'], EntityUtils::getField($hook, 'expiresAt')->getTimestamp());
    }

    /** @depends testCreatingStateWebhook */
    public function testGettingStateWebhook() {
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $client->apiRequestV23('GET', '/api/integrations/state-webhook');
        $this->assertStatusCode(200, $client->getResponse());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('url', $response);
        $this->assertArrayHasKey('expiresAt', $response);
        $this->assertArrayHasKey('functions', $response);
        $this->assertEquals(['LIGHTSWITCH'], $response['functions']);
        $this->assertArrayNotHasKey('functionsIds', $response);
        $this->assertArrayNotHasKey('id', $response);
        $this->assertArrayNotHasKey('authToken', $response);
        $this->assertArrayNotHasKey('refreshToken', $response);
    }

    /** @depends testCreatingStateWebhook */
    public function testUpdatingStateWebhook() {
        /** @var TestClient $client */
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $hookData = [
            'url' => 'https://unicorns2.pl',
            'functions' => ['POWERSWITCH'],
            'authToken' => 'YYY',
            'refreshToken' => 'ZZZ',
            'expiresAt' => time() + 200000,
        ];
        $client->apiRequestV23('PUT', '/api/integrations/state-webhook', $hookData);
        $this->assertStatusCode('2XX', $client->getResponse());
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('url', $response);
        $hook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->client, $this->user);
        $this->assertNotNull($hook->getId());
        $this->assertEquals($hookData['url'], EntityUtils::getField($hook, 'url'));
        $this->assertEquals(ChannelFunction::POWERSWITCH, EntityUtils::getField($hook, 'functionsIds'));
        $this->assertEquals($hookData['authToken'], EntityUtils::getField($hook, 'authToken'));
        $this->assertEquals($hookData['refreshToken'], EntityUtils::getField($hook, 'refreshToken'));
        $this->assertEquals($hookData['expiresAt'], EntityUtils::getField($hook, 'expiresAt')->getTimestamp());
    }

    /** @depends testUpdatingStateWebhook */
    public function testDeletingStateWebhook() {
        /** @var TestClient $client */
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $client->apiRequestV23('DELETE', '/api/integrations/state-webhook');
        $this->assertStatusCode('2XX', $client->getResponse());
        $hook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->client, $this->user);
        $this->assertNull($hook->getId());
    }

    /** @dataProvider invalidStateWebhookRequests */
    public function testInvalidStateWebhookRequests($hookData) {
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $client->apiRequestV23('PUT', '/api/integrations/state-webhook', $hookData);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function invalidStateWebhookRequests() {
        $almostGoodRequest = function (array $override) {
            return array_replace(
                [
                    'url' => 'https://unicorns2.pl',
                    'functions' => ['POWERSWITCH'],
                    'authToken' => 'YYY',
                    'refreshToken' => 'ZZZ',
                    'expiresAt' => time() + 100000,
                ],
                $override
            );
        };
        return [
            [[]],
            [$almostGoodRequest(['expiresAt' => time() + 3600])],
            [$almostGoodRequest(['expiresAt' => time() + 8640000])],
            [$almostGoodRequest(['functions' => []])],
            [$almostGoodRequest(['functions' => ['UNICORN']])],
            [$almostGoodRequest(['authToken' => ''])],
            [$almostGoodRequest(['refreshToken' => ''])],
            [$almostGoodRequest(['url' => 'alamakota.pl'])],
            [$almostGoodRequest(['url' => 'ftp://alamakota.pl'])],
        ];
    }
}

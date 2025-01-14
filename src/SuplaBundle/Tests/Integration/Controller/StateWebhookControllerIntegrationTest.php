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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\StateWebhook;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Repository\StateWebhookRepository;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\Traits\OAuthHelper;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class StateWebhookControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use OAuthHelper;

    /** @var ApiClient */
    private $client;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;
    /** @var StateWebhookRepository */
    private $stateWebhookRepository;

    public function initializeDatabaseForTests() {
        $this->client = $this->createApiClient('123');
        $this->user = $this->createConfirmedUser();
        $this->createAccessToken($this->client, $this->user, 'state_webhook');
        $this->stateWebhookRepository = self::$container->get('doctrine')->getRepository(StateWebhook::class);
    }

    public function testDoesNotHaveWebhookAtTheBeginning() {
        $hook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->client, $this->user);
        $this->assertNull($hook->getId());
    }

    public function testCreatingStateWebhook() {
        /** @var TestClient $client */
        self::ensureKernelShutdown();
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $hookData = [
            'url' => 'https://unicorns.pl',
            'functions' => ['LIGHTSWITCH'],
            'accessToken' => 'XXX',
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
        $this->assertEquals($hookData['accessToken'], EntityUtils::getField($hook, 'accessToken'));
        $this->assertEquals($hookData['refreshToken'], EntityUtils::getField($hook, 'refreshToken'));
        $this->assertEquals($hookData['expiresAt'], EntityUtils::getField($hook, 'expiresAt')->getTimestamp());
    }

    /** @depends testCreatingStateWebhook */
    public function testGettingStateWebhook() {
        self::ensureKernelShutdown();
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
        $this->assertArrayNotHasKey('accessToken', $response);
        $this->assertArrayNotHasKey('refreshToken', $response);
    }

    /** @depends testCreatingStateWebhook */
    public function testUpdatingStateWebhook() {
        self::ensureKernelShutdown();
        /** @var TestClient $client */
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $hookData = [
            'url' => 'https://unicorns2.pl',
            'functions' => ['POWERSWITCH'],
            'accessToken' => 'YYY',
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
        $this->assertEquals($hookData['accessToken'], EntityUtils::getField($hook, 'accessToken'));
        $this->assertEquals($hookData['refreshToken'], EntityUtils::getField($hook, 'refreshToken'));
        $this->assertEquals($hookData['expiresAt'], EntityUtils::getField($hook, 'expiresAt')->getTimestamp());
    }

    /** @depends testUpdatingStateWebhook */
    public function testDeletingStateWebhook() {
        self::ensureKernelShutdown();
        /** @var TestClient $client */
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $client->apiRequestV23('DELETE', '/api/integrations/state-webhook');
        $this->assertStatusCode('2XX', $client->getResponse());
        $hook = $this->stateWebhookRepository->findOrCreateForApiClientAndUser($this->client, $this->user);
        $this->assertNull($hook->getId());
    }

    /** @large */
    public function testCantCreateStateWebhookForNonPublicClient() {
        $client = $this->createApiClient();
        $this->createAccessToken($client, $this->user, 'state_webhook', 'BCD');
        self::ensureKernelShutdown();
        /** @var TestClient $client */
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer BCD', 'HTTPS' => true]);
        $client->followRedirects();
        $hookData = [
            'url' => 'https://unicorns.pl',
            'functions' => ['LIGHTSWITCH'],
            'accessToken' => 'XXX',
            'refreshToken' => 'YYY',
            'expiresAt' => time() + 100000,
        ];
        $client->apiRequestV23('PUT', '/api/integrations/state-webhook', $hookData);
        $this->assertStatusCode(409, $client->getResponse());
    }

    /** @dataProvider invalidStateWebhookRequests */
    public function testInvalidStateWebhookRequests($hookData) {
        self::ensureKernelShutdown();
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $client->apiRequestV23('PUT', '/api/integrations/state-webhook', $hookData);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public static function invalidStateWebhookRequests() {
        $almostGoodRequest = function (array $override) {
            return array_replace(
                [
                    'url' => 'https://unicorns2.pl',
                    'functions' => ['POWERSWITCH'],
                    'accessToken' => 'YYY',
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
            [$almostGoodRequest(['accessToken' => ''])],
            [$almostGoodRequest(['refreshToken' => ''])],
            [$almostGoodRequest(['url' => 'alamakota.pl'])],
            [$almostGoodRequest(['url' => 'ftp://alamakota.pl'])],
        ];
    }
}

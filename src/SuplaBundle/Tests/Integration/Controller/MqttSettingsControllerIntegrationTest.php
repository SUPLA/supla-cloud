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
use SuplaBundle\Entity\Main\OAuth\ApiClientAuthorization;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Repository\ApiClientAuthorizationRepository;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\OAuthHelper;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class MqttSettingsControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use OAuthHelper;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;
    /** @var \SuplaBundle\Entity\Main\OAuth\ApiClient */
    private $client;
    /** @var ApiClientAuthorizationRepository */
    private $apiClientAuthorizationRepository;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->user->setMqttBrokerEnabled(true);
        $this->getEntityManager()->persist($this->user);
        $this->client = $this->createApiClient();
        $this->createAccessToken($this->client, $this->user, 'mqtt_broker');
        $this->apiClientAuthorizationRepository = $this->getDoctrine()->getRepository(ApiClientAuthorization::class);
    }

    public function testGetMqttBrokerSettings() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/settings/mqtt-broker');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('enabled', $content);
        $this->assertArrayHasKey('userEnabled', $content);
    }

    public function testGeneratingMqttPasswordForApiClient() {
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $client->apiRequestV23('POST', '/api/integrations/mqtt-credentials');
        $response = $client->getResponse();
        $this->assertStatusCode('2XX', $response);
        $authorization = $this->apiClientAuthorizationRepository->findOneByUserAndApiClient($this->user, $this->client);
        $pass = EntityUtils::getField($authorization, 'mqttBrokerAuthPassword');
        $this->assertNotNull($pass);
        $content = json_decode($response->getContent(), true);
        return $content['password'];
    }

    /** @depends testGeneratingMqttPasswordForApiClient */
    public function testCheckingPasswordForOAuthAppWithProcedureSucceeds(string $password) {
        $result = $this->getEntityManager()->getConnection()->executeQuery('CALL supla_mqtt_broker_auth(:username, :password);', [
            'username' => $this->user->getShortUniqueId(),
            'password' => $password,
        ]);
        $this->assertTrue(!!$result->fetchOne());
    }

    /** @depends testGeneratingMqttPasswordForApiClient */
    public function testGeneratingMqttPasswordForApiClientAgain() {
        $authorization = $this->apiClientAuthorizationRepository->findOneByUserAndApiClient($this->user, $this->client);
        $originalPass = EntityUtils::getField($authorization, 'mqttBrokerAuthPassword');
        $rawPassword = $this->testGeneratingMqttPasswordForApiClient();
        $authorization = $this->apiClientAuthorizationRepository->findOneByUserAndApiClient($this->user, $this->client);
        $pass = EntityUtils::getField($authorization, 'mqttBrokerAuthPassword');
        $this->assertNotEquals($originalPass, $pass);
        $this->testCheckingPasswordForOAuthAppWithProcedureSucceeds($rawPassword);
    }

    public function test409WhenUserDisabledMqtt() {
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->user->setMqttBrokerEnabled(false);
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ABC', 'HTTPS' => true]);
        $client->followRedirects();
        $client->apiRequestV23('POST', '/api/integrations/mqtt-credentials');
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
    }

    protected static function createClient(array $options = [], array $server = []) {
        self::ensureKernelShutdown();
        return parent::createClient($options, $server);
    }
}

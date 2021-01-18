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

namespace SuplaBundle\Tests\Integration\User;

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\User;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

/** @small */
class UserMqttSettingsIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->initializeDatabaseWithMigrations();
        $this->user = $this->createConfirmedUser();
    }

    public function testEnablingMqttSupport() {
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest('PATCH', '/api/users/current', ['action' => 'change:mqttBrokerEnabled', 'enabled' => true]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertTrue($user->isMqttBrokerEnabled());
        $this->assertTrue($user->hasMqttBrokerAuthPassword());
        $password = $response->headers->get('SUPLA-MQTT-password');
        $this->assertNotNull($password);
        return $password;
    }

    /** @depends testEnablingMqttSupport */
    public function testNotifyingSuplaServerAboutMqttSettingsChanged() {
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $this->assertEquals('USER-MQTT-SETTINGS-CHANGED:1', SuplaServerMock::$executedCommands[0]);
    }

    /** @depends testEnablingMqttSupport */
    public function testCheckingPasswordWithProcedure(string $password) {
        $result = $this->getEntityManager()->getConnection()->executeQuery('CALL supla_mqtt_broker_auth(:username, :password);', [
            'username' => $this->user->getShortUniqueId(),
            'password' => $password,
        ]);
        $this->assertTrue(!!$result->fetchColumn());
    }

    /** @depends testEnablingMqttSupport */
    public function testChangingMqttSettingsTooQuickly() {
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest('PATCH', '/api/users/current', ['action' => 'change:mqttBrokerEnabled', 'enabled' => false]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertContains('too quickly', $body['message']);
    }

    /** @depends testEnablingMqttSupport */
    public function testDisablingMqttSupport() {
        TestTimeProvider::setTime('+15 minutes');
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest('PATCH', '/api/users/current', ['action' => 'change:mqttBrokerEnabled', 'enabled' => false]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
    }

    /** @depends testEnablingMqttSupport */
    public function testCheckingPasswordWithProcedureWhenMqttDisabled(string $password) {
        $result = $this->getEntityManager()->getConnection()->executeQuery('CALL supla_mqtt_broker_auth(:username, :password);', [
            'username' => $this->user->getShortUniqueId(),
            'password' => $password,
        ]);
        $this->assertFalse(!!$result->fetchColumn());
    }

    /** @depends testDisablingMqttSupport */
    public function testEnablingMqttSupportAgainDoesNotChangeThePassword() {
        $user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $oldPassword = EntityUtils::getField($user, 'mqttBrokerAuthPassword');
        TestTimeProvider::setTime('+30 minutes');
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest('PATCH', '/api/users/current', ['action' => 'change:mqttBrokerEnabled', 'enabled' => true]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $newPassword = EntityUtils::getField($user, 'mqttBrokerAuthPassword');
        $this->assertEquals($oldPassword, $newPassword);
    }

    /** @depends testEnablingMqttSupport */
    public function testGeneratingNewMqttPassword() {
        $user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $oldPassword = EntityUtils::getField($user, 'mqttBrokerAuthPassword');
        /** @var TestClient $client */
        $client = self::createAuthenticatedClient();
        $client->apiRequest('PATCH', '/api/users/current', ['action' => 'change:mqttBrokerPassword']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertTrue($user->hasMqttBrokerAuthPassword());
        $encodedPassword = EntityUtils::getField($user, 'mqttBrokerAuthPassword');
        $this->assertNotNull($encodedPassword);
        $this->assertNotEquals($oldPassword, $encodedPassword);
        $password = $response->headers->get('SUPLA-MQTT-password');
        $this->assertNotNull($password);
    }
}

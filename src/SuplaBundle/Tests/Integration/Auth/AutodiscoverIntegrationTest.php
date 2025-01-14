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

use AppKernel;
use SuplaBundle\Auth\AutodiscoverPublicClientStub;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\SettingsString;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\InstanceSettings;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Supla\SuplaAutodiscover;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\TestMailer;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\TestSuplaHttpClient;

/**
 * For these tests to run, you need to launch your local instance of SUPLA Autodiscover from https://github.com/SUPLA/supla-autodiscover
 * The directories supla-cloud and supla-autodiscover should be next to each other (in the same parent dir).
 * Run AD with php7.4 -S localhost:8010 -t web web/app.php
 * Then, update app/config/config_local.yml so the "Real" Autodiscover is being used in the tests instead of the Mocked one:
 */
/*
supla:
  autodiscover_url: http://localhost:8010
parameters:
  act_as_broker_cloud: true
  supla_protocol: http
services:
  SuplaBundle\Supla\SuplaAutodiscover: '@SuplaBundle\Supla\SuplaAutodiscoverReal'
*/

/**
 * These tests are disabled by default because of the demand for the specific environment. To run them, change the group name below
 * (this group name is excluded in the app/phpunit.xml configuration file).
 * @group AutodiscoverIntegrationTest
 */
class AutodiscoverIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;

    const AD_PROJECT_PATH = AppKernel::VAR_PATH . '/../../supla-autodiscover';

    /** @var SuplaAutodiscover */
    private $autodiscover;
    private $clientId;
    private $clientSecret;

    /** @before */
    public function clearState() {
        $this->getDoctrine()->getRepository(SettingsString::class)->clearValue(InstanceSettings::TARGET_TOKEN);
        @unlink(SuplaAutodiscover::PUBLIC_CLIENTS_SAVE_PATH);
        @unlink(SuplaAutodiscover::BROKER_CLOUDS_SAVE_PATH);
        $path = realpath(self::AD_PROJECT_PATH);
        exec("$path/vendor/bin/phpunit -c $path/tests --filter testInvalidUrl", $output); // clears the database
        $this->autodiscover = self::$container->get(SuplaAutodiscover::class);
        $this->executeAdCommand('public-clients:create');
        $publicClientConfigPath = $path . '/var/public-clients/0001.yml';
        $config = file_get_contents($publicClientConfigPath);
        $config = str_replace('enabled: false', 'enabled: true', $config);
        preg_match('#clientId: (.+)#', $config, $matches);
        $this->clientId = trim($matches[1]);
        preg_match('#secret: (.+)#', $config, $matches);
        $this->clientSecret = trim($matches[1]);
        file_put_contents($publicClientConfigPath, $config);
        $this->executeAdCommand('public-clients:update 1');
    }

    public function testRegisteringTargetCloud() {
        $result = $this->executeAdCommand("target-clouds:registration-tokens:issue http://supla.local local@supla.org");
        $this->assertCount(3, $result);
        $command = $result[2];
        $this->assertStringStartsWith('php bin/console supla:register-target-cloud ', $command);
        $result = $this->executeCommand(substr($command, strlen('php bin/console ')));
        $this->assertStringContainsString('correctly', $result);
        @chmod(SuplaAutodiscover::PUBLIC_CLIENTS_SAVE_PATH, 0777);
    }

    public function testSettingBrokerIps() {
        $this->testRegisteringTargetCloud();
        $this->treatAsBroker();
        $randomIp = long2ip(rand(0, "4294967295"));
        $success = $this->autodiscover->setBrokerIpAddresses([$randomIp, '2.3.4.5']);
        $this->assertTrue($success);
        $brokerClouds = $this->autodiscover->getBrokerClouds();
        $this->assertEquals([$randomIp, '2.3.4.5'], $brokerClouds[0]['ips']);
        $this->assertEquals($randomIp, $brokerClouds[0]['ip']);
    }

    public function testRegisteringUserInAd() {
        $this->registerUser();
        $user = $this->getEntityManager()->find(User::class, 1);
        $this->getEntityManager()->remove($user); // let's pretend the user does not exist here
        $this->getEntityManager()->flush();
        $server = $this->autodiscover->getAuthServerForUser('adtest@supla.org');
        $this->assertFalse($server->isLocal());
    }

    public function testQueryingUserInfoAsBroker() {
        $this->testRegisteringUserInAd();
        $userData = [
            'email' => 'adtest@supla.org',
            'regulationsAgreed' => true,
            'password' => 'alamakota',
            'timezone' => 'Europe/Warsaw',
        ];
        $client = $this->createClient();
        $client->apiRequest('POST', '/api/register-account', $userData);
        $this->assertStatusCode(409, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($content['accountEnabled']);
    }

    public function testDeletingUserDeletesItInAd() {
        $client = $this->registerUser();
        $result = $this->executeCommand('supla:delete-user adtest@supla.org', $client);
        $this->assertStringContainsString('has been deleted', $result);
        $server = $this->autodiscover->getAuthServerForUser('adtest@supla.org');
        $this->assertTrue($server->isLocal());
    }

    public function testGetTargetCloudClientId() {
        $this->testRegisteringTargetCloud();
        $this->treatAsBroker();
        $targetCloud = new TargetSuplaCloud('http://supla.local', true);
        $localClientId = $this->autodiscover->getTargetCloudClientId($targetCloud, $this->clientId);
        $this->assertNotNull($localClientId);
        return $localClientId;
    }

    public function testGetPublicIdBasedOnMappedId() {
        $localClientId = $this->testGetTargetCloudClientId();
        $publicId = $this->autodiscover->getPublicIdBasedOnMappedId($localClientId);
        $this->assertEquals($publicId, $this->clientId);
    }

    public function testUpdateTargetCloudCredentials() {
        $localClientId = $this->testGetTargetCloudClientId();
        $clientMock = $this->createMock(ApiClient::class);
        $clientMock->method('getPublicId')->willReturn('1_local');
        $clientMock->method('getSecret')->willReturn('XXX');
        $this->autodiscover->updateTargetCloudCredentials($localClientId, $clientMock);
        $publicId = $this->autodiscover->getPublicIdBasedOnMappedId('1_local');
        $this->assertEquals($publicId, $this->clientId);
    }

    public function testFetchTargetCloudClientSecret() {
        $this->testUpdateTargetCloudCredentials();
        $client = new AutodiscoverPublicClientStub($this->clientId);
        $client->setSecret($this->clientSecret);
        $data = $this->autodiscover->fetchTargetCloudClientSecret($client, new TargetSuplaCloud('http://supla.local', true));
        $this->assertEquals('XXX', $data['secret']);
        $this->assertEquals('1_local', $data['mappedClientId']);
    }

    public function testIssueRegistrationTokenForTargetCloud() {
        $this->testRegisteringTargetCloud();
        $this->treatAsBroker();
        $targetCloud = new TargetSuplaCloud('http://supla2.local');
        $token = $this->autodiscover->issueRegistrationTokenForTargetCloud($targetCloud, 'some@email.com');
        $this->assertNotNull($token);
    }

    public function testGetPublicClient() {
        $this->testRegisteringTargetCloud();
        $clientData = $this->autodiscover->getPublicClient($this->clientId);
        $this->assertNotNull($clientData);
        $this->assertEquals('New Public Client', $clientData['name']);
    }

    public function testRegisteringAnotherTargetCloudThroughBroker() {
        $this->testRegisteringTargetCloud();
        $this->treatAsBroker();
        TestSuplaHttpClient::mockHttpRequest('http://supla.private.pl/api/v2.3.0/server-info', ['cloudVersion' => '2.3.0']);
        $userData = [
            'email' => 'chief@supla.org',
            'targetCloud' => 'supla.private.pl',
        ];
        $client = $this->createClient();
        $client->apiRequest('POST', '/api/register-target-cloud', $userData);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $responseBody = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $responseBody);
        $token = $responseBody['token'];
        $tempLocalSuplaCloud = new TargetSuplaCloud('http://supla.private.pl', true);
        $originalSuplaCloud = EntityUtils::getField($this->autodiscover, 'localSuplaCloud');
        EntityUtils::setField($this->autodiscover, 'localSuplaCloud', $tempLocalSuplaCloud);
        $instanceToken = $this->autodiscover->registerTargetCloud($token);
        EntityUtils::setField($this->autodiscover, 'localSuplaCloud', $originalSuplaCloud);
        $this->assertIsString($instanceToken);
    }

    public function testRegisteringAnotherTargetCloudThroughBrokerTwice() {
        $this->testRegisteringAnotherTargetCloudThroughBroker();
        TestSuplaHttpClient::mockHttpRequest('http://supla.private.pl/api/v2.3.0/server-info', ['cloudVersion' => '2.3.0']);
        $userData = [
            'email' => 'chief@supla.org',
            'targetCloud' => 'supla.private.pl',
        ];
        $client = $this->createClient();
        $client->apiRequest('POST', '/api/register-target-cloud', $userData);
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
    }

    public function testUnregisteringTargetCloud() {
        $this->testRegisteringAnotherTargetCloudThroughBroker();
        $client = $this->createClient();
        $client->apiRequest('POST', '/api/remove-target-cloud', ['email' => 'chief@supla.org', 'targetCloud' => 'supla.private.pl']);
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
        $this->flushMessagesQueue($client);
        $this->assertCount(1, TestMailer::getMessages());
        $message = TestMailer::getMessages()[0];
        preg_match('#confirm-target-cloud-deletion/([0-9]+)/([^\?]+)#', $message->getHtmlBody(), $match);
        $this->assertCount(3, $match);
        [, $targetCloudId, $token] = $match;
        $client->apiRequest('DELETE', "/api/remove-target-cloud/$targetCloudId/$token");
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
    }

    public function testCanReregisterTargetCloudAfterUnregistration() {
        $this->testUnregisteringTargetCloud();
        TestSuplaHttpClient::mockHttpRequest('http://supla.private.pl/api/v2.3.0/server-info', ['cloudVersion' => '2.3.0']);
        $userData = [
            'email' => 'chief@supla.org',
            'targetCloud' => 'supla.private.pl',
        ];
        $client = $this->createClient();
        $client->apiRequest('POST', '/api/register-target-cloud', $userData);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
    }

    private function registerUser(): TestClient {
        $this->testRegisteringTargetCloud();
        $this->treatAsBroker();
        $userData = [
            'email' => 'adtest@supla.org',
            'regulationsAgreed' => true,
            'password' => 'supla123',
            'timezone' => 'Europe/Warsaw',
        ];
        $client = $this->createClient();
        $client->apiRequest('POST', '/api/register', $userData);
        $this->assertStatusCode(201, $client->getResponse());
        return $client;
    }

    private function treatAsBroker(string $targetCloudUrl = 'http://supla.local') {
        $this->executeAdCommand("target-clouds:update $targetCloudUrl --set-broker --no-interaction");
    }

    private function executeAdCommand(string $command): array {
        $path = realpath(self::AD_PROJECT_PATH) . '/autodiscover';
        exec("php $path $command", $output);
        return $output;
    }
}

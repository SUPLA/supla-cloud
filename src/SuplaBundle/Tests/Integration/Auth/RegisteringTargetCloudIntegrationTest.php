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

use SuplaBundle\Entity\User;
use SuplaBundle\Model\TargetSuplaCloudRequestForwarder;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpFoundation\Response;

/** @small */
class RegisteringTargetCloudIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;

    public function initializeDatabaseForTests() {
    }

    public function testRegistertingTargetCloud() {
        $client = $this->createHttpsClient();
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint) use (&$targetCalled) {
                $this->assertEquals('https://supla.private.pl', $address);
                $this->assertEquals('server-info', $endpoint);
                $targetCalled = true;
                return [['cloudVersion' => '2.3.0'], Response::HTTP_OK];
            };

        $client->apiRequestV23('POST', '/api/register-target-cloud', ['email' => 'chief@supla.org', 'targetCloud' => 'supla.private.pl']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $body);
    }

    public function testRegisteringTargetIfInvalidDomain() {
        $client = $this->createHttpsClient();
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint) use (&$targetCalled) {
                $this->assertEquals('https://supla.private.pl', $address);
                $this->assertEquals('server-info', $endpoint);
                $targetCalled = true;
                return [null, Response::HTTP_NOT_FOUND];
            };

        $client->apiRequestV23('POST', '/api/register-target-cloud', ['email' => 'chief@supla.org', 'targetCloud' => 'supla.private.pl']);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $body);
        $this->assertContains('not available', $body['message']);
    }

    public function testRegisteringTargetIfDomainWithSuplaName() {
        $client = $this->createHttpsClient();
        $client->apiRequestV23('POST', '/api/register-target-cloud', ['email' => 'chief@supla.org', 'targetCloud' => 'supla-cloud.pl']);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $body);
        $this->assertContains('You cannot use SUPLA', $body['message']);
    }

    public function testRegisteringObsoleteCloud() {
        $client = $this->createHttpsClient();
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint) use (&$targetCalled) {
                $this->assertEquals('https://supla.private.pl', $address);
                $this->assertEquals('server-info', $endpoint);
                $targetCalled = true;
                return [['cloudVersion' => '2.2.0'], Response::HTTP_OK];
            };

        $client->apiRequestV23('POST', '/api/register-target-cloud', ['email' => 'chief@supla.org', 'targetCloud' => 'supla.private.pl']);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $body);
        $this->assertContains('2.3.0', $body['message']);
    }

    public function testAdNotAvailable() {
        SuplaAutodiscoverMock::mockResponse('target-cloud-registration-token', [], 503, 'POST');
        $client = $this->createHttpsClient();
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint) use (&$targetCalled) {
                $this->assertEquals('https://supla.private.pl', $address);
                $this->assertEquals('server-info', $endpoint);
                $targetCalled = true;
                return [['cloudVersion' => '2.3.0'], Response::HTTP_OK];
            };

        $client->apiRequestV23('POST', '/api/register-target-cloud', ['email' => 'chief@supla.org', 'targetCloud' => 'supla.private.pl']);
        $response = $client->getResponse();
        $this->assertStatusCode(503, $response);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $body);
        $this->assertContains('Could not contact Autodiscover', $body['message']);
    }

    public function testAdFails() {
        SuplaAutodiscoverMock::mockResponse('target-cloud-registration-token', [], 500, 'POST');
        $client = $this->createHttpsClient();
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint) use (&$targetCalled) {
                $this->assertEquals('https://supla.private.pl', $address);
                $this->assertEquals('server-info', $endpoint);
                $targetCalled = true;
                return [['cloudVersion' => '2.3.0'], Response::HTTP_OK];
            };

        $client->apiRequestV23('POST', '/api/register-target-cloud', ['email' => 'chief@supla.org', 'targetCloud' => 'supla.private.pl']);
        $response = $client->getResponse();
        $this->assertStatusCode(500, $response);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $body);
        $this->assertContains('Could not contact Autodiscover', $body['message']);
    }

    public function testTargetCloudAlreadyRegistered() {
        SuplaAutodiscoverMock::mockResponse(
            'target-cloud-registration-token',
            ['error' => 'Target cloud with given URL is already registered.'],
            409,
            'POST'
        );
        $client = $this->createHttpsClient();
        TargetSuplaCloudRequestForwarder::$requestExecutor =
            function (string $address, string $endpoint) use (&$targetCalled) {
                $this->assertEquals('https://supla.private.pl', $address);
                $this->assertEquals('server-info', $endpoint);
                $targetCalled = true;
                return [['cloudVersion' => '2.3.0'], Response::HTTP_OK];
            };

        $client->apiRequestV23('POST', '/api/register-target-cloud', ['email' => 'chief@supla.org', 'targetCloud' => 'supla.private.pl']);
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $body);
        $this->assertContains('already registered', $body['message']);
    }
}

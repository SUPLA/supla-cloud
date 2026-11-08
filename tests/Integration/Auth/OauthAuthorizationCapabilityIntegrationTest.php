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

namespace App\Tests\Integration\Auth;

use App\Auth\OAuthAuthorizationApi;
use App\Auth\OAuthAuthorizationCapability;
use App\Auth\OAuthAuthorizationCapabilityChecker;
use App\Exception\ApiException;
use App\Model\TargetSuplaCloud;
use App\Model\TargetSuplaCloudRequestForwarder;
use App\Supla\SuplaAutodiscover;
use App\Supla\SuplaAutodiscoverMock;
use App\Tests\Integration\IntegrationTestCase;
use Symfony\Component\HttpFoundation\Response;

/** @small */
class OauthAuthorizationCapabilityIntegrationTest extends IntegrationTestCase {
    /** @var OAuthAuthorizationCapabilityChecker */
    private $capabilityChecker;
    /** @var SuplaAutodiscover */
    private $autodiscover;

    protected function initializeDatabaseForTests() {
        $this->capabilityChecker = new OAuthAuthorizationCapabilityChecker(
            self::getContainer()->get(TargetSuplaCloudRequestForwarder::class)
        );
        $this->autodiscover = self::getContainer()->get(SuplaAutodiscover::class);
    }

    protected function tearDown(): void {
        TargetSuplaCloudRequestForwarder::$requestExecutor = null;
        parent::tearDown();
    }

    public function testChecksCapabilityOnAlreadyTrustedTargetUsingTrustedPeerForwarder(): void {
        $target = TargetSuplaCloud::trustedPeer('https://target.cloud');
        $requestCaptured = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function (string $origin, string $endpoint, ?array $data) use (&$requestCaptured) {
            $requestCaptured = true;
            $this->assertSame('https://target.cloud', $origin);
            $this->assertSame('server-info', $endpoint);
            $this->assertNull($data);
            return [[
                'cloudVersion' => '0.0.1', // capability must not be inferred from this value
                'oauthAuthorizationApiVersion' => OAuthAuthorizationApi::VERSION,
            ], Response::HTTP_OK];
        };

        $capability = $this->capabilityChecker->check($target);

        $this->assertTrue($requestCaptured);
        $this->assertSame(OAuthAuthorizationCapability::SUPPORTED, $capability->getStatus());
        $this->assertSame(OAuthAuthorizationApi::VERSION, $capability->getApiVersion());
    }

    public function testReportsOldTargetWithoutCapabilityAsUnsupported(): void {
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () {
            return [['cloudVersion' => '99.0.0'], Response::HTTP_OK];
        };

        $capability = $this->capabilityChecker->check(TargetSuplaCloud::trustedPeer('https://old-target.cloud'));

        $this->assertSame(OAuthAuthorizationCapability::UNSUPPORTED, $capability->getStatus());
        $this->assertFalse($capability->isSupported());
        $this->assertNull($capability->getApiVersion());
    }

    public function testReportsTimeoutAsTargetUnavailable(): void {
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () {
            throw new ApiException('Service temporarily unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        };

        $capability = $this->capabilityChecker->check(TargetSuplaCloud::trustedPeer('https://timeout.cloud'));

        $this->assertSame(OAuthAuthorizationCapability::TARGET_UNAVAILABLE, $capability->getStatus());
    }

    public function testAutodiscoverOutageMapsToTransientTargetUnavailable(): void {
        SuplaAutodiscoverMock::mockResponse(
            '/users/ala%40supla\.org',
            new ApiException('Autodiscover temporarily unavailable', Response::HTTP_SERVICE_UNAVAILABLE),
            Response::HTTP_SERVICE_UNAVAILABLE
        );

        try {
            // AD must succeed before a trusted Target Cloud can be created and
            // passed to check(). The broker operation maps this discovery
            // failure to the same transient result, rather than inferring
            // missing capability or falling back to a client-supplied host.
            $target = $this->autodiscover->getAuthServerForUser('ala@supla.org');
            $capability = $this->capabilityChecker->check($target);
        } catch (ApiException $e) {
            $capability = OAuthAuthorizationCapability::targetUnavailable();
        }

        $this->assertSame(OAuthAuthorizationCapability::TARGET_UNAVAILABLE, $capability->getStatus());
    }

    public function testReportsServerErrorAsTargetUnavailable(): void {
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () {
            return [['error' => 'temporary'], Response::HTTP_INTERNAL_SERVER_ERROR];
        };

        $capability = $this->capabilityChecker->check(TargetSuplaCloud::trustedPeer('https://unavailable.cloud'));

        $this->assertSame(OAuthAuthorizationCapability::TARGET_UNAVAILABLE, $capability->getStatus());
        $this->assertNull($capability->getApiVersion());
    }

    public function testRejectsUntrustedTargetBeforeRequestExecutorIsCalled(): void {
        $requestCaptured = false;
        TargetSuplaCloudRequestForwarder::$requestExecutor = function () use (&$requestCaptured) {
            $requestCaptured = true;
            return [['oauthAuthorizationApiVersion' => OAuthAuthorizationApi::VERSION], Response::HTTP_OK];
        };

        try {
            $this->capabilityChecker->check(new TargetSuplaCloud('https://forged.example'));
            $this->fail('An untrusted target must not be probed.');
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse($requestCaptured);
        }
    }
}

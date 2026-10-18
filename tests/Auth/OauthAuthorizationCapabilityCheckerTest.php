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

namespace App\Tests\Auth;

use App\Auth\OAuthAuthorizationApi;
use App\Auth\OAuthAuthorizationCapability;
use App\Auth\OAuthAuthorizationCapabilityChecker;
use App\Exception\ApiException;
use App\Model\TargetSuplaCloud;
use App\Model\TargetSuplaCloudRequestForwarder;
use PHPUnit\Framework\TestCase;

class OauthAuthorizationCapabilityCheckerTest extends TestCase {
    public function testSupportedWhenTargetAdvertisesCurrentVersion() {
        $forwarder = $this->createMock(TargetSuplaCloudRequestForwarder::class);
        $forwarder->expects($this->once())
            ->method('getInfo')
            ->with(TargetSuplaCloud::trustedPeer('https://target.cloud'))
            ->willReturn([
                'cloudVersion' => '2.4.1',
                'oauthAuthorizationApiVersion' => OAuthAuthorizationApi::VERSION,
            ]);

        $capability = (new OAuthAuthorizationCapabilityChecker($forwarder))
            ->check(TargetSuplaCloud::trustedPeer('https://target.cloud'));

        $this->assertTrue($capability->isSupported());
        $this->assertSame(OAuthAuthorizationCapability::SUPPORTED, $capability->getStatus());
        $this->assertSame(OAuthAuthorizationApi::VERSION, $capability->getApiVersion());
    }

    public function testUnsupportedWhenTargetDoesNotAdvertiseVersion() {
        $forwarder = $this->createMock(TargetSuplaCloudRequestForwarder::class);
        $forwarder->method('getInfo')
            ->willReturn(['cloudVersion' => '2.3.0']);

        $capability = (new OAuthAuthorizationCapabilityChecker($forwarder))
            ->check(TargetSuplaCloud::trustedPeer('https://target.cloud'));

        $this->assertFalse($capability->isSupported());
        $this->assertSame(OAuthAuthorizationCapability::UNSUPPORTED, $capability->getStatus());
        $this->assertNull($capability->getApiVersion());
    }

    public function testTargetUnavailableOnNetworkFailure() {
        $forwarder = $this->createMock(TargetSuplaCloudRequestForwarder::class);
        $forwarder->method('getInfo')
            ->willThrowException(new ApiException('Service temporarily unavailable', 503));

        $capability = (new OAuthAuthorizationCapabilityChecker($forwarder))
            ->check(TargetSuplaCloud::trustedPeer('https://target.cloud'));

        $this->assertFalse($capability->isSupported());
        $this->assertSame(OAuthAuthorizationCapability::TARGET_UNAVAILABLE, $capability->getStatus());
    }

    public function testTargetUnavailableOnTimeout() {
        $forwarder = $this->createMock(TargetSuplaCloudRequestForwarder::class);
        $forwarder->method('getInfo')
            ->willThrowException(new ApiException('Service temporarily unavailable', 503));

        $capability = (new OAuthAuthorizationCapabilityChecker($forwarder))
            ->check(TargetSuplaCloud::trustedPeer('https://target.cloud'));

        $this->assertSame(OAuthAuthorizationCapability::TARGET_UNAVAILABLE, $capability->getStatus());
    }

    public function testTargetUnavailableWhenServerInfoRequestFails() {
        // e.g. the target answered with 5xx, so the capability is unknown
        $forwarder = $this->createMock(TargetSuplaCloudRequestForwarder::class);
        $forwarder->method('getInfo')->willReturn(null);

        $capability = (new OAuthAuthorizationCapabilityChecker($forwarder))
            ->check(TargetSuplaCloud::trustedPeer('https://target.cloud'));

        $this->assertFalse($capability->isSupported());
        $this->assertSame(OAuthAuthorizationCapability::TARGET_UNAVAILABLE, $capability->getStatus());
    }
}

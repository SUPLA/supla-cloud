<?php

namespace App\Tests\Model;

use App\Model\RealClientIpResolver;
use App\Model\TargetSuplaCloud;
use App\Model\TargetSuplaCloudRequestForwarder;
use App\Model\TrustedPeerDestinationSelector;
use App\Supla\SuplaBrokerHttpClient;
use PHPUnit\Framework\TestCase;

class TargetSuplaCloudRequestForwarderTest extends TestCase {
    /** @before */
    public function resetRequestExecutor(): void {
        TargetSuplaCloudRequestForwarder::$requestExecutor = null;
    }

    public function testGetInfoDoesNotUseBrokerToken(): void {
        $httpClient = $this->createMock(SuplaBrokerHttpClient::class);
        $httpClient->expects($this->once())
            ->method('requestTrusted')
            ->willReturnCallback(function ($url, $payload, &$status) {
                $this->assertSame('https://private.example/api/v2.3.0/server-info', $url);
                $this->assertNull($payload);
                $status = 200;
                return ['cloudVersion' => '2.3.0'];
            });
        $httpClient->expects($this->never())->method('requestTrustedWithStoredToken');

        $forwarder = new TargetSuplaCloudRequestForwarder(
            $httpClient,
            $this->createMock(RealClientIpResolver::class),
            new TrustedPeerDestinationSelector()
        );

        $this->assertSame(['cloudVersion' => '2.3.0'], $forwarder->getInfo(TargetSuplaCloud::trustedPeer('https://private.example')));
    }

    public function testBrokerRequestUsesBrokerToken(): void {
        $httpClient = $this->createMock(SuplaBrokerHttpClient::class);
        $httpClient->expects($this->once())
            ->method('requestTrustedWithStoredToken')
            ->willReturnCallback(function ($url, $payload, &$status, $headers, $method, $headerName) {
                $this->assertSame('https://broker.example/api/v2.3.0/webapp-tokens', $url);
                $this->assertSame(['username' => 'user@example.com', 'password' => 'password'], $payload);
                $this->assertSame('SUPLA-Broker-Token', $headerName);
                $status = 200;
                return ['token' => 'token'];
            });
        $httpClient->expects($this->never())->method('requestTrusted');

        $forwarder = new TargetSuplaCloudRequestForwarder(
            $httpClient,
            $this->createMock(RealClientIpResolver::class),
            new TrustedPeerDestinationSelector()
        );

        $this->assertSame(
            [['token' => 'token'], 200],
            $forwarder->issueWebappToken(TargetSuplaCloud::trustedPeer('https://broker.example'), 'user@example.com', 'password')
        );
    }

    public function testRejectsTargetThatWasNotResolvedByTrustedPeerRegistry(): void {
        $httpClient = $this->createMock(SuplaBrokerHttpClient::class);
        $httpClient->expects($this->never())->method('requestTrusted');

        $forwarder = new TargetSuplaCloudRequestForwarder(
            $httpClient,
            $this->createMock(RealClientIpResolver::class),
            new TrustedPeerDestinationSelector()
        );

        $this->expectException(\InvalidArgumentException::class);
        $forwarder->getInfo(new TargetSuplaCloud('https://forged.example'));
    }
}

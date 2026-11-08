<?php

namespace App\Tests\Model;

use App\Model\RealClientIpResolver;
use App\Model\TargetSuplaCloudRequestForwarder;
use App\Model\TrustedPeerDestinationSelector;
use App\Supla\SuplaBrokerHttpClient;
use PHPUnit\Framework\TestCase;

class TargetSuplaCloudRegistrationProbeTest extends TestCase {
    public function testRegistrationProbeUsesPinnedTransportWithoutBrokerCredentials(): void {
        $httpClient = $this->createMock(SuplaBrokerHttpClient::class);
        $httpClient->expects($this->once())
            ->method('requestTrusted')
            ->with('https://target.example/api/v2.3.0/server-info', null, $this->anything())
            ->willReturnCallback(function ($url, $payload, &$status) {
                $status = 200;
                return ['cloudVersion' => '2.3.0'];
            });
        $httpClient->expects($this->never())->method('requestTrustedWithStoredToken');

        $forwarder = new TargetSuplaCloudRequestForwarder(
            $httpClient,
            $this->createMock(RealClientIpResolver::class),
            new TrustedPeerDestinationSelector()
        );

        $this->assertSame(['cloudVersion' => '2.3.0'], $forwarder->getRegistrationInfo('https://TARGET.example/'));
    }

    /** @dataProvider unsafeOrigins */
    public function testRegistrationProbeRejectsNonCanonicalOrProhibitedOriginsBeforeConnecting(string $origin): void {
        $httpClient = $this->createMock(SuplaBrokerHttpClient::class);
        $httpClient->expects($this->never())->method('requestTrusted');

        $forwarder = new TargetSuplaCloudRequestForwarder(
            $httpClient,
            $this->createMock(RealClientIpResolver::class),
            new TrustedPeerDestinationSelector()
        );

        $this->expectException(\InvalidArgumentException::class);
        $forwarder->getRegistrationInfo($origin);
    }

    /** @return array<string, array{string}> */
    public static function unsafeOrigins(): array {
        return [
            'HTTP' => ['http://target.example'],
            'credentials' => ['https://user:password@target.example'],
            'path' => ['https://target.example/path'],
            'loopback' => ['https://127.0.0.1'],
            'link local' => ['https://169.254.169.254'],
            'IPv6 loopback' => ['https://[::1]'],
        ];
    }
}

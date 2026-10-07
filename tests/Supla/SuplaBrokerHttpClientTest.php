<?php

namespace App\Tests\Supla;

use App\Enums\InstanceSettings;
use App\Repository\SettingsStringRepository;
use App\Supla\SuplaBrokerHttpClient;
use App\Supla\SuplaHttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SuplaBrokerHttpClientTest extends TestCase {
    public function testPlainRequestDoesNotReadOrSendStoredToken(): void {
        $settings = $this->createMock(SettingsStringRepository::class);
        $settings->expects($this->never())->method('hasValue');
        $httpClient = $this->createMock(SuplaHttpClient::class);
        $httpClient->expects($this->once())
            ->method('request')
            ->willReturnCallback(function ($url, $method, $payload, $headers) {
                $this->assertArrayNotHasKey('SUPLA-Broker-Token', $headers);
                return [true, '{}', 200];
            });

        $client = new SuplaBrokerHttpClient($httpClient, $settings, $this->createMock(LoggerInterface::class));
        $client->request('https://private.example/server-info', null, $status);
    }

    public function testStoredTokenIsAddedOnlyByExplicitMethod(): void {
        $settings = $this->createMock(SettingsStringRepository::class);
        $settings->method('hasValue')->with(InstanceSettings::TARGET_TOKEN)->willReturn(true);
        $settings->method('getValue')->with(InstanceSettings::TARGET_TOKEN)->willReturn('registration-token');
        $httpClient = $this->createMock(SuplaHttpClient::class);
        $httpClient->expects($this->once())
            ->method('request')
            ->willReturnCallback(function ($url, $method, $payload, $headers) {
                $this->assertSame('Bearer registration-token', $headers['SUPLA-Broker-Token']);
                return [true, '{}', 200];
            });

        $client = new SuplaBrokerHttpClient($httpClient, $settings, $this->createMock(LoggerInterface::class));
        $client->requestWithStoredToken('https://broker.example/api', null, $status, [], null, 'SUPLA-Broker-Token');
    }
}

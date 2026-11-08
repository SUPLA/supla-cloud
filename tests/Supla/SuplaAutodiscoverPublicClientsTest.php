<?php

namespace App\Tests\Supla;

use App\Model\LocalSuplaCloud;
use App\Model\UserManager;
use App\Repository\SettingsStringRepository;
use App\Supla\SuplaAutodiscover;
use App\Supla\SuplaAutodiscoverMock;
use App\Supla\SuplaAutodiscoverReal;
use App\Supla\SuplaBrokerHttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SuplaAutodiscoverPublicClientsTest extends TestCase {
    private ?string $originalCache;

    protected function setUp(): void {
        $path = SuplaAutodiscover::PUBLIC_CLIENTS_SAVE_PATH;
        $this->originalCache = file_exists($path) ? file_get_contents($path) : null;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    protected function tearDown(): void {
        $path = SuplaAutodiscover::PUBLIC_CLIENTS_SAVE_PATH;
        if ($this->originalCache === null) {
            @unlink($path);
        } else {
            file_put_contents($path, $this->originalCache);
        }
    }

    public function testRealCatalogReplacesMockCatalogAndThenUsesConditionalRequests(): void {
        $localCloud = new LocalSuplaCloud('https://cloud.example');
        $users = $this->createMock(UserManager::class);
        $settings = $this->createMock(SettingsStringRepository::class);
        $http = $this->createMock(SuplaBrokerHttpClient::class);
        $mock = new SuplaAutodiscoverMock($localCloud, $users, new NullLogger(), $settings, $http);
        $mock->getPublicClients();

        $requests = [];
        $http->method('requestWithStoredToken')->willReturnCallback(
            function ($url, $payload, &$status, $headers) use (&$requests) {
                $requests[] = $headers;
                // Real AD compares its catalog's modification time with the
                // supplied date, not with the contents of our local cache.
                if ($headers['If-Modified-Since']) {
                    $status = 304;
                    return null;
                }
                $status = 200;
                return [['clientId' => 'sandbox-public', 'name' => 'Sandbox application']];
            }
        );
        $real = new SuplaAutodiscoverReal('https://ad.example', $localCloud, true, $users, new NullLogger(), $settings, $http);

        $expected = ['clientId' => 'sandbox-public', 'name' => 'Sandbox application'];
        $this->assertSame($expected, $real->getPublicClient('sandbox-public'));
        $this->assertSame($expected, $real->getPublicClient('sandbox-public'));
        $this->assertSame(0, $requests[0]['If-Modified-Since']);
        $this->assertNotEmpty($requests[1]['If-Modified-Since']);
    }

    public function testRealCatalogDoesNotReuseCacheFromAnotherAutodiscoverEndpoint(): void {
        $localCloud = new LocalSuplaCloud('https://cloud.example');
        $users = $this->createMock(UserManager::class);
        $settings = $this->createMock(SettingsStringRepository::class);
        $http = $this->createMock(SuplaBrokerHttpClient::class);
        $http->method('requestWithStoredToken')->willReturnCallback(function ($url, $payload, &$status, $headers) {
            $this->assertSame(0, $headers['If-Modified-Since']);
            $status = 200;
            return [['clientId' => $url, 'name' => 'Application']];
        });
        foreach (['https://first-ad.example', 'https://second-ad.example'] as $url) {
            $real = new SuplaAutodiscoverReal($url, $localCloud, true, $users, new NullLogger(), $settings, $http);
            $this->assertNotNull($real->getPublicClient($url . '/public-clients'));
        }
    }

    public function testLegacyCacheIsRefetchedWithoutConditionalDate(): void {
        file_put_contents(SuplaAutodiscover::PUBLIC_CLIENTS_SAVE_PATH, json_encode([
            'lastFetchedDatetime' => gmdate(DATE_RFC822),
            'clients' => [['clientId' => 'mock-public', 'name' => 'Wrong application']],
        ]));
        $http = $this->createMock(SuplaBrokerHttpClient::class);
        $http->method('requestWithStoredToken')->willReturnCallback(function ($url, $payload, &$status, $headers) {
            $this->assertSame(0, $headers['If-Modified-Since']);
            $status = 200;
            return [['clientId' => 'real-public', 'name' => 'Real application']];
        });
        $real = new SuplaAutodiscoverReal(
            'https://ad.example',
            new LocalSuplaCloud('https://cloud.example'),
            true,
            $this->createMock(UserManager::class),
            new NullLogger(),
            $this->createMock(SettingsStringRepository::class),
            $http
        );
        $this->assertSame('Real application', $real->getPublicClient('real-public')['name']);
    }
}

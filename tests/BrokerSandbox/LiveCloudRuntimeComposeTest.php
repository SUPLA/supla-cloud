<?php

namespace App\Tests\BrokerSandbox;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class LiveCloudRuntimeComposeTest extends TestCase {
    private array $compose;

    protected function setUp(): void {
        $contents = file_get_contents(__DIR__ . '/../../docker-compose.broker-dev.yml');
        self::assertNotFalse($contents);
        $this->compose = Yaml::parse($contents);
    }

    /** @dataProvider liveClouds */
    public function testLiveCloudHasAnIsolatedBrokerDevelopmentRuntime(string $service, int $port): void {
        $cloud = $this->compose['services'][$service];

        self::assertSame($port, $cloud['environment']['APP_PORT']);
        self::assertSame('adreal', $cloud['environment']['APP_ENV']);
        self::assertSame('1', $cloud['environment']['SUPLA_ACT_AS_BROKER_CLOUD']);
        self::assertSame('http://localhost:${BROKER_SANDBOX_AUTODISCOVER_PORT:-8010}', $cloud['environment']['SUPLA_AUTODISCOVER_URL']);
        $portVariable = strtoupper(str_replace('-', '_', $service));
        $url = sprintf('http://localhost:${BROKER_SANDBOX_%s_PORT:-%d}', $portVariable, $port);
        self::assertSame($url, $cloud['environment']['SUPLA_URL']);
        self::assertSame($url, $cloud['environment']['SUPLA_BROKER_PUBLIC_URL']);
        self::assertSame(sprintf('SUPLA_BROKER_SANDBOX_${BROKER_SANDBOX_%s_PORT:-%d}', $portVariable, $port), $cloud['environment']['SESSION_COOKIE_NAME']);
        self::assertSame('smtp://127.0.0.1:1025', $cloud['environment']['MAILER_DSN']);
        self::assertSame('false', $cloud['environment']['SUPLA_MQTT_BROKER_ENABLED']);
        self::assertSame('false', $cloud['environment']['SUPLA_OCR_ENABLED']);

        self::assertContains('broker_sandbox_' . str_replace('-', '_', $service) . '_vendor:/var/www/html/vendor', $cloud['volumes']);
        self::assertContains('broker_sandbox_' . str_replace('-', '_', $service) . '_runtime:/var/www/html/var', $cloud['volumes']);
        self::assertContains('broker_sandbox_' . str_replace('-', '_', $service) . '_assets:/var/www/html/public/dist', $cloud['volumes']);
    }

    public function testLiveCloudsDoNotShareRuntimeVolumesOrCookieNames(): void {
        $cloud1 = $this->compose['services']['cloud-1'];
        $cloud2 = $this->compose['services']['cloud-2'];

        self::assertNotSame($cloud1['environment']['SESSION_COOKIE_NAME'], $cloud2['environment']['SESSION_COOKIE_NAME']);
        $cloud1ManagedVolumes = array_filter($cloud1['volumes'], fn (string $volume): bool => !str_starts_with($volume, '.:'));
        $cloud2ManagedVolumes = array_filter($cloud2['volumes'], fn (string $volume): bool => !str_starts_with($volume, '.:'));

        self::assertEmpty(array_intersect($cloud1ManagedVolumes, $cloud2ManagedVolumes));
    }

    public function testCanonicalUrlsUseTheSamePortOverridesAsPublishedListeners(): void {
        $cloud1 = $this->compose['services']['cloud-1']['environment'];
        $cloud2 = $this->compose['services']['cloud-2']['environment'];
        $network = $this->compose['services']['sandbox-network'];

        self::assertSame('http://localhost:${BROKER_SANDBOX_CLOUD_1_PORT:-8001}', $cloud1['SUPLA_URL']);
        self::assertSame('http://localhost:${BROKER_SANDBOX_CLOUD_2_PORT:-8002}', $cloud2['SUPLA_URL']);
        self::assertSame('http://localhost:${BROKER_SANDBOX_AUTODISCOVER_PORT:-8010}', $cloud1['SUPLA_AUTODISCOVER_URL']);
        self::assertContains('127.0.0.1:${BROKER_SANDBOX_CLOUD_1_PORT:-8001}:8001', $network['ports']);
        self::assertContains('127.0.0.1:${BROKER_SANDBOX_CLOUD_2_PORT:-8002}:8002', $network['ports']);
    }

    public static function liveClouds(): array {
        return [
            ['cloud-1', 8001],
            ['cloud-2', 8002],
        ];
    }
}

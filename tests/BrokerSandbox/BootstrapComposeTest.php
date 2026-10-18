<?php

namespace App\Tests\BrokerSandbox;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class BootstrapComposeTest extends TestCase {
    public function testBootstrapRunsAfterDatabaseAndApplicationServicesAreAvailable(): void {
        $contents = file_get_contents(__DIR__ . '/../../docker-compose.broker-dev.yml');
        self::assertNotFalse($contents);
        $compose = Yaml::parse($contents);

        self::assertArrayHasKey('bootstrap', $compose['services']);
        $bootstrap = $compose['services']['bootstrap'];
        self::assertSame('service_started', $bootstrap['depends_on']['cloud-1']['condition']);
        self::assertSame('service_started', $bootstrap['depends_on']['cloud-2']['condition']);
        self::assertSame('service_started', $bootstrap['depends_on']['cloud-3']['condition']);
        self::assertSame('service_started', $bootstrap['depends_on']['autodiscover']['condition']);
        self::assertContains('broker_sandbox_bootstrap:/var/lib/broker-sandbox', $bootstrap['volumes']);
        self::assertContains('${SUPLA_AUTODISCOVER_PATH:-../supla-autodiscover}:/var/www/autodiscover:ro', $bootstrap['volumes']);
    }

    public function testBootstrapUsesSiblingAutodiscoverSchemasAndVerifiesReadiness(): void {
        $bootstrap = file_get_contents(__DIR__ . '/../../docker/broker-dev/bootstrap');
        self::assertNotFalse($bootstrap);

        self::assertStringContainsString('autodiscover_root=/var/www/autodiscover', $bootstrap);
        self::assertStringContainsString('"$autodiscover_root"/sql/v2_13-v2_14.sql', $bootstrap);
        self::assertStringNotContainsString('"$root"/sql/', $bootstrap);
        self::assertStringContainsString("wait_for_http 'Cloud 1'", $bootstrap);
        self::assertStringContainsString("wait_for_http 'Autodiscover'", $bootstrap);
    }

    public function testBootstrapDerivesFixtureOwnershipFromCreatedUsers(): void {
        $bootstrap = file_get_contents(__DIR__ . '/../../docker/broker-dev/bootstrap');
        self::assertNotFalse($bootstrap);

        self::assertStringContainsString('cloud_user_id', $bootstrap);
        self::assertStringContainsString('verify_cloud_fixture', $bootstrap);
        self::assertStringContainsString('SELECT id FROM supla_user WHERE email', $bootstrap);
        self::assertStringContainsString('has no sample devices', $bootstrap);
        self::assertStringContainsString('has no sample channels', $bootstrap);
        self::assertStringNotContainsString("SELECT t.id, 1", $bootstrap);
        self::assertStringContainsString("awk '/supla:register-target-cloud/ { print \$NF }'", $bootstrap);
    }

    public function testBootstrapCreatesTheFrozenCloudDatabaseBeforeItInitializesCloud3(): void {
        $bootstrap = file_get_contents(__DIR__ . '/../../docker/broker-dev/bootstrap');
        self::assertNotFalse($bootstrap);

        self::assertStringContainsString('CREATE DATABASE IF NOT EXISTS broker_sandbox_cloud_3', $bootstrap);
        self::assertStringContainsString('GRANT ALL PRIVILEGES ON broker_sandbox_cloud_3.*', $bootstrap);
        self::assertStringContainsString('cloud "$service" \'php bin/console supla:initialize --no-interaction\'', $bootstrap);
        self::assertStringContainsString("APP_ENV=dev php bin/console doctrine:fixtures:load --no-interaction --append", $bootstrap);
    }
}

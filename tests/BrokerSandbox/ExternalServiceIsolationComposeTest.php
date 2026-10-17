<?php

namespace App\Tests\BrokerSandbox;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class ExternalServiceIsolationComposeTest extends TestCase {
    private array $compose;

    protected function setUp(): void {
        $contents = file_get_contents(__DIR__ . '/../../docker-compose.broker-dev.yml');
        self::assertNotFalse($contents);
        $this->compose = Yaml::parse($contents);
    }

    /** @dataProvider clouds */
    public function testEveryCloudCapturesMailAndDisablesExternalServices(string $service, int $port): void {
        $environment = $this->compose['services'][$service]['environment'];

        self::assertSame('smtp://127.0.0.1:1025', $environment['MAILER_DSN']);
        self::assertSame('false', $environment['SUPLA_MQTT_BROKER_ENABLED']);
        self::assertSame('false', $environment['SUPLA_OCR_ENABLED']);
        self::assertSame('http://localhost:8010', $environment['SUPLA_AUTODISCOVER_URL']);
        self::assertSame(sprintf('http://localhost:%d', $port), $environment['SUPLA_URL']);
    }

    public function testMailpitIsLocalAndPersistsCapturedMail(): void {
        $mail = $this->compose['services']['mail'];

        self::assertSame('axllent/mailpit:v1.26.2', $mail['image']);
        self::assertSame('/data/mailpit.db', $mail['environment']['MP_DATABASE']);
        self::assertContains('broker_sandbox_mail:/data', $mail['volumes']);
        self::assertContains('127.0.0.1:${BROKER_SANDBOX_MAIL_SMTP_PORT:-1025}:1025', $this->compose['services']['sandbox-network']['ports']);
        self::assertContains('127.0.0.1:${BROKER_SANDBOX_MAIL_UI_PORT:-8025}:8025', $this->compose['services']['sandbox-network']['ports']);
    }

    public function testFrozenCloudHasVersionCompatibleBrokerRuntimeSettings(): void {
        $cloud = $this->compose['services']['cloud-3']['environment'];

        self::assertSame('http://localhost:${BROKER_SANDBOX_CLOUD_3_PORT:-8003}', $cloud['SUPLA_URL']);
        self::assertSame('http://localhost:${BROKER_SANDBOX_CLOUD_3_PORT:-8003}', $cloud['SUPLA_BROKER_PUBLIC_URL']);
        self::assertSame('SUPLA_BROKER_SANDBOX_${BROKER_SANDBOX_CLOUD_3_PORT:-8003}', $cloud['SESSION_COOKIE_NAME']);
    }

    public function testFrozenCloudHasItsOwnMariaDbDatabaseAndLoggingBackend(): void {
        $cloud = $this->compose['services']['cloud-3']['environment'];

        self::assertSame('127.0.0.1', $cloud['DATABASE_HOST']);
        self::assertSame(3306, $cloud['DATABASE_PORT']);
        self::assertSame('broker_sandbox_cloud_3', $cloud['DATABASE_NAME']);
        self::assertSame('broker_sandbox', $cloud['DATABASE_USER']);
        self::assertSame('broker-sandbox', $cloud['DATABASE_PASSWORD']);
        self::assertSame('mariadb-11.7.2', $cloud['DATABASE_VERSION']);
        self::assertSame('mariadb', $cloud['DATABASE_FOR_LOGS']);
    }

    public static function clouds(): array {
        return [
            ['cloud-1', 8001],
            ['cloud-2', 8002],
            ['cloud-3', 8003],
        ];
    }
}

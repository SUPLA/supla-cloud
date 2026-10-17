<?php

namespace App\Tests\Message;

use App\Message\EmailTwigExtension;
use App\Model\LocalSuplaCloud;
use App\Supla\SuplaAutodiscover;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailTwigExtensionTest extends TestCase {
    public function testUsesProductionBrokerUrlByDefault(): void {
        $extension = $this->createExtension();

        self::assertSame('https://cloud.supla.org/reset-password/token?lang=en', $extension->getCloudUrl(['userLocale' => 'en'], 'reset-password/token'));
        self::assertSame('cloud.supla.org', $extension->getCloudHost());
    }

    public function testUsesConfiguredBrokerUrlAndHost(): void {
        $extension = $this->createExtension('http://localhost:8001');

        self::assertSame('http://localhost:8001/reset-password/token?lang=en', $extension->getCloudUrl(['userLocale' => 'en'], 'reset-password/token'));
        self::assertSame('localhost:8001', $extension->getCloudHost());
    }

    public function testKeepsLocalCloudUrlForNonBrokerEmails(): void {
        $extension = $this->createExtension('http://localhost:8001', false);

        self::assertSame('https://local.supla.test/reset-password/token', $extension->getCloudUrl([], 'reset-password/token'));
        self::assertSame('local.supla.test', $extension->getCloudHost());
    }

    private function createExtension(string $brokerUrl = 'https://cloud.supla.org', bool $isBroker = true): EmailTwigExtension {
        $autodiscover = $this->createMock(SuplaAutodiscover::class);
        $autodiscover->method('isBroker')->willReturn($isBroker);
        return new EmailTwigExtension(
            $this->createMock(TranslatorInterface::class),
            new LocalSuplaCloud('https://local.supla.test'),
            $autodiscover,
            $brokerUrl
        );
    }
}

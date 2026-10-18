<?php

namespace App\Tests\Model;

use App\Model\TargetSuplaCloud;
use App\Model\TrustedPeerDestinationSelector;
use PHPUnit\Framework\TestCase;

class TrustedPeerDestinationSelectorTest extends TestCase {
    public function testSelectsCanonicalHttpsOriginFromTrustedPeerRecord(): void {
        $destination = (new TrustedPeerDestinationSelector())->select(
            TargetSuplaCloud::trustedPeer('https://TARGET.example:8443/')
        );

        $this->assertSame('https://target.example:8443', $destination->getOrigin());
    }

    /** @dataProvider untrustedOrigins */
    public function testRejectsUnsafeTrustedPeerOrigins(string $origin): void {
        $this->expectException(\InvalidArgumentException::class);
        TargetSuplaCloud::trustedPeer($origin);
    }

    public static function untrustedOrigins(): array {
        return [
            ['http://target.example'],
            ['https://user:password@target.example'],
            ['https://target.example/other-path'],
            ['https://target.example?redirect=https://forged.example'],
            ['https://127.0.0.1'],
            ['https://169.254.169.254'],
            ['https://[::1]'],
        ];
    }
}

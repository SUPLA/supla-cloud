<?php

namespace App\Tests\Supla;

use App\Supla\SuplaHttpClient;
use PHPUnit\Framework\TestCase;

class SuplaHttpClientTest extends TestCase {
    /** @dataProvider prohibitedTrustedPeerAddresses */
    public function testTrustedPeerRequestRejectsSpecialPurposeAddressBeforeConnecting(string $address): void {
        $this->expectException(\InvalidArgumentException::class);

        (new SuplaHttpClient())->request("https://$address", 'GET', null, [], true);
    }

    public static function prohibitedTrustedPeerAddresses(): array {
        return [
            'IPv4 documentation range' => ['192.0.2.1'],
            'IPv4 reserved range' => ['240.0.0.1'],
            'IPv4 limited broadcast' => ['255.255.255.255'],
            'IPv4 deprecated relay anycast range' => ['192.88.99.1'],
            'IPv6 documentation range' => ['[2001:db8::1]'],
            'IPv6 transition range' => ['[2002:c000:0201::1]'],
            'IPv6 NAT64 well-known prefix' => ['[64:ff9b::c000:201]'],
            'IPv6 documentation range allocated from 3fff' => ['[3fff::1]'],
        ];
    }
}

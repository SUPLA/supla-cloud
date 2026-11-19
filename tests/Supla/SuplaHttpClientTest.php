<?php

namespace App\Tests\Supla;

use App\Supla\SuplaHttpClient;
use App\Supla\TrustedPeerDnsResolver;
use PHPUnit\Framework\TestCase;

class SuplaHttpClientTest extends TestCase {
    public function testDnsResolutionUsesTheSystemResolver(): void {
        $addresses = (new TrustedPeerDnsResolver())->resolve('localhost');

        $this->assertNotEmpty($addresses);
        $this->assertContains('127.0.0.1', $addresses);
    }

    public function testIpLiteralDoesNotRequireDns(): void {
        $this->assertSame(['8.8.8.8'], (new TrustedPeerDnsResolver())->resolve('8.8.8.8'));
    }

    /** @dataProvider invalidDnsAnswers */
    public function testInvalidDnsAnswersAreRejectedBeforeConnecting(array $addresses): void {
        $resolver = new class ($addresses) extends TrustedPeerDnsResolver {
            public function __construct(private array $addresses) {
            }

            public function resolve(string $host): array {
                return $this->addresses;
            }
        };
        $this->expectException(\InvalidArgumentException::class);
        (new SuplaHttpClient($resolver))->request('https://peer.example/', 'GET', null, [], true);
    }

    public static function invalidDnsAnswers(): array {
        return [
            'empty' => [[]],
            'mixed public and private' => [['8.8.8.8', '127.0.0.1']],
        ];
    }

    public function testExhaustedBudgetIsRejectedAfterDns(): void {
        $client = new class extends SuplaHttpClient {
            public function configurationAfterSlowDns(): array {
                return $this->trustedPeerCurlConfiguration('https://peer.example/', ['8.8.8.8'], hrtime(true) - 11000000000);
            }
        };
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('exceeded its time budget');
        $client->configurationAfterSlowDns();
    }

    public function testResolvedProhibitedAddressIsRejectedBeforeLocalHttpFixtureAndIsNotReResolved(): void {
        $listener = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
        $this->assertNotFalse($listener, $errorMessage);
        stream_set_blocking($listener, false);
        $address = stream_socket_get_name($listener, false);
        $port = (int) substr(strrchr($address, ':'), 1);
        $resolver = new class extends TrustedPeerDnsResolver {
            public int $calls = 0;

            public function resolve(string $host): array {
                $this->calls++;
                return $this->calls === 1 ? ['127.0.0.1'] : ['8.8.8.8'];
            }
        };

        try {
            (new SuplaHttpClient($resolver))->request("https://rebind.test:$port/", 'GET', null, [], true);
            $this->fail('Expected the prohibited first resolution to be rejected.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('prohibited', $exception->getMessage());
        }

        $read = [$listener];
        $write = $except = [];
        $this->assertSame(0, stream_select($read, $write, $except, 0, 100000));
        $this->assertSame(1, $resolver->calls);
        fclose($listener);
    }

    public function testValidatedResolutionIsPinnedAndALaterProhibitedAnswerCannotMoveTheDestination(): void {
        $resolver = new class extends TrustedPeerDnsResolver {
            public int $calls = 0;

            public function resolve(string $host): array {
                $this->calls++;
                // The first answer is validated; a later answer attempts to
                // rebind the name to a prohibited loopback address.
                return $this->calls === 1 ? ['93.184.216.34'] : ['127.0.0.1'];
            }
        };
        $client = new class ($resolver) extends SuplaHttpClient {
            /** @var array|null */
            public $observedConfiguration = null;

            protected function trustedPeerCurlConfiguration(string $fullUrl, array $resolvedIps, int $startedAt): array {
                return $this->observedConfiguration = parent::trustedPeerCurlConfiguration($fullUrl, $resolvedIps, $startedAt);
            }

            protected function executeCurlHandle(\CurlHandle $ch): string|false {
                // Hermetic: the pin under test decides the destination, the transfer does not run.
                return 'pinned';
            }
        };

        $client->request('https://rebind.example:8443/server-info', 'GET', null, [], true);

        $this->assertSame(1, $resolver->calls, 'The destination must not be re-resolved once the validated pin is set.');
        $this->assertSame('rebind.example:8443:93.184.216.34', $client->observedConfiguration[0], 'The request must stay pinned to the validated answer.');
        $this->assertSame(3000, $client->observedConfiguration[1]);
        $this->assertGreaterThan(9900, $client->observedConfiguration[2], 'A fast resolution must leave the budget essentially intact for cURL.');
    }

    public function testOverallBudgetStartedBeforeDnsIsCarriedIntoTheCurlTimeouts(): void {
        $resolver = new class extends TrustedPeerDnsResolver {
            public function resolve(string $host): array {
                usleep(1500000);
                return ['93.184.216.34'];
            }
        };
        $client = new class ($resolver) extends SuplaHttpClient {
            /** @var array|null */
            public $observedConfiguration = null;

            protected function trustedPeerCurlConfiguration(string $fullUrl, array $resolvedIps, int $startedAt): array {
                return $this->observedConfiguration = parent::trustedPeerCurlConfiguration($fullUrl, $resolvedIps, $startedAt);
            }

            protected function executeCurlHandle(\CurlHandle $ch): string|false {
                return 'pinned';
            }
        };

        $startedAt = microtime(true);
        $client->request('https://slow-dns.example:8443/server-info', 'GET', null, [], true);
        $elapsed = microtime(true) - $startedAt;

        $this->assertNotNull($client->observedConfiguration);
        $this->assertLessThan(9500, $client->observedConfiguration[2], 'cURL must receive only the budget remaining after DNS.');
        $this->assertGreaterThan(7000, $client->observedConfiguration[2], 'DNS time must be subtracted from the overall budget, not reset it.');
        $this->assertLessThan(3.0, $elapsed);
    }

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

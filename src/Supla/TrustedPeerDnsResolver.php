<?php

namespace App\Supla;

/** Resolves all peer addresses in a child process with a strict deadline. */
class TrustedPeerDnsResolver {
    public const TIMEOUT_SECONDS = 2.0;

    private BoundedProcessRunner $processRunner;

    public function __construct(?BoundedProcessRunner $processRunner = null) {
        $this->processRunner = $processRunner ?? new BoundedProcessRunner();
    }

    /** @return string[] */
    public function resolve(string $host): array {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $script = <<<'PHP'
        $host = getenv('SUPLA_TRUSTED_PEER_HOST');
        $ips = @gethostbynamel($host) ?: [];
        if (function_exists('dns_get_record')) {
            foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $record) {
                if (isset($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }
        echo json_encode(array_values(array_unique($ips)));
PHP;

        $phpCli = PHP_BINDIR . DIRECTORY_SEPARATOR . ('\\' === DIRECTORY_SEPARATOR ? 'php.exe' : 'php');
        if (!is_executable($phpCli) || is_dir($phpCli)) {
            throw new \InvalidArgumentException('Trusted peer DNS resolution requires an available PHP CLI executable.');
        }

        try {
            $output = $this->processRunner->run(
                [$phpCli, '-r', $script],
                self::TIMEOUT_SECONDS,
                ['SUPLA_TRUSTED_PEER_HOST' => $host]
            );
        } catch (\RuntimeException $exception) {
            throw new \InvalidArgumentException('Trusted peer DNS resolution failed or exceeded its deadline.', 0, $exception);
        }

        $ips = json_decode($output, true);
        if (!is_array($ips) || array_filter($ips, fn($ip) => !is_string($ip))) {
            throw new \InvalidArgumentException('Trusted peer DNS resolution returned an invalid response.');
        }
        return array_values(array_unique($ips));
    }
}

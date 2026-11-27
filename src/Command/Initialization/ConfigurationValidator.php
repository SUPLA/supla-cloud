<?php

namespace App\Command\Initialization;

class ConfigurationValidator {
    public function __construct(private string $suplaUrl, private string $suplaHost) {
    }

    public function validate(): void {
        if (!self::isOrigin($this->suplaUrl)) {
            throw new \InvalidArgumentException(
                'Invalid SUPLA_URL: expected a URL containing only a scheme, host and optional port (e.g. https://cloud.example.com:443).'
            );
        }
        if (!self::isOrigin('https://' . $this->suplaHost)) {
            throw new \InvalidArgumentException(
                'Invalid SUPLA_HOST_ADDRESS: expected only a host and optional port, without a scheme (e.g. cloud.example.com:443).'
            );
        }
    }

    private static function isOrigin(string $url): bool {
        $parts = parse_url($url);
        if (
            !filter_var($url, FILTER_VALIDATE_URL)
            || !$parts
            || !isset($parts['scheme'], $parts['host'])
            || array_diff(array_keys($parts), ['scheme', 'host', 'port'])
            || str_ends_with($url, ':')
            || (isset($parts['port']) && $parts['port'] === 0)
        ) {
            return false;
        }
        return true;
    }
}

<?php

namespace App\Supla;

/** Resolves peer addresses using the system resolver. */
class TrustedPeerDnsResolver {
    /** @return string[] */
    public function resolve(string $host): array {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $ips = @gethostbynamel($host) ?: [];
        if (function_exists('dns_get_record')) {
            foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $record) {
                if (isset($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }
        return array_values(array_unique($ips));
    }
}

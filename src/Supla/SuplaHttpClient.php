<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace App\Supla;

class SuplaHttpClient {
    public function request(
        string $fullUrl,
        ?string $method = null,
        ?array $payload = null,
        array $headers = [],
        bool $trustedPeer = false
    ): array {
        $resolvedIps = $trustedPeer ? $this->resolveTrustedPeer($fullUrl) : [];
        $ch = curl_init($fullUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($payload) {
            $content = json_encode($payload);
            $headers = array_merge(['Content-Type' => 'application/json', 'Content-Length' => strlen($content)], $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }
        $headers = array_map(function ($headerName, $headerValue) {
            return "$headerName: $headerValue";
        }, array_keys($headers), $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($trustedPeer) {
            $parts = parse_url($fullUrl);
            curl_setopt($ch, CURLOPT_RESOLVE, [sprintf('%s:%d:%s', $parts['host'], $parts['port'] ?? 443, implode(',', $resolvedIps))]);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=PHPUNIT'); // uncomment to enable XDEBUG debugging in dev
        $result = curl_exec($ch);
        $responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $requestSuccess = curl_errno($ch) === 0;
        curl_close($ch);
        return [$requestSuccess, $result, $responseStatus];
    }

    /** @return string[] */
    private function resolveTrustedPeer(string $url): array {
        $parts = parse_url($url);
        if (!is_array($parts) || ($parts['scheme'] ?? null) !== 'https' || empty($parts['host'])) {
            throw new \InvalidArgumentException('Invalid trusted peer destination.');
        }
        $host = trim($parts['host'], '[]');
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if (function_exists('dns_get_record') && !filter_var($host, FILTER_VALIDATE_IP)) {
            foreach (dns_get_record($host, DNS_AAAA) ?: [] as $record) {
                if (isset($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }
        $ips = array_values(array_unique($ips));
        if (!$ips || count($ips) > 16 || array_filter($ips, fn(string $ip) => $this->isProhibitedIp($ip))) {
            throw new \InvalidArgumentException('Trusted peer resolved to a prohibited address.');
        }
        return $ips;
    }

    private function isProhibitedIp(string $ip): bool {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IANA IPv4 special-purpose address registry. Do not rely on
            // FILTER_FLAG_NO_RES_RANGE: it deliberately permits several
            // documentation and special-purpose networks.
            foreach (
                [
                '0.0.0.0/8', '10.0.0.0/8', '100.64.0.0/10', '127.0.0.0/8',
                '169.254.0.0/16', '172.16.0.0/12', '192.0.0.0/24',
                '192.0.2.0/24', '192.88.99.0/24', '192.175.48.0/24',
                '192.168.0.0/16', '198.18.0.0/15', '198.51.100.0/24',
                '203.0.113.0/24', '224.0.0.0/4', '240.0.0.0/4',
                ] as $range
            ) {
                if ($this->ipInRange($ip, $range)) {
                    return true;
                }
            }
            return false;
        }
        // IPv6 special-purpose and transition ranges can also encode a
        // loopback or private route. Fail closed rather than relying on a
        // resolver's classification of them as globally reachable.
        foreach (
            [
            '::/128', '::1/128', '::ffff:0:0/96', '64:ff9b::/96',
            '64:ff9b:1::/48', '100::/64', '2001::/23', '2001:db8::/32',
            '2002::/16', '3fff::/20', 'fc00::/7', 'fe80::/10', 'ff00::/8',
            ] as $range
        ) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }
        return false;
    }

    private function ipInRange(string $ip, string $range): bool {
        [$network, $bits] = explode('/', $range);
        $ipBytes = inet_pton($ip);
        $networkBytes = inet_pton($network);
        $fullBytes = intdiv((int)$bits, 8);
        $remainingBits = (int)$bits % 8;
        return substr($ipBytes, 0, $fullBytes) === substr($networkBytes, 0, $fullBytes)
            && (!$remainingBits || (ord($ipBytes[$fullBytes]) >> (8 - $remainingBits)) === (ord($networkBytes[$fullBytes]) >> (8 - $remainingBits)));
    }
}

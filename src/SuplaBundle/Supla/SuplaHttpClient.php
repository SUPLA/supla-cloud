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

namespace SuplaBundle\Supla;

class SuplaHttpClient {
    public function request(string $fullUrl, string $method = null, ?array $payload = null, array $headers = []): array {
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=PHPUNIT'); // uncomment to enable XDEBUG debugging in dev
        $result = curl_exec($ch);
        $responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $requestSuccess = curl_errno($ch) === 0;
        curl_close($ch);
        return [$requestSuccess, $result, $responseStatus];
    }
}

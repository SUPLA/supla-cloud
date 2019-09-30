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

use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\ApiVersions;
use Symfony\Component\HttpFoundation\Response;

class SuplaAutodiscoverReal extends SuplaAutodiscover {
    protected function remoteRequest($endpoint, $post = false, &$responseStatus = null, array $headers = [], string $method = null) {
        if (!$this->enabled()) {
            return null;
        }
        $endpointUrl = $this->autodiscoverUrl . $endpoint;
        $ch = curl_init($endpointUrl);
        $method = $method ?: ($post ? 'POST' : 'GET');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers['X-Cloud-Version'] = ApiVersions::LATEST;
        if ($post) {
            $content = json_encode($post);
            $headers = array_merge(['Content-Type' => 'application/json', 'Content-Length' => strlen($content)], $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }
        if (!isset($headers['Authorization']) && file_exists(self::TARGET_CLOUD_TOKEN_SAVE_PATH)) {
            $headers['Authorization'] = 'Bearer ' . file_get_contents(self::TARGET_CLOUD_TOKEN_SAVE_PATH);
        }
        $headers = array_map(function ($headerName, $headerValue) {
            return "$headerName: $headerValue";
        }, array_keys($headers), $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=PHPUNIT'); // uncomment to enable XDEBUG debugging in dev
        $result = curl_exec($ch);
        $responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch) != 0) {
            throw new ApiException('Service temporarily unavailable', Response::HTTP_SERVICE_UNAVAILABLE); // i18n
        }
        curl_close($ch);
        if ($responseStatus >= 200 && $responseStatus <= 304) {
            return json_decode($result, true);
        } elseif ($responseStatus == 404) {
            return false;
        } else {
            throw new ApiException('Service temporarily unavailable', $responseStatus ?: Response::HTTP_SERVICE_UNAVAILABLE); // i18n
        }
    }
}

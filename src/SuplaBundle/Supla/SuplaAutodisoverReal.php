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
use Symfony\Component\HttpFoundation\Response;

class SuplaAutodisoverReal extends SuplaAutodiscover {
    protected function remoteRequest($endpoint, $post = false) {
        if (!$this->enabled()) {
            return null;
        }
        $options = [
            'http' => [ // use key 'http' even if you send the request to https://...
                'header' => "Content-type: application/json\r\n",
                'method' => $post ? 'POST' : 'GET',
                'content' => json_encode($post),
            ],
        ];
        $context = stream_context_create($options);
        $result = @file_get_contents("https://" . $this->autodiscoverUrl . $endpoint, false, $context);
        if ($result) {
            $result = json_decode($result, true);
        } elseif (preg_match("/^HTTP\/1\.1\ 404/", @$http_response_header[0])) {
            return false;
        } else {
            throw new ApiException('Service temporarily unavailable.', Response::HTTP_SERVICE_UNAVAILABLE);
        }
        return $result;
    }
}

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

use SuplaBundle\Model\UserManager;

class SuplaAutodiscoverMock extends SuplaAutodiscover {
    public static $publicClients = [
        '100_public' => [
            'name' => 'Cool app',
            'description' => 'This is very cool mocked public app',
            'redirectUris' => ['https://cool.app'],
        ],
    ];

    public static $clientMapping = [
        'http://supla.local' => [
            '100_public' => '100_local',
        ],
    ];

    public static $userMapping = [
        'user@supla.org' => 'supla.local',
        'user2@supla.org' => 'localhost:81',
    ];

    public function __construct(string $suplaProtocol, UserManager $userManager) {
        parent::__construct(count(self::$userMapping) ? 'mocked-autodiscover' : false, $suplaProtocol, $suplaProtocol . '://supla.local', $userManager);
    }

    protected function remoteRequest($endpoint, $post = false, &$responseStatus = null) {
        if (preg_match('#/users/(.+)#', $endpoint, $match)) {
            $server = self::$userMapping[urldecode($match[1])] ?? null;
            if ($server) {
                $responseStatus = 200;
                return ['server' => $server];
            }
        } elseif (preg_match('#/new-account-server/#', $endpoint)) {
            $responseStatus = 200;
            return ['server' => current(self::$userMapping)];
        } elseif (preg_match('#/mapped-client-id/(.+)/(.+)#', $endpoint, $match)) {
            $domainMaps = self::$clientMapping[urldecode($match[2])] ?? [];
            $mappedClientId = $domainMaps[urldecode($match[1])] ?? null;
            if ($mappedClientId) {
                $responseStatus = 200;
                return ['mapped_client_id' => $mappedClientId];
            }
        } elseif (preg_match('#/mapped-client-data/(.+)/(.+)#', $endpoint, $match)) {
            if ($post) {
                $responseStatus = 204;
                return '';
            } else {
                $responseStatus = 200;
                $domainMaps = self::$clientMapping[urldecode($match[2])] ?? [];
                $publicId = array_search(urldecode($match[1]), $domainMaps);
                return $publicId ? (self::$publicClients[$publicId] ?? []) : [];
            }
        }
        $responseStatus = 404;
        return false;
    }
}

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

use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Model\UserManager;

class SuplaAutodiscoverMock extends SuplaAutodiscover {
    public static $isBroker = true;
    public static $isTarget = true;

    public static $publicClients = [
        '100_public' => [
            'name' => 'SUPLA Scripts',
            'description' => [
                'en' => 'SUPLA on steroids! Web management, thermostats, voice command and notifications 
                              - all of these is possible with SUPLA Scripts integration.',
                'pl' => 'SUPLA on steroids! Zarządzanie przez przegląarkę, termostat, komendy głosowe i powiadomienia 
                              - to wszystko już możliwe dzięki integracji z SUPLA Scripts.',
            ],
            'websiteUrl' => 'https://supla.fracz.com',
            'redirectUris' => ['http://suplascripts.local/auth'],
            'defaultRedirectUri' => 'http://suplascripts.local/auth',
            'secret' => '100-public-secret',
            'defaultScope' => 'account_r channels_r channels_ea',
        ],
        '101_public' => [
            'name' => 'Amazon Alexa',
            'description' => 'Bring voice commands from Alexa to your SUPLA-ish home!',
            'redirectUris' => ['https://cool.app'],
            'secret' => '101-public-secret',
            'defaultScope' => 'account_r channels_r channels_ea',
        ],
        '102_public' => [
            'name' => 'Google Home',
            'description' => 'Bring voice commands from Google Now service to your SUPLA-ish home!',
            'redirectUris' => ['https://cool.app'],
            'secret' => '102-public-secret',
            'defaultScope' => 'account_r channels_r channels_ea',
        ],
    ];

    public static $clientMapping = [
        'http://supla.local' => [
            '100_public' => ['clientId' => '100_local', 'secret' => '100-local-secret'],
        ],
    ];

    public static $userMapping = [
        'user@supla.org' => 'supla.local',
        'user2@supla.org' => 'localhost:81',
    ];

    public function __construct(LocalSuplaCloud $localSuplaCloud, UserManager $userManager) {
        parent::__construct(
            count(self::$userMapping) ? 'mocked-autodiscover' : false,
            $localSuplaCloud,
            self::$isBroker,
            $userManager
        );
    }

    public function isBroker(): bool {
        return self::$isBroker;
    }

    public function isTarget(): bool {
        return self::$isTarget;
    }

    protected function remoteRequest($endpoint, $post = false, &$responseStatus = null, array $headers = []) {
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
            $mapping = $domainMaps[urldecode($match[1])] ?? [];
            $mappedClientId = $mapping['clientId'] ?? null;
            if ($mappedClientId) {
                $responseStatus = 200;
                return ['mappedClientId' => $mappedClientId];
            }
        } elseif (preg_match('#/mapped-client-public-id/(.+)/(.+)#', $endpoint, $match)) {
            $responseStatus = 200;
            $domainMaps = self::$clientMapping[urldecode($match[2])] ?? [];
            $targetMapping = array_filter($domainMaps, function ($mapping) use ($match) {
                return $mapping['clientId'] == urldecode($match[1]);
            });
            $publicId = $targetMapping ? key($targetMapping) : null;
            return $publicId ? ['publicClientId' => $publicId] : null;
        } elseif (preg_match('#/mapped-client-credentials/(.+)/(.+)#', $endpoint, $match)) {
            $responseStatus = 204;
            return '';
        } elseif (preg_match('#/mapped-client-secret/(.+)/(.+)#', $endpoint, $match)) {
            $publicId = urldecode($match[1]);
            $secret = $post['secret'];
            if (isset(self::$publicClients[$publicId]) && self::$publicClients[$publicId]['secret'] == $secret) {
                $domainMaps = self::$clientMapping[urldecode($match[2])] ?? [];
                return $domainMaps[$publicId] ?? [];
            }
        } elseif (preg_match('#/(register-target-cloud)|(target-cloud-registration-token)#', $endpoint, $match)) {
            $randomBytes = bin2hex(random_bytes(20));
            $token = preg_replace('#[1lI0O]#', '', preg_replace('#[^a-zA-Z0-9]#', '', base64_encode($randomBytes)));
            return ['token' => $token];
        } elseif (preg_match('#/public-clients#', $endpoint, $match)) {
            $responseStatus = 200;
            return array_values(array_map(function ($client, $id) {
                unset($client['secret']);
                $client['id'] = $id;
                return $client;
            }, self::$publicClients, array_keys(self::$publicClients)));
        }
        $responseStatus = 404;
        return false;
    }

    public static function clear($shouldBeEnabled = true) {
        self::$userMapping = [];
        self::$clientMapping = [];
        self::$publicClients = [];
        self::$isBroker = true;
        self::$isTarget = true;
        if ($shouldBeEnabled) {
            self::$userMapping['user@supla.org'] = 'supla.local';
        }
    }
}

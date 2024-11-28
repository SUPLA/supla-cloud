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

use Psr\Log\LoggerInterface;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\SettingsStringRepository;

class SuplaAutodiscoverMock extends SuplaAutodiscover {
    public static $mockedResponses = [];

    public static $isBroker = true;
    public static $isTarget = true;
    public static $requests = [];

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

    public function __construct(
        LocalSuplaCloud $localSuplaCloud,
        UserManager $userManager,
        LoggerInterface $logger,
        SettingsStringRepository $settingsStringRepository,
        SuplaBrokerHttpClient $brokerHttpClient
    ) {
        parent::__construct(
            count(self::$userMapping) ? 'mocked-autodiscover' : false,
            $localSuplaCloud,
            self::$isBroker,
            $userManager,
            $logger,
            $settingsStringRepository,
            $brokerHttpClient
        );
    }

    public function isBroker(): bool {
        return self::$isBroker;
    }

    public function isTarget(): bool {
        return self::$isTarget;
    }

    protected function remoteRequest($endpoint, $post = false, &$responseStatus = null, array $headers = [], string $method = null) {
        $method = $method ?: ($post ? 'POST' : 'GET');
        self::$requests[] = ['endpoint' => $endpoint, 'post' => $post, 'headers' => $headers];
        foreach (self::$mockedResponses as $mockedEndpoint => $responseSpec) {
            if (preg_match("#$mockedEndpoint#", $endpoint) && $method === $responseSpec['method']) {
                unset(self::$mockedResponses[$mockedEndpoint]);
                $responseStatus = $responseSpec['responseStatus'];
                return $responseSpec['response'];
            }
        }
        if (preg_match('#/users/?(.*)#', $endpoint, $match)) {
            if ($method == 'DELETE') {
                $responseStatus = 204;
                return;
            }
            if ($method == 'POST') {
                $responseStatus = 201;
                return;
            }
            $server = self::$userMapping[urldecode($match[1])] ?? null;
            if ($server) {
                $responseStatus = 200;
                return ['server' => $server];
            }
        } elseif (preg_match('#/new-account-server/#', $endpoint)) {
            $responseStatus = 200;
            return ['server' => current(self::$userMapping)];
        } elseif (preg_match('#/mapped-client/(.+)/(.+)#', $endpoint, $match)) {
            $domainMaps = self::$clientMapping[urldecode($match[2])] ?? [];
            $publicId = urldecode($match[1]);
            $mapping = $domainMaps[$publicId] ?? [];
            $mappedClientId = $mapping['clientId'] ?? null;
            if ($post) {
                $secret = $post['secret'];
                if (isset(self::$publicClients[$publicId]) && self::$publicClients[$publicId]['secret'] == $secret) {
                    if (isset($domainMaps[$publicId])) {
                        return ['mappedClientId' => $mappedClientId, 'secret' => $domainMaps[$publicId]['secret']];
                    }
                }
            } elseif ($mappedClientId) {
                $responseStatus = 200;
                return ['mappedClientId' => $mappedClientId];
            }
        } elseif (preg_match('#/mapped-client-public-id/(.+)#', $endpoint, $match)) {
            $responseStatus = 200;
            $domainMaps = self::$clientMapping[$this->localSuplaCloud->getAddress()] ?? [];
            $targetMapping = array_filter($domainMaps, function ($mapping) use ($match) {
                return $mapping['clientId'] == urldecode($match[1]);
            });
            $publicId = $targetMapping ? key($targetMapping) : null;
            return $publicId ? ['publicClientId' => $publicId] : null;
        } elseif (preg_match('#/mapped-client-credentials/(.+)#', $endpoint, $match)) {
            $responseStatus = 204;
            return '';
        } elseif (preg_match('#/(register-target-cloud)|(target-cloud-registration-token)#', $endpoint, $match)) {
            $randomBytes = bin2hex(random_bytes(20));
            $token = preg_replace('#[1lI0O]#', '', preg_replace('#[^a-zA-Z0-9]#', '', base64_encode($randomBytes)));
            $responseStatus = 201;
            return ['token' => $token];
        } elseif (preg_match('#/public-clients#', $endpoint, $match)) {
            $responseStatus = 200;
            return array_values(array_map(function ($client, $id) {
                unset($client['secret']);
                $client['id'] = $id;
                $client['clientId'] = $id;
                return $client;
            }, self::$publicClients, array_keys(self::$publicClients)));
        } elseif (preg_match('#/broker-clouds#', $endpoint, $match)) {
            return [
                ['id' => 1, 'url' => 'https://broker1.supla', 'ip' => '127.0.0.2', 'ips' => ['127.0.0.2']],
                ['id' => 2, 'url' => 'https://broker2.supla', 'ip' => '127.0.0.3', 'ips' => ['127.0.0.3']],
                ['id' => 3, 'url' => 'https://supla.local', 'ip' => '127.0.0.1', 'ips' => ['127.0.0.1']],
            ];
        } elseif (preg_match('#/about#', $endpoint, $match)) {
            $responseStatus = 200;
            $authorization = $headers['Authorization'] ?? '';
            if ($authorization === 'Bearer BROKER') {
                return ['isBroker' => true, 'isTarget' => true];
            } elseif ($authorization === 'Bearer TARGET') {
                return ['isBroker' => false, 'isTarget' => true];
            } elseif ($authorization) {
                $responseStatus = 403;
                return null;
            } else {
                return ['isBroker' => false, 'isTarget' => false];
            }
        } elseif (preg_match('#/esp-updates#', $endpoint, $match)) {
            return [self::sampleEspUpdate(), self::sampleEspUpdate()];
        } elseif (preg_match('#/set-broker-ip-addresses#', $endpoint, $match)) {
            $responseStatus = 204;
            return null;
        } elseif (preg_match('#/unlock-device#', $endpoint, $match)) {
            $responseStatus = 200;
            return ['unlock_code' => md5(microtime(true))];
        }
        $responseStatus = 404;
        return false;
    }

    public static function clear(bool $shouldBeBroker = true, bool $shouldBeTarget = true) {
        self::$userMapping = [];
        self::$clientMapping = [];
        self::$publicClients = [];
        self::$isBroker = $shouldBeBroker;
        self::$isTarget = $shouldBeTarget || $shouldBeBroker;
        self::$requests = [];
        self::$userMapping['user@supla.org'] = 'supla.local';
    }

    public static function mockResponse(string $endpoint, $response = [], int $responseStatus = 200, string $method = 'GET') {
        self::$mockedResponses[$endpoint] = [
            'method' => $method,
            'response' => $response,
            'responseStatus' => $responseStatus,
        ];
    }

    public static function sampleEspUpdate(array $data = []): array {
        $defaults = [
            'device_id' => 0,
            'device_name' => 'ZAMEL ROW-01',
            'platform' => 1,
            'latest_software_version' => '2.7.' . rand(0, 100),
            'fparam1' => 3,
            'fparam2' => 5,
            'fparam3' => 7,
            'fparam4' => 9,
            'protocols' => 1,
            'host' => 'www.acsoftware.pl',
            'port' => 80,
            'path' => 'support/get_esp_firmware.php?file=zam_srw_03_user2.2048_DIO.new.5.bin',
        ];
        return array_replace($defaults, $data);
    }
}

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
        } elseif (preg_match('#/weather-data/cities#', $endpoint, $match)) {
            $responseStatus = 200;
            return [
                ['id' => 1, 'name' => 'Warszawa', 'country' => 'PL'],
                ['id' => 2, 'name' => 'Kraków', 'country' => 'PL'],
                ['id' => 3, 'name' => 'Gdańsk', 'country' => 'PL'],
                ['id' => 4, 'name' => 'Wrocław', 'country' => 'PL'],
            ];
        } elseif (preg_match('#/weather-data#', $endpoint, $match)) {
            $responseStatus = 200;
            return array_map(function ($cityId) {
                return [
                    'id' => $cityId,
                    'fetchedAt' => (new \DateTime())->format(\DateTime::ATOM),
                    'weather' => [
                        'temp' => rand(0, 3000) / 100,
                        'feelsLike' => rand(0, 3000) / 100,
                        'pressure' => rand(970, 1030),
                        'humidity' => rand(20, 80),
                        'visibility' => rand(500, 10000),
                        'windSpeed' => rand(0, 100) / 10,
                        'windGust' => rand(0, 100) / 10,
                        'clouds' => rand(0, 100),
                        'rainMmh' => rand(0, 50),
                        'snowMmh' => rand(0, 50),
                        'airCo' => rand(100, 1000) / 10,
                        'airNo' => rand(100, 1000) / 10,
                        'airNo2' => rand(100, 1000) / 10,
                        'airO3' => rand(100, 1000) / 10,
                        'airPm10' => rand(100, 1000) / 10,
                        'airPm25' => rand(100, 1000) / 10,
                    ],
                ];
            }, $post['cityIds']);
        } elseif (preg_match('#/energy-price-forecast#', $endpoint, $match)) {
            $today = date('Y-m-d');
            $tommorrow = date('Y-m-d', strtotime('+1 day'));
            // @codingStandardsIgnoreStart
            return json_decode(str_replace('2025-06-11', $today, str_replace('2025-06-12', $tommorrow, '[{"dateFrom":"2025-06-11T00:00:00+02:00","dateTo":"2025-06-11T00:14:59+02:00","rce":454.31},{"dateFrom":"2025-06-11T00:15:00+02:00","dateTo":"2025-06-11T00:29:59+02:00","rce":454.31},{"dateFrom":"2025-06-11T00:30:00+02:00","dateTo":"2025-06-11T00:44:59+02:00","rce":454.31},{"dateFrom":"2025-06-11T00:45:00+02:00","dateTo":"2025-06-11T00:59:59+02:00","rce":454.31},{"dateFrom":"2025-06-11T01:00:00+02:00","dateTo":"2025-06-11T01:14:59+02:00","rce":443.75},{"dateFrom":"2025-06-11T01:15:00+02:00","dateTo":"2025-06-11T01:29:59+02:00","rce":443.75},{"dateFrom":"2025-06-11T01:30:00+02:00","dateTo":"2025-06-11T01:44:59+02:00","rce":443.75},{"dateFrom":"2025-06-11T01:45:00+02:00","dateTo":"2025-06-11T01:59:59+02:00","rce":443.75},{"dateFrom":"2025-06-11T02:00:00+02:00","dateTo":"2025-06-11T02:14:59+02:00","rce":410.23},{"dateFrom":"2025-06-11T02:15:00+02:00","dateTo":"2025-06-11T02:29:59+02:00","rce":410.23},{"dateFrom":"2025-06-11T02:30:00+02:00","dateTo":"2025-06-11T02:44:59+02:00","rce":410.23},{"dateFrom":"2025-06-11T02:45:00+02:00","dateTo":"2025-06-11T02:59:59+02:00","rce":410.23},{"dateFrom":"2025-06-11T03:00:00+02:00","dateTo":"2025-06-11T03:14:59+02:00","rce":386.97},{"dateFrom":"2025-06-11T03:15:00+02:00","dateTo":"2025-06-11T03:29:59+02:00","rce":386.97},{"dateFrom":"2025-06-11T03:30:00+02:00","dateTo":"2025-06-11T03:44:59+02:00","rce":386.97},{"dateFrom":"2025-06-11T03:45:00+02:00","dateTo":"2025-06-11T03:59:59+02:00","rce":386.97},{"dateFrom":"2025-06-11T04:00:00+02:00","dateTo":"2025-06-11T04:14:59+02:00","rce":377.03},{"dateFrom":"2025-06-11T04:15:00+02:00","dateTo":"2025-06-11T04:29:59+02:00","rce":377.03},{"dateFrom":"2025-06-11T04:30:00+02:00","dateTo":"2025-06-11T04:44:59+02:00","rce":377.03},{"dateFrom":"2025-06-11T04:45:00+02:00","dateTo":"2025-06-11T04:59:59+02:00","rce":377.03},{"dateFrom":"2025-06-11T05:00:00+02:00","dateTo":"2025-06-11T05:14:59+02:00","rce":400.03},{"dateFrom":"2025-06-11T05:15:00+02:00","dateTo":"2025-06-11T05:29:59+02:00","rce":400.03},{"dateFrom":"2025-06-11T05:30:00+02:00","dateTo":"2025-06-11T05:44:59+02:00","rce":400.03},{"dateFrom":"2025-06-11T05:45:00+02:00","dateTo":"2025-06-11T05:59:59+02:00","rce":400.03},{"dateFrom":"2025-06-11T06:00:00+02:00","dateTo":"2025-06-11T06:14:59+02:00","rce":484.2},{"dateFrom":"2025-06-11T06:15:00+02:00","dateTo":"2025-06-11T06:29:59+02:00","rce":484.2},{"dateFrom":"2025-06-11T06:30:00+02:00","dateTo":"2025-06-11T06:44:59+02:00","rce":484.2},{"dateFrom":"2025-06-11T06:45:00+02:00","dateTo":"2025-06-11T06:59:59+02:00","rce":484.2},{"dateFrom":"2025-06-11T07:00:00+02:00","dateTo":"2025-06-11T07:14:59+02:00","rce":501.5},{"dateFrom":"2025-06-11T07:15:00+02:00","dateTo":"2025-06-11T07:29:59+02:00","rce":501.5},{"dateFrom":"2025-06-11T07:30:00+02:00","dateTo":"2025-06-11T07:44:59+02:00","rce":501.5},{"dateFrom":"2025-06-11T07:45:00+02:00","dateTo":"2025-06-11T07:59:59+02:00","rce":501.5},{"dateFrom":"2025-06-11T08:00:00+02:00","dateTo":"2025-06-11T08:14:59+02:00","rce":467.76},{"dateFrom":"2025-06-11T08:15:00+02:00","dateTo":"2025-06-11T08:29:59+02:00","rce":467.76},{"dateFrom":"2025-06-11T08:30:00+02:00","dateTo":"2025-06-11T08:44:59+02:00","rce":467.76},{"dateFrom":"2025-06-11T08:45:00+02:00","dateTo":"2025-06-11T08:59:59+02:00","rce":467.76},{"dateFrom":"2025-06-11T09:00:00+02:00","dateTo":"2025-06-11T09:14:59+02:00","rce":406.42},{"dateFrom":"2025-06-11T09:15:00+02:00","dateTo":"2025-06-11T09:29:59+02:00","rce":406.42},{"dateFrom":"2025-06-11T09:30:00+02:00","dateTo":"2025-06-11T09:44:59+02:00","rce":406.42},{"dateFrom":"2025-06-11T09:45:00+02:00","dateTo":"2025-06-11T09:59:59+02:00","rce":406.42},{"dateFrom":"2025-06-11T10:00:00+02:00","dateTo":"2025-06-11T10:14:59+02:00","rce":213.96},{"dateFrom":"2025-06-11T10:15:00+02:00","dateTo":"2025-06-11T10:29:59+02:00","rce":213.96},{"dateFrom":"2025-06-11T10:30:00+02:00","dateTo":"2025-06-11T10:44:59+02:00","rce":213.96},{"dateFrom":"2025-06-11T10:45:00+02:00","dateTo":"2025-06-11T10:59:59+02:00","rce":213.96},{"dateFrom":"2025-06-11T11:00:00+02:00","dateTo":"2025-06-11T11:14:59+02:00","rce":132.22},{"dateFrom":"2025-06-11T11:15:00+02:00","dateTo":"2025-06-11T11:29:59+02:00","rce":132.22},{"dateFrom":"2025-06-11T11:30:00+02:00","dateTo":"2025-06-11T11:44:59+02:00","rce":132.22},{"dateFrom":"2025-06-11T11:45:00+02:00","dateTo":"2025-06-11T11:59:59+02:00","rce":132.22},{"dateFrom":"2025-06-11T12:00:00+02:00","dateTo":"2025-06-11T12:14:59+02:00","rce":28.72},{"dateFrom":"2025-06-11T12:15:00+02:00","dateTo":"2025-06-11T12:29:59+02:00","rce":28.72},{"dateFrom":"2025-06-11T12:30:00+02:00","dateTo":"2025-06-11T12:44:59+02:00","rce":28.72},{"dateFrom":"2025-06-11T12:45:00+02:00","dateTo":"2025-06-11T12:59:59+02:00","rce":28.72},{"dateFrom":"2025-06-11T13:00:00+02:00","dateTo":"2025-06-11T13:14:59+02:00","rce":5.12},{"dateFrom":"2025-06-11T13:15:00+02:00","dateTo":"2025-06-11T13:29:59+02:00","rce":5.12},{"dateFrom":"2025-06-11T13:30:00+02:00","dateTo":"2025-06-11T13:44:59+02:00","rce":5.12},{"dateFrom":"2025-06-11T13:45:00+02:00","dateTo":"2025-06-11T13:59:59+02:00","rce":5.12},{"dateFrom":"2025-06-11T14:00:00+02:00","dateTo":"2025-06-11T14:14:59+02:00","rce":5.99},{"dateFrom":"2025-06-11T14:15:00+02:00","dateTo":"2025-06-11T14:29:59+02:00","rce":5.99},{"dateFrom":"2025-06-11T14:30:00+02:00","dateTo":"2025-06-11T14:44:59+02:00","rce":5.99},{"dateFrom":"2025-06-11T14:45:00+02:00","dateTo":"2025-06-11T14:59:59+02:00","rce":5.99},{"dateFrom":"2025-06-11T15:00:00+02:00","dateTo":"2025-06-11T15:14:59+02:00","rce":16.77},{"dateFrom":"2025-06-11T15:15:00+02:00","dateTo":"2025-06-11T15:29:59+02:00","rce":16.77},{"dateFrom":"2025-06-11T15:30:00+02:00","dateTo":"2025-06-11T15:44:59+02:00","rce":16.77},{"dateFrom":"2025-06-11T15:45:00+02:00","dateTo":"2025-06-11T15:59:59+02:00","rce":16.77},{"dateFrom":"2025-06-11T16:00:00+02:00","dateTo":"2025-06-11T16:14:59+02:00","rce":74.66},{"dateFrom":"2025-06-11T16:15:00+02:00","dateTo":"2025-06-11T16:29:59+02:00","rce":74.66},{"dateFrom":"2025-06-11T16:30:00+02:00","dateTo":"2025-06-11T16:44:59+02:00","rce":74.66},{"dateFrom":"2025-06-11T16:45:00+02:00","dateTo":"2025-06-11T16:59:59+02:00","rce":74.66},{"dateFrom":"2025-06-11T17:00:00+02:00","dateTo":"2025-06-11T17:14:59+02:00","rce":316.72},{"dateFrom":"2025-06-11T17:15:00+02:00","dateTo":"2025-06-11T17:29:59+02:00","rce":316.72},{"dateFrom":"2025-06-11T17:30:00+02:00","dateTo":"2025-06-11T17:44:59+02:00","rce":316.72},{"dateFrom":"2025-06-11T17:45:00+02:00","dateTo":"2025-06-11T17:59:59+02:00","rce":316.72},{"dateFrom":"2025-06-11T18:00:00+02:00","dateTo":"2025-06-11T18:14:59+02:00","rce":454.8},{"dateFrom":"2025-06-11T18:15:00+02:00","dateTo":"2025-06-11T18:29:59+02:00","rce":454.8},{"dateFrom":"2025-06-11T18:30:00+02:00","dateTo":"2025-06-11T18:44:59+02:00","rce":454.8},{"dateFrom":"2025-06-11T18:45:00+02:00","dateTo":"2025-06-11T18:59:59+02:00","rce":454.8},{"dateFrom":"2025-06-11T19:00:00+02:00","dateTo":"2025-06-11T19:14:59+02:00","rce":613.59},{"dateFrom":"2025-06-11T19:15:00+02:00","dateTo":"2025-06-11T19:29:59+02:00","rce":613.59},{"dateFrom":"2025-06-11T19:30:00+02:00","dateTo":"2025-06-11T19:44:59+02:00","rce":613.59},{"dateFrom":"2025-06-11T19:45:00+02:00","dateTo":"2025-06-11T19:59:59+02:00","rce":613.59},{"dateFrom":"2025-06-11T20:00:00+02:00","dateTo":"2025-06-11T20:14:59+02:00","rce":863.38},{"dateFrom":"2025-06-11T20:15:00+02:00","dateTo":"2025-06-11T20:29:59+02:00","rce":863.38},{"dateFrom":"2025-06-11T20:30:00+02:00","dateTo":"2025-06-11T20:44:59+02:00","rce":863.38},{"dateFrom":"2025-06-11T20:45:00+02:00","dateTo":"2025-06-11T20:59:59+02:00","rce":863.38},{"dateFrom":"2025-06-11T21:00:00+02:00","dateTo":"2025-06-11T21:14:59+02:00","rce":705.15},{"dateFrom":"2025-06-11T21:15:00+02:00","dateTo":"2025-06-11T21:29:59+02:00","rce":705.15},{"dateFrom":"2025-06-11T21:30:00+02:00","dateTo":"2025-06-11T21:44:59+02:00","rce":705.15},{"dateFrom":"2025-06-11T21:45:00+02:00","dateTo":"2025-06-11T21:59:59+02:00","rce":705.15},{"dateFrom":"2025-06-11T22:00:00+02:00","dateTo":"2025-06-11T22:14:59+02:00","rce":506.11},{"dateFrom":"2025-06-11T22:15:00+02:00","dateTo":"2025-06-11T22:29:59+02:00","rce":506.11},{"dateFrom":"2025-06-11T22:30:00+02:00","dateTo":"2025-06-11T22:44:59+02:00","rce":506.11},{"dateFrom":"2025-06-11T22:45:00+02:00","dateTo":"2025-06-11T22:59:59+02:00","rce":506.11},{"dateFrom":"2025-06-11T23:00:00+02:00","dateTo":"2025-06-11T23:14:59+02:00","rce":459.85},{"dateFrom":"2025-06-11T23:15:00+02:00","dateTo":"2025-06-11T23:29:59+02:00","rce":459.85},{"dateFrom":"2025-06-11T23:30:00+02:00","dateTo":"2025-06-11T23:44:59+02:00","rce":459.85},{"dateFrom":"2025-06-11T23:45:00+02:00","dateTo":"2025-06-11T23:59:59+02:00","rce":459.85},{"dateFrom":"2025-06-12T00:00:00+02:00","dateTo":"2025-06-12T00:14:59+02:00","rce":440.8,"fixing1":448,"fixing2":422.16},{"dateFrom":"2025-06-12T00:15:00+02:00","dateTo":"2025-06-12T00:29:59+02:00","rce":440.8,"fixing1":448,"fixing2":422.16},{"dateFrom":"2025-06-12T00:30:00+02:00","dateTo":"2025-06-12T00:44:59+02:00","rce":440.8,"fixing1":448,"fixing2":422.16},{"dateFrom":"2025-06-12T00:45:00+02:00","dateTo":"2025-06-12T00:59:59+02:00","rce":440.8,"fixing1":448,"fixing2":422.16},{"dateFrom":"2025-06-12T01:00:00+02:00","dateTo":"2025-06-12T01:14:59+02:00","rce":439.06,"fixing1":437.03,"fixing2":444.5},{"dateFrom":"2025-06-12T01:15:00+02:00","dateTo":"2025-06-12T01:29:59+02:00","rce":439.06,"fixing1":437.03,"fixing2":444.5},{"dateFrom":"2025-06-12T01:30:00+02:00","dateTo":"2025-06-12T01:44:59+02:00","rce":439.06,"fixing1":437.03,"fixing2":444.5},{"dateFrom":"2025-06-12T01:45:00+02:00","dateTo":"2025-06-12T01:59:59+02:00","rce":439.06,"fixing1":437.03,"fixing2":444.5},{"dateFrom":"2025-06-12T02:00:00+02:00","dateTo":"2025-06-12T02:14:59+02:00","rce":435.15,"fixing1":430.37,"fixing2":444.86},{"dateFrom":"2025-06-12T02:15:00+02:00","dateTo":"2025-06-12T02:29:59+02:00","rce":435.15,"fixing1":430.37,"fixing2":444.86},{"dateFrom":"2025-06-12T02:30:00+02:00","dateTo":"2025-06-12T02:44:59+02:00","rce":435.15,"fixing1":430.37,"fixing2":444.86},{"dateFrom":"2025-06-12T02:45:00+02:00","dateTo":"2025-06-12T02:59:59+02:00","rce":435.15,"fixing1":430.37,"fixing2":444.86},{"dateFrom":"2025-06-12T03:00:00+02:00","dateTo":"2025-06-12T03:14:59+02:00","rce":426.74,"fixing1":424.15,"fixing2":431.67},{"dateFrom":"2025-06-12T03:15:00+02:00","dateTo":"2025-06-12T03:29:59+02:00","rce":426.74,"fixing1":424.15,"fixing2":431.67},{"dateFrom":"2025-06-12T03:30:00+02:00","dateTo":"2025-06-12T03:44:59+02:00","rce":426.74,"fixing1":424.15,"fixing2":431.67},{"dateFrom":"2025-06-12T03:45:00+02:00","dateTo":"2025-06-12T03:59:59+02:00","rce":426.74,"fixing1":424.15,"fixing2":431.67},{"dateFrom":"2025-06-12T04:00:00+02:00","dateTo":"2025-06-12T04:14:59+02:00","rce":407.26,"fixing1":410,"fixing2":403.12},{"dateFrom":"2025-06-12T04:15:00+02:00","dateTo":"2025-06-12T04:29:59+02:00","rce":407.26,"fixing1":410,"fixing2":403.12},{"dateFrom":"2025-06-12T04:30:00+02:00","dateTo":"2025-06-12T04:44:59+02:00","rce":407.26,"fixing1":410,"fixing2":403.12},{"dateFrom":"2025-06-12T04:45:00+02:00","dateTo":"2025-06-12T04:59:59+02:00","rce":407.26,"fixing1":410,"fixing2":403.12},{"dateFrom":"2025-06-12T05:00:00+02:00","dateTo":"2025-06-12T05:14:59+02:00","rce":422.51,"fixing1":431.02,"fixing2":405.75},{"dateFrom":"2025-06-12T05:15:00+02:00","dateTo":"2025-06-12T05:29:59+02:00","rce":422.51,"fixing1":431.02,"fixing2":405.75},{"dateFrom":"2025-06-12T05:30:00+02:00","dateTo":"2025-06-12T05:44:59+02:00","rce":422.51,"fixing1":431.02,"fixing2":405.75},{"dateFrom":"2025-06-12T05:45:00+02:00","dateTo":"2025-06-12T05:59:59+02:00","rce":422.51,"fixing1":431.02,"fixing2":405.75},{"dateFrom":"2025-06-12T06:00:00+02:00","dateTo":"2025-06-12T06:14:59+02:00","rce":486.43,"fixing1":485,"fixing2":489.76},{"dateFrom":"2025-06-12T06:15:00+02:00","dateTo":"2025-06-12T06:29:59+02:00","rce":486.43,"fixing1":485,"fixing2":489.76},{"dateFrom":"2025-06-12T06:30:00+02:00","dateTo":"2025-06-12T06:44:59+02:00","rce":486.43,"fixing1":485,"fixing2":489.76},{"dateFrom":"2025-06-12T06:45:00+02:00","dateTo":"2025-06-12T06:59:59+02:00","rce":486.43,"fixing1":485,"fixing2":489.76},{"dateFrom":"2025-06-12T07:00:00+02:00","dateTo":"2025-06-12T07:14:59+02:00","rce":448.08,"fixing1":452.05,"fixing2":442.04},{"dateFrom":"2025-06-12T07:15:00+02:00","dateTo":"2025-06-12T07:29:59+02:00","rce":448.08,"fixing1":452.05,"fixing2":442.04},{"dateFrom":"2025-06-12T07:30:00+02:00","dateTo":"2025-06-12T07:44:59+02:00","rce":448.08,"fixing1":452.05,"fixing2":442.04},{"dateFrom":"2025-06-12T07:45:00+02:00","dateTo":"2025-06-12T07:59:59+02:00","rce":448.08,"fixing1":452.05,"fixing2":442.04},{"dateFrom":"2025-06-12T08:00:00+02:00","dateTo":"2025-06-12T08:14:59+02:00","rce":372.69,"fixing1":378.8,"fixing2":358.04},{"dateFrom":"2025-06-12T08:15:00+02:00","dateTo":"2025-06-12T08:29:59+02:00","rce":372.69,"fixing1":378.8,"fixing2":358.04},{"dateFrom":"2025-06-12T08:30:00+02:00","dateTo":"2025-06-12T08:44:59+02:00","rce":372.69,"fixing1":378.8,"fixing2":358.04},{"dateFrom":"2025-06-12T08:45:00+02:00","dateTo":"2025-06-12T08:59:59+02:00","rce":372.69,"fixing1":378.8,"fixing2":358.04},{"dateFrom":"2025-06-12T09:00:00+02:00","dateTo":"2025-06-12T09:14:59+02:00","rce":179.72,"fixing1":194.69,"fixing2":155.1},{"dateFrom":"2025-06-12T09:15:00+02:00","dateTo":"2025-06-12T09:29:59+02:00","rce":179.72,"fixing1":194.69,"fixing2":155.1},{"dateFrom":"2025-06-12T09:30:00+02:00","dateTo":"2025-06-12T09:44:59+02:00","rce":179.72,"fixing1":194.69,"fixing2":155.1},{"dateFrom":"2025-06-12T09:45:00+02:00","dateTo":"2025-06-12T09:59:59+02:00","rce":179.72,"fixing1":194.69,"fixing2":155.1},{"dateFrom":"2025-06-12T10:00:00+02:00","dateTo":"2025-06-12T10:14:59+02:00","rce":0,"fixing1":0,"fixing2":0},{"dateFrom":"2025-06-12T10:15:00+02:00","dateTo":"2025-06-12T10:29:59+02:00","rce":0,"fixing1":0,"fixing2":0},{"dateFrom":"2025-06-12T10:30:00+02:00","dateTo":"2025-06-12T10:44:59+02:00","rce":0,"fixing1":0,"fixing2":0},{"dateFrom":"2025-06-12T10:45:00+02:00","dateTo":"2025-06-12T10:59:59+02:00","rce":0,"fixing1":0,"fixing2":0},{"dateFrom":"2025-06-12T11:00:00+02:00","dateTo":"2025-06-12T11:14:59+02:00","rce":0.2,"fixing1":-2.12,"fixing2":3.52},{"dateFrom":"2025-06-12T11:15:00+02:00","dateTo":"2025-06-12T11:29:59+02:00","rce":0.2,"fixing1":-2.12,"fixing2":3.52},{"dateFrom":"2025-06-12T11:30:00+02:00","dateTo":"2025-06-12T11:44:59+02:00","rce":0.2,"fixing1":-2.12,"fixing2":3.52},{"dateFrom":"2025-06-12T11:45:00+02:00","dateTo":"2025-06-12T11:59:59+02:00","rce":0.2,"fixing1":-2.12,"fixing2":3.52},{"dateFrom":"2025-06-12T12:00:00+02:00","dateTo":"2025-06-12T12:14:59+02:00","rce":-5.79,"fixing1":-2.12,"fixing2":-11.59},{"dateFrom":"2025-06-12T12:15:00+02:00","dateTo":"2025-06-12T12:29:59+02:00","rce":-5.79,"fixing1":-2.12,"fixing2":-11.59},{"dateFrom":"2025-06-12T12:30:00+02:00","dateTo":"2025-06-12T12:44:59+02:00","rce":-5.79,"fixing1":-2.12,"fixing2":-11.59},{"dateFrom":"2025-06-12T12:45:00+02:00","dateTo":"2025-06-12T12:59:59+02:00","rce":-5.79,"fixing1":-2.12,"fixing2":-11.59},{"dateFrom":"2025-06-12T13:00:00+02:00","dateTo":"2025-06-12T13:14:59+02:00","rce":-14.93,"fixing1":-2,"fixing2":-37.37},{"dateFrom":"2025-06-12T13:15:00+02:00","dateTo":"2025-06-12T13:29:59+02:00","rce":-14.93,"fixing1":-2,"fixing2":-37.37},{"dateFrom":"2025-06-12T13:30:00+02:00","dateTo":"2025-06-12T13:44:59+02:00","rce":-14.93,"fixing1":-2,"fixing2":-37.37},{"dateFrom":"2025-06-12T13:45:00+02:00","dateTo":"2025-06-12T13:59:59+02:00","rce":-14.93,"fixing1":-2,"fixing2":-37.37},{"dateFrom":"2025-06-12T14:00:00+02:00","dateTo":"2025-06-12T14:14:59+02:00","rce":-0.67,"fixing1":-1.43,"fixing2":0.43},{"dateFrom":"2025-06-12T14:15:00+02:00","dateTo":"2025-06-12T14:29:59+02:00","rce":-0.67,"fixing1":-1.43,"fixing2":0.43},{"dateFrom":"2025-06-12T14:30:00+02:00","dateTo":"2025-06-12T14:44:59+02:00","rce":-0.67,"fixing1":-1.43,"fixing2":0.43},{"dateFrom":"2025-06-12T14:45:00+02:00","dateTo":"2025-06-12T14:59:59+02:00","rce":-0.67,"fixing1":-1.43,"fixing2":0.43},{"dateFrom":"2025-06-12T15:00:00+02:00","dateTo":"2025-06-12T15:14:59+02:00","rce":8.68,"fixing1":0.01,"fixing2":22.42},{"dateFrom":"2025-06-12T15:15:00+02:00","dateTo":"2025-06-12T15:29:59+02:00","rce":8.68,"fixing1":0.01,"fixing2":22.42},{"dateFrom":"2025-06-12T15:30:00+02:00","dateTo":"2025-06-12T15:44:59+02:00","rce":8.68,"fixing1":0.01,"fixing2":22.42},{"dateFrom":"2025-06-12T15:45:00+02:00","dateTo":"2025-06-12T15:59:59+02:00","rce":8.68,"fixing1":0.01,"fixing2":22.42},{"dateFrom":"2025-06-12T16:00:00+02:00","dateTo":"2025-06-12T16:14:59+02:00","rce":84.17,"fixing1":100,"fixing2":49.59},{"dateFrom":"2025-06-12T16:15:00+02:00","dateTo":"2025-06-12T16:29:59+02:00","rce":84.17,"fixing1":100,"fixing2":49.59},{"dateFrom":"2025-06-12T16:30:00+02:00","dateTo":"2025-06-12T16:44:59+02:00","rce":84.17,"fixing1":100,"fixing2":49.59},{"dateFrom":"2025-06-12T16:45:00+02:00","dateTo":"2025-06-12T16:59:59+02:00","rce":84.17,"fixing1":100,"fixing2":49.59},{"dateFrom":"2025-06-12T17:00:00+02:00","dateTo":"2025-06-12T17:14:59+02:00","rce":319.43,"fixing1":320,"fixing2":318.35},{"dateFrom":"2025-06-12T17:15:00+02:00","dateTo":"2025-06-12T17:29:59+02:00","rce":319.43,"fixing1":320,"fixing2":318.35},{"dateFrom":"2025-06-12T17:30:00+02:00","dateTo":"2025-06-12T17:44:59+02:00","rce":319.43,"fixing1":320,"fixing2":318.35},{"dateFrom":"2025-06-12T17:45:00+02:00","dateTo":"2025-06-12T17:59:59+02:00","rce":319.43,"fixing1":320,"fixing2":318.35},{"dateFrom":"2025-06-12T18:00:00+02:00","dateTo":"2025-06-12T18:14:59+02:00","rce":446.93,"fixing1":479,"fixing2":382.42},{"dateFrom":"2025-06-12T18:15:00+02:00","dateTo":"2025-06-12T18:29:59+02:00","rce":446.93,"fixing1":479,"fixing2":382.42},{"dateFrom":"2025-06-12T18:30:00+02:00","dateTo":"2025-06-12T18:44:59+02:00","rce":446.93,"fixing1":479,"fixing2":382.42},{"dateFrom":"2025-06-12T18:45:00+02:00","dateTo":"2025-06-12T18:59:59+02:00","rce":446.93,"fixing1":479,"fixing2":382.42},{"dateFrom":"2025-06-12T19:00:00+02:00","dateTo":"2025-06-12T19:14:59+02:00","rce":659.23,"fixing1":680,"fixing2":625.82},{"dateFrom":"2025-06-12T19:15:00+02:00","dateTo":"2025-06-12T19:29:59+02:00","rce":659.23,"fixing1":680,"fixing2":625.82},{"dateFrom":"2025-06-12T19:30:00+02:00","dateTo":"2025-06-12T19:44:59+02:00","rce":659.23,"fixing1":680,"fixing2":625.82},{"dateFrom":"2025-06-12T19:45:00+02:00","dateTo":"2025-06-12T19:59:59+02:00","rce":659.23,"fixing1":680,"fixing2":625.82},{"dateFrom":"2025-06-12T20:00:00+02:00","dateTo":"2025-06-12T20:14:59+02:00","rce":972.72,"fixing1":960,"fixing2":994.29},{"dateFrom":"2025-06-12T20:15:00+02:00","dateTo":"2025-06-12T20:29:59+02:00","rce":972.72,"fixing1":960,"fixing2":994.29},{"dateFrom":"2025-06-12T20:30:00+02:00","dateTo":"2025-06-12T20:44:59+02:00","rce":972.72,"fixing1":960,"fixing2":994.29},{"dateFrom":"2025-06-12T20:45:00+02:00","dateTo":"2025-06-12T20:59:59+02:00","rce":972.72,"fixing1":960,"fixing2":994.29},{"dateFrom":"2025-06-12T21:00:00+02:00","dateTo":"2025-06-12T21:14:59+02:00","rce":735.65,"fixing1":701.1,"fixing2":801.82},{"dateFrom":"2025-06-12T21:15:00+02:00","dateTo":"2025-06-12T21:29:59+02:00","rce":735.65,"fixing1":701.1,"fixing2":801.82},{"dateFrom":"2025-06-12T21:30:00+02:00","dateTo":"2025-06-12T21:44:59+02:00","rce":735.65,"fixing1":701.1,"fixing2":801.82},{"dateFrom":"2025-06-12T21:45:00+02:00","dateTo":"2025-06-12T21:59:59+02:00","rce":735.65,"fixing1":701.1,"fixing2":801.82},{"dateFrom":"2025-06-12T22:00:00+02:00","dateTo":"2025-06-12T22:14:59+02:00","rce":496.85,"fixing1":479.46,"fixing2":525.24},{"dateFrom":"2025-06-12T22:15:00+02:00","dateTo":"2025-06-12T22:29:59+02:00","rce":496.85,"fixing1":479.46,"fixing2":525.24},{"dateFrom":"2025-06-12T22:30:00+02:00","dateTo":"2025-06-12T22:44:59+02:00","rce":496.85,"fixing1":479.46,"fixing2":525.24},{"dateFrom":"2025-06-12T22:45:00+02:00","dateTo":"2025-06-12T22:59:59+02:00","rce":496.85,"fixing1":479.46,"fixing2":525.24},{"dateFrom":"2025-06-12T23:00:00+02:00","dateTo":"2025-06-12T23:14:59+02:00","rce":444.05,"fixing1":443,"fixing2":445.97},{"dateFrom":"2025-06-12T23:15:00+02:00","dateTo":"2025-06-12T23:29:59+02:00","rce":444.05,"fixing1":443,"fixing2":445.97},{"dateFrom":"2025-06-12T23:30:00+02:00","dateTo":"2025-06-12T23:44:59+02:00","rce":444.05,"fixing1":443,"fixing2":445.97},{"dateFrom":"2025-06-12T23:45:00+02:00","dateTo":"2025-06-12T23:59:59+02:00","rce":444.05,"fixing1":443,"fixing2":445.97}]')));
            // @codingStandardsIgnoreEnd
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

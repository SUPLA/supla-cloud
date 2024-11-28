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

namespace SuplaBundle\Model;

use SuplaBundle\Supla\SuplaBrokerHttpClient;
use Symfony\Component\HttpFoundation\Request;

class TargetSuplaCloudRequestForwarder {
    private SuplaBrokerHttpClient $brokerHttpClient;
    private RealClientIpResolver $clientIpResolver;

    /**
     * For the sake of tests.
     * @var callable
     */
    public static $requestExecutor;

    public function __construct(SuplaBrokerHttpClient $brokerHttpClient, RealClientIpResolver $clientIpResolver) {
        $this->brokerHttpClient = $brokerHttpClient;
        $this->clientIpResolver = $clientIpResolver;
    }

    public function issueWebappToken(TargetSuplaCloud $target, string $username, string $password): array {
        return $this->sendRequest($target, 'webapp-tokens', ['username' => $username, 'password' => $password]);
    }

    public function issueOAuthToken(TargetSuplaCloud $target, Request $request, array $mappedClientData): array {
        if ($request->getMethod() === 'POST') {
            $inputData = $request->request->all();
        } else {
            $inputData = $request->query->all();
        }
        $inputData = array_merge($inputData, [
            'client_id' => $mappedClientData['mappedClientId'],
            'client_secret' => $mappedClientData['secret'],
        ]);

        return $this->sendRequest($target, '/oauth/v2/token', $inputData);
    }

    public function resetPasswordToken(TargetSuplaCloud $target, string $username): array {
        return $this->sendRequest($target, 'forgotten-password', ['email' => $username]);
    }

    public function resendActivationEmail(TargetSuplaCloud $target, string $username): array {
        return $this->sendRequest($target, 'register-resend', ['email' => $username]);
    }

    public function requestUserDeletion(TargetSuplaCloud $target, string $username, string $password): array {
        return $this->sendRequest($target, 'account-deletion', ['username' => $username, 'password' => $password], 'PUT');
    }

    public function getUserInfo(TargetSuplaCloud $target, string $username): array {
        return $this->sendRequest($target, 'user-info', ['username' => $username], 'PATCH');
    }

    public function registerUser(TargetSuplaCloud $target, Request $request): array {
        return $this->sendRequest($target, 'register', $request->request->all());
    }

    public function getInfo(TargetSuplaCloud $target, array $headers = []): ?array {
        [$response, $status] = $this->sendRequest($target, 'server-info', null, null, $headers);
        if ($status == 200) {
            return $response;
        } else {
            return null;
        }
    }

    private function sendRequest(
        TargetSuplaCloud $target,
        string $apiEndpoint,
        array $data = null,
        string $method = null,
        array $headers = []
    ): array {
        if (self::$requestExecutor) {
            return (self::$requestExecutor)($target->getAddress(), $apiEndpoint, $data);
        }
        if (strpos($apiEndpoint, '/') !== 0) {
            $apiEndpoint = '/api/v' . ApiVersions::V2_3 . '/' . $apiEndpoint;
        }
        if ($ip = $this->clientIpResolver->getRealIp()) {
            $headers['X-Real-Ip'] = $ip;
        }
        $fullUrl = $target->getAddress() . $apiEndpoint;
        $response = $this->brokerHttpClient->request($fullUrl, $data, $responseStatus, $headers, $method, 'SUPLA-Broker-Token');
        return [$response, $responseStatus];
    }
}

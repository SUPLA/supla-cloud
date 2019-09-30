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

use Psr\Log\LoggerInterface;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TargetSuplaCloudRequestForwarder {
    /** @var LoggerInterface */
    private $logger;
    /** @var RealClientIpResolver */
    private $clientIpResolver;

    /**
     * For the sake of tests.
     * @var callable
     */
    public static $requestExecutor;

    public function __construct(LoggerInterface $logger, RealClientIpResolver $clientIpResolver) {
        $this->logger = $logger;
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
        $inputData = array_merge($inputData, ['client_id' => $mappedClientData['mappedClientId'],
            'client_secret' => $mappedClientData['secret']]);

        return $this->sendRequest($target, '/oauth/v2/token', $inputData);
    }

    public function resetPasswordToken(TargetSuplaCloud $target, string $username): array {
        return $this->sendRequest($target, 'forgotten-password', ['email' => $username]);
    }

    public function resendActivationEmail(TargetSuplaCloud $target, string $username): array {
        return $this->sendRequest($target, 'register-resend', ['email' => $username]);
    }

    public function getUserInfo(TargetSuplaCloud $target, string $username): array {
        return $this->sendRequest($target, 'user-info', ['username' => $username], 'PATCH');
    }

    public function registerUser(TargetSuplaCloud $target, Request $request): array {
        return $this->sendRequest($target, 'register', $request->request->all());
    }

    public function getInfo(TargetSuplaCloud $target) {
        list($response, $status) = $this->sendRequest($target, 'server-info');
        if ($status == 200) {
            return $response;
        } else {
            return null;
        }
    }

    private function sendRequest(TargetSuplaCloud $target, string $apiEndpoint, array $data = null, string $method = null): array {
        if (self::$requestExecutor) {
            return (self::$requestExecutor)($target->getAddress(), $apiEndpoint, $data);
        }
        if (strpos($apiEndpoint, '/') !== 0) {
            $apiEndpoint = '/api/v' . ApiVersions::V2_3 . '/' . $apiEndpoint;
        }
        $ch = curl_init($target->getAddress() . $apiEndpoint);
        $method = $method ?: ($data ? 'POST' : 'GET');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        $headers = [];
        if ($ip = $this->clientIpResolver->getRealIp()) {
            $headers[] = 'X-Real-Ip: ' . $ip;
        }
        if ($data) {
            $content = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($content);
        }
        if (file_exists(SuplaAutodiscover::TARGET_CLOUD_TOKEN_SAVE_PATH)) {
            $headers[] = 'SUPLA-Broker-Token: Bearer ' . file_get_contents(SuplaAutodiscover::TARGET_CLOUD_TOKEN_SAVE_PATH);
        }
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=PHPUNIT'); // uncomment to enable XDEBUG debugging in dev
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch) != 0) {
            $this->logger->error(
                'Target Cloud does not respond.',
                ['address' => $target->getAddress(), 'responseStatus' => $status, 'response' => var_export($response, true)]
            );
            throw new ApiException('Service temporarily unavailable', Response::HTTP_SERVICE_UNAVAILABLE); // i18n
        }
        curl_close($ch);
        $response = json_decode($response, true);
        return [$response, $status];
    }
}

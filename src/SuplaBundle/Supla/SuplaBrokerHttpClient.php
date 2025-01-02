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
use SuplaBundle\Enums\InstanceSettings;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Repository\SettingsStringRepository;
use Symfony\Component\HttpFoundation\Response;

class SuplaBrokerHttpClient {
    private SettingsStringRepository $settingsStringRepository;
    private LoggerInterface $logger;
    private SuplaHttpClient $httpClient;

    public function __construct(SuplaHttpClient $httpClient, SettingsStringRepository $settingsStringRepository, LoggerInterface $logger) {
        $this->settingsStringRepository = $settingsStringRepository;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
    }

    public function request(
        string $fullUrl,
        ?array $payload = null,
        int &$responseStatus = null,
        array $headers = [],
        string $method = null,
        string $authorizationHeaderName = 'Authorization'
    ): ?array {
        $method = $method ?: ($payload ? 'POST' : 'GET');
        $headers['X-Cloud-Version'] = ApiVersions::LATEST;
        if (!isset($headers[$authorizationHeaderName]) && $this->settingsStringRepository->hasValue(InstanceSettings::TARGET_TOKEN)) {
            $headers[$authorizationHeaderName] = 'Bearer ' . $this->settingsStringRepository->getValue(InstanceSettings::TARGET_TOKEN);
        }
        [$responseSuccess, $rawResponse, $responseStatus] = $this->httpClient->request($fullUrl, $method, $payload, $headers);
        $logDetails = [
            'address' => $fullUrl,
            'method' => $method,
            'payload' => $payload,
            'responseStatus' => $responseStatus,
            'response' => var_export($rawResponse, true),
        ];
        if ($responseSuccess) {
            if ($responseStatus >= 200 && $responseStatus <= 399) {
                $this->logger->debug('HTTP Request', $logDetails);
            } else {
                $this->logger->notice('HTTP Request with error response', $logDetails);
            }
        } else {
            $this->logger->warning('Target service does not respond.', $logDetails);
            throw new ApiException('Service temporarily unavailable', Response::HTTP_SERVICE_UNAVAILABLE); // i18n
        }
        return json_decode($rawResponse, true);
    }
}

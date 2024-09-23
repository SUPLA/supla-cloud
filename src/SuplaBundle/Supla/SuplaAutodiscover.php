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

use AppKernel;
use Assert\Assertion;
use DateTime;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\InstanceSettings;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Repository\SettingsStringRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class SuplaAutodiscover {
    const PUBLIC_CLIENTS_SAVE_PATH = AppKernel::VAR_PATH . '/local/public-clients';
    const BROKER_CLOUDS_SAVE_PATH = AppKernel::VAR_PATH . '/local/broker-clouds';

    protected $autodiscoverUrl = null;

    private UserManager $userManager;
    private bool $actAsBrokerCloud;
    /** @var LocalSuplaCloud $localSuplaCloud */
    protected $localSuplaCloud;
    private LoggerInterface $logger;
    protected SettingsStringRepository $settingsStringRepository;
    protected SuplaBrokerHttpClient $brokerHttpClient;

    public function __construct(
        $autodiscoverUrl,
        LocalSuplaCloud $localSuplaCloud,
        bool $actAsBrokerCloud,
        UserManager $userManager,
        LoggerInterface $logger,
        SettingsStringRepository $settingsStringRepository,
        SuplaBrokerHttpClient $brokerHttpClient
    ) {
        $this->autodiscoverUrl = $autodiscoverUrl;
        $this->userManager = $userManager;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->actAsBrokerCloud = $actAsBrokerCloud;
        $this->logger = $logger;
        $this->settingsStringRepository = $settingsStringRepository;
        if (strpos($this->autodiscoverUrl, 'http') !== 0) {
            $this->autodiscoverUrl = 'https://' . $this->autodiscoverUrl;
        }
        $this->brokerHttpClient = $brokerHttpClient;
    }

    public function getAutodiscoverUrl(): ?string {
        return $this->autodiscoverUrl;
    }

    public function enabled(): bool {
        return !!$this->autodiscoverUrl;
    }

    public function isBroker(): bool {
        return $this->actAsBrokerCloud;
    }

    public function isTarget(): bool {
        return $this->settingsStringRepository->hasValue(InstanceSettings::TARGET_TOKEN);
    }

    abstract protected function remoteRequest(
        $endpoint,
        $post = false,
        &$responseStatus = null,
        array $headers = [],
        string $method = null
    );

    public function getAuthServerForUser(string $username): TargetSuplaCloud {
        if ($this->isBroker()) {
            $domainFromAutodiscover = false;
            if (!$this->userManager->userByEmail($username) && filter_var($username, FILTER_VALIDATE_EMAIL) && $this->enabled()) {
                $domainFromAutodiscover = $this->getUserServerFromAd($username);
            }
            if ($domainFromAutodiscover) {
                $serverUrl = $domainFromAutodiscover;
                if (strpos($serverUrl, 'http') !== 0) {
                    $serverUrl = $this->localSuplaCloud->getProtocol() . '://' . $serverUrl;
                }
                return new TargetSuplaCloud($serverUrl);
            }
        }
        return $this->localSuplaCloud;
    }

    public function getRegisterServerForUser(Request $request): TargetSuplaCloud {
        if ($this->isBroker()) {
            if ($this->enabled()) {
                $result = $this->remoteRequest('/new-account-server/' . urlencode($request->getClientIp()));
                $domainFromAutodiscover = $result ? ($result['server'] ?? false) : false;
                if ($domainFromAutodiscover) {
                    return new TargetSuplaCloud($domainFromAutodiscover);
                }
            }
        }
        return $this->localSuplaCloud;
    }

    public function userExists($username): bool {
        if ($username) {
            if ($this->userManager->userByEmail($username)) {
                return true;
            }
            if ($this->enabled() && $this->isBroker()) {
                $authServer = $this->getAuthServerForUser($username);
                return !$authServer->isLocal();
            }
        }
        return false;
    }

    public function getUserServerFromAd($username) {
        if ($this->enabled() && $this->isBroker()) {
            $result = $this->remoteRequest('/users/' . urlencode($username));
            $this->logger->debug(__FUNCTION__, ['response' => $result]);
            $domainFromAutodiscover = $result ? ($result['server'] ?? false) : false;
            if ($domainFromAutodiscover && strpos($domainFromAutodiscover, 'http') !== 0) {
                $domainFromAutodiscover = $this->localSuplaCloud->getProtocol() . '://' . $domainFromAutodiscover;
            }
            return $domainFromAutodiscover;
        }
        return false;
    }

    public function registerUser(User $user): bool {
        if ($this->isBroker()) {
            $this->logger->debug(__FUNCTION__);
            $this->remoteRequest('/users', ['email' => $user->getUsername()], $responseStatus);
            return $responseStatus === 201;
        }
        return true;
    }

    public function deleteUser(User $user): bool {
        if ($this->isBroker()) {
            $this->logger->debug(__FUNCTION__);
            $this->remoteRequest('/users/' . urlencode($user->getUsername()), false, $responseStatus, [], 'DELETE');
            return $responseStatus === 204;
        }
        return true;
    }

    /** @return string|null */
    public function getTargetCloudClientId(TargetSuplaCloud $targetCloud, $clientPublicId) {
        if (!$this->isBroker()) {
            return null;
        }
        $url = '/mapped-client/' . urlencode($clientPublicId) . '/' . urlencode($targetCloud->getAddress());
        $response = $this->remoteRequest($url);
        $this->logger->debug(__FUNCTION__, ['url' => $url, 'response' => $response]);
        return $response['mappedClientId'] ?? null;
    }

    public function getPublicIdBasedOnMappedId(string $clientId): string {
        $url = '/mapped-client-public-id/' . urlencode($clientId);
        $response = $this->remoteRequest($url);
        $this->logger->debug(__FUNCTION__, ['url' => $url, 'response' => $response]);
        return is_array($response) && isset($response['publicClientId']) ? $response['publicClientId'] : '';
    }

    public function updateTargetCloudCredentials(string $mappedClientId, ApiClient $client) {
        $url = '/mapped-client-credentials/' . urlencode($mappedClientId);
        $this->remoteRequest($url, ['clientId' => $client->getPublicId(), 'secret' => $client->getSecret()], $responseStatus);
        $this->logger->debug(__FUNCTION__, ['url' => $url, 'responseStatus' => $responseStatus, 'clientId' => $client->getPublicId()]);
        if (!in_array($responseStatus, [Response::HTTP_OK, Response::HTTP_NO_CONTENT])) {
            throw new ApiException('Autodiscover has rejected the new client data.');
        }
    }

    public function fetchTargetCloudClientSecret(ClientInterface $client, TargetSuplaCloud $targetCloud) {
        if (!$this->isBroker()) {
            return false;
        }
        $url = '/mapped-client/' . urlencode($client->getPublicId()) . '/' . urlencode($targetCloud->getAddress());
        $response = $this->remoteRequest($url, ['secret' => $client->getSecret()], $responseStatus);
        $this->logger->debug(__FUNCTION__, ['url' => $url, 'responseStatus' => $responseStatus]);
        return is_array($response) && isset($response['secret']) ? $response : false;
    }

    public function issueRegistrationTokenForTargetCloud(TargetSuplaCloud $targetCloud, $email): string {
        $response = $this->remoteRequest('/target-cloud-registration-token', [
            'targetCloudUrl' => $targetCloud->getAddress(),
            'email' => $email,
        ], $responseStatus);
        $this->logger->debug(__FUNCTION__, ['targetCloud' => $targetCloud->getAddress()]);
        if ($responseStatus !== 201) {
            $errors = [
                409 => 'Private instance of the SUPLA cloud with given URL is already registered.', // i18n
                503 => 'Could not contact Autodiscover service. Try again in a while.', // i18n
            ];
            throw new ApiException($errors[$responseStatus] ?? $errors[503], $responseStatus);
        }
        $token = is_array($response) && isset($response['token']) ? $response['token'] : '';
        Assertion::notEmpty($token, '');
        return $token;
    }

    public function registerTargetCloud(string $registrationToken): string {
        $response = $this->remoteRequest('/register-target-cloud', [
            'targetCloudUrl' => $this->localSuplaCloud->getAddress(),
            'registrationToken' => $registrationToken,
        ], $responseStatus);
        $this->logger->debug(
            __FUNCTION__,
            [
                'targetCloud' => $this->localSuplaCloud->getAddress(),
                'responseStatus' => $responseStatus,
                'response' => array_diff_key(is_array($response) ? $response : [], ['token' => '']),
            ]
        );
        if ($responseStatus !== 201) {
            $errors = [
                404 => 'Invalid token.', // i18n
                503 => 'Could not contact Autodiscover service. Try again in a while.', // i18n
            ];
            throw new ApiException($errors[$responseStatus] ?? $errors[503], $responseStatus);
        }
        $token = is_array($response) && isset($response['token']) ? $response['token'] : '';
        Assertion::notEmpty($token, "Could not contact Autodiscover service. Try again in a while. (Error: $responseStatus)");
        return $token;
    }

    public function issueRemovalTokenForTargetCloud(TargetSuplaCloud $targetCloud, $email): array {
        $response = $this->remoteRequest('/target-cloud-removal-token', [
            'targetCloudUrl' => $targetCloud->getAddress(),
            'email' => $email,
        ], $responseStatus);
        $this->logger->debug(__FUNCTION__, ['targetCloud' => $targetCloud->getAddress()]);
        if ($responseStatus !== 201) {
            $errors = [
                400 => 'Invalid request.', // i18n
                404 => 'Target Cloud is not registered, or is registered with another e-mail address.', // i18n
                503 => 'Could not contact Autodiscover service. Try again in a while.', // i18n
            ];
            throw new ApiException($errors[$responseStatus] ?? $errors[503], $responseStatus);
        }
        Assertion::keyExists($response, 'token');
        Assertion::keyExists($response, 'targetCloudId');
        return $response;
    }

    public function removeTargetCloud(int $targetCloudId, string $removalToken): void {
        $this->remoteRequest('/remove-target-cloud', [
            'targetCloudId' => $targetCloudId,
            'token' => $removalToken,
        ], $responseStatus);
        $this->logger->debug(
            __FUNCTION__,
            [
                'targetCloudId' => $targetCloudId,
                'responseStatus' => $responseStatus,
            ]
        );
        if ($responseStatus !== 204) {
            $errors = [
                400 => 'Invalid request.', // i18n
                404 => 'Invalid token.', // i18n
                503 => 'Could not contact Autodiscover service. Try again in a while.', // i18n
            ];
            throw new ApiException($errors[$responseStatus] ?? $errors[503], $responseStatus);
        }
    }

    public function getPublicClients() {
        $publicClients = null;
        if (file_exists(self::PUBLIC_CLIENTS_SAVE_PATH)) {
            $publicClients = json_decode(file_get_contents(self::PUBLIC_CLIENTS_SAVE_PATH), true);
        }
        if (!is_array($publicClients) || !isset($publicClients['lastFetchedDatetime'])) {
            $publicClients = ['lastFetchedDatetime' => 0, 'clients' => []];
        }
        $response = $this->remoteRequest(
            '/public-clients',
            false,
            $responseStatus,
            ['If-Modified-Since' => $publicClients['lastFetchedDatetime']]
        );
        $this->logger->debug(__FUNCTION__, ['responseStatus' => $responseStatus]);
        if ($responseStatus == Response::HTTP_OK) {
            $publicClients = ['lastFetchedDatetime' => (new DateTime())->format(DateTime::RFC822), 'clients' => $response];
            $saved = file_put_contents(self::PUBLIC_CLIENTS_SAVE_PATH, json_encode($publicClients));
            Assertion::greaterThan($saved, 0, 'Could not save the public clients list from Autodiscover');
        } else {
            Assertion::eq(Response::HTTP_NOT_MODIFIED, $responseStatus, 'Error communicating with AD - please try again later.');
        }
        return $publicClients['clients'];
    }

    public function getPublicClient(string $publicClientId): ?array {
        $publicClients = $this->getPublicClients();
        $publicClient = array_filter($publicClients, function ($publicClientData) use ($publicClientId) {
            return $publicClientData['id'] == $publicClientId;
        });
        return count($publicClient) ? current($publicClient) : null;
    }

    public function getBrokerClouds(): array {
        if (!$this->isBroker()) {
            return [];
        }
        if (file_exists(self::BROKER_CLOUDS_SAVE_PATH)) {
            $brokerClouds = json_decode(file_get_contents(self::BROKER_CLOUDS_SAVE_PATH), true);
            if (is_array($brokerClouds)) {
                return $brokerClouds;
            }
        }
        $url = '/broker-clouds';
        $response = $this->remoteRequest($url, null, $responseStatus);
        $brokerClouds = is_array($response) ? $response : [];
        $this->logger->debug(__FUNCTION__, ['url' => $url, 'responseStatus' => $responseStatus, 'brokersCount' => count($brokerClouds)]);
        if ($brokerClouds) {
            file_put_contents(self::BROKER_CLOUDS_SAVE_PATH, json_encode($brokerClouds));
        }
        return $brokerClouds;
    }

    public function getEspUpdates() {
        if ($this->enabled()) {
            $result = $this->remoteRequest('/esp-updates');
            $this->logger->debug(__FUNCTION__, ['response' => $result]);
            return $result;
        }
        return false;
    }

    public function getInfo(string $brokerToken = null): array {
        $response = $this->remoteRequest('/about', null, $responseStatus, ['Authorization' => $brokerToken]);
        return $response ?: ['isBroker' => false, 'isTarget' => false];
    }

    public function setBrokerIpAddresses(array $ips): bool {
        $response = $this->remoteRequest('/set-broker-ip-addresses', ['ips' => $ips,], $responseStatus);
        $this->logger->debug(
            __FUNCTION__,
            [
                'targetCloud' => $this->localSuplaCloud->getAddress(),
                'responseStatus' => $responseStatus,
                'response' => $response,
            ]
        );
        return $responseStatus === 204;
    }

    public function requestDeviceUnlockCode(IODevice $device, string $unlockerEmail): string {
        $response = $this->remoteRequest('/unlock-device', [
            'email' => $unlockerEmail,
            'deviceName' => $device->getName(),
            'guidString' => $device->getGUIDString(),
            'userEmail' => $device->getUser()->getEmail(),
        ], $responseStatus);
        $this->logger->debug(
            __FUNCTION__,
            [
                'targetCloud' => $this->localSuplaCloud->getAddress(),
                'responseStatus' => $responseStatus,
                'response' => $response,
            ]
        );
        if ($responseStatus !== 200) {
            $errors = [
                404 => 'Invalid e-mail address.', // i18n
                409 => 'The device has been unlocked already.', // i18n
                503 => 'Could not contact Autodiscover service. Try again in a while.', // i18n
            ];
            throw new ApiException($errors[$responseStatus] ?? $errors[503], $responseStatus);
        }
        return $response['unlock_code'];
    }

    public function unlockDevice(IODevice $device, string $unlockCode): void {
        $this->remoteRequest('/unlock-device', [
            'unlockCode' => $unlockCode,
            'guidString' => $device->getGUIDString(),
        ], $responseStatus);
        $this->logger->debug(
            __FUNCTION__,
            [
                'targetCloud' => $this->localSuplaCloud->getAddress(),
                'responseStatus' => $responseStatus,
            ]
        );
        if ($responseStatus !== 200) {
            $errors = [
                400 => 'Invalid unlock code.', // i18n
                404 => 'Invalid unlock code.', // i18n
                503 => 'Could not contact Autodiscover service. Try again in a while.', // i18n
            ];
            throw new ApiException($errors[$responseStatus] ?? $errors[503], $responseStatus);
        }
    }
}

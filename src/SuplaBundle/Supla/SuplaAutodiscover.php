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

use Assert\Assertion;
use FOS\OAuthServerBundle\Model\ClientInterface;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\User;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Model\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class SuplaAutodiscover {
    const TARGET_CLOUD_TOKEN_SAVE_PATH = \AppKernel::VAR_PATH . '/local/target-cloud-token';
    const PUBLIC_CLIENTS_SAVE_PATH = \AppKernel::VAR_PATH . '/local/public-clients';

    protected $autodiscoverUrl = null;

    /** @var UserManager */
    private $userManager;
    /** @var bool */
    private $actAsBrokerCloud;
    /** @var LocalSuplaCloud */
    protected $localSuplaCloud;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        $autodiscoverUrl,
        LocalSuplaCloud $localSuplaCloud,
        bool $actAsBrokerCloud,
        UserManager $userManager,
        LoggerInterface $logger
    ) {
        $this->autodiscoverUrl = $autodiscoverUrl;
        $this->userManager = $userManager;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->actAsBrokerCloud = $actAsBrokerCloud;
        $this->logger = $logger;
        if (strpos($this->autodiscoverUrl, 'http') !== 0) {
            $this->autodiscoverUrl = 'https://' . $this->autodiscoverUrl;
        }
    }

    public function enabled(): bool {
        return !!$this->autodiscoverUrl;
    }

    public function isBroker(): bool {
        return $this->actAsBrokerCloud;
    }

    public function isTarget(): bool {
        return file_exists(self::TARGET_CLOUD_TOKEN_SAVE_PATH);
    }

    abstract protected function remoteRequest($endpoint, $post = false, &$responseStatus = null, array $headers = []);

    public function getAuthServerForUser(string $username): TargetSuplaCloud {
        $domainFromAutodiscover = false;
        if (!$this->userManager->userByEmail($username) && filter_var($username, FILTER_VALIDATE_EMAIL) && $this->enabled()) {
            $result = $this->remoteRequest('/users/' . urlencode($username));
            $this->logger->debug(__FUNCTION__, ['response' => $result]);
            $domainFromAutodiscover = $result ? ($result['server'] ?? false) : false;
        }
        if ($domainFromAutodiscover) {
            $serverUrl = $this->localSuplaCloud->getProtocol() . '://' . $domainFromAutodiscover;
            return new TargetSuplaCloud($serverUrl);
        } else {
            return $this->localSuplaCloud;
        }
    }

    public function getRegisterServerForUser(Request $request): TargetSuplaCloud {
        $domainFromAutodiscover = false;
        if ($this->enabled()) {
            $result = $this->remoteRequest('/new-account-server/' . urlencode($request->getClientIp()));
            $domainFromAutodiscover = $result ? ($result['server'] ?? false) : false;
        }
        if ($domainFromAutodiscover) {
            $serverUrl = $this->localSuplaCloud->getProtocol() . '://' . $domainFromAutodiscover;
            return new TargetSuplaCloud($serverUrl);
        } else {
            return $this->localSuplaCloud;
        }
    }

    public function userExists($username) {
        if ($username) {
            if ($this->userManager->userByEmail($username)) {
                return true;
            }
            if ($this->enabled()) {
                $authServer = $this->getAuthServerForUser($username);
                return !$authServer->isLocal();
            }
        }
        return false;
    }

    public function registerUser(User $user) {
        $this->logger->debug(__FUNCTION__);
        $this->remoteRequest('/users', ['email' => $user->getUsername()]);
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
            'targetCloud' => $targetCloud->getAddress(),
            'email' => $email,
        ]);
        $this->logger->debug(__FUNCTION__, ['targetCloud' => $targetCloud->getAddress()]);
        $token = is_array($response) && isset($response['token']) ? $response['token'] : '';
        Assertion::notEmpty($token, 'Could not contact Autodiscover service. Try again in a while.');
        return $token;
    }

    public function registerTargetCloud(string $registrationToken): string {
        $response = $this->remoteRequest('/register-target-cloud', [
            'targetCloud' => $this->localSuplaCloud->getAddress(),
            'registrationToken' => $registrationToken,
        ]);
        $this->logger->debug(__FUNCTION__, ['targetCloud' => $this->localSuplaCloud->getAddress()]);
        $token = is_array($response) && isset($response['token']) ? $response['token'] : '';
        Assertion::notEmpty($token, 'Could not contact Autodiscover service. Try again in a while.');
        return $token;
    }

    public function getPublicClients() {
        $publicClients = null;
        if (file_exists(self::PUBLIC_CLIENTS_SAVE_PATH)) {
            $publicClients = json_decode(file_get_contents(self::PUBLIC_CLIENTS_SAVE_PATH), true);
        }
        if (!is_array($publicClients) || !isset($publicClients['lastFetchedTimestamp'])) {
            $publicClients = ['lastFetchedTimestamp' => 0, 'clients' => []];
        }
        $response = $this->remoteRequest(
            '/public-clients',
            false,
            $responseStatus,
            ['If-Modified-Since' => $publicClients['lastFetchedTimestamp']]
        );
        $this->logger->debug(__FUNCTION__, ['responseStatus' => $responseStatus]);
        if ($responseStatus == Response::HTTP_OK) {
            $publicClients = ['lastFetchedTimestamp' => time(), 'clients' => $response];
            $saved = file_put_contents(self::PUBLIC_CLIENTS_SAVE_PATH, json_encode($publicClients));
            Assertion::greaterThan($saved, 0, 'Could not save the public clients list from Autodiscover');
        } else {
            Assertion::eq(Response::HTTP_NOT_MODIFIED, $responseStatus, 'Error communicating with AD - please try again later.');
        }
        return $publicClients['clients'];
    }

    /** @return array|null */
    public function getPublicClient(string $publicClientId) {
        $publicClients = $this->getPublicClients();
        $publicClient = array_filter($publicClients, function ($publicClientData) use ($publicClientId) {
            return $publicClientData['id'] == $publicClientId;
        });
        return count($publicClient) ? current($publicClient) : null;
    }
}

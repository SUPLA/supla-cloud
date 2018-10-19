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

    protected $autodiscoverUrl = null;

    /** @var UserManager */
    private $userManager;
    /** @var bool */
    private $actAsBrokerCloud;
    /** @var LocalSuplaCloud */
    private $localSuplaCloud;

    public function __construct($autodiscoverUrl, LocalSuplaCloud $localSuplaCloud, bool $actAsBrokerCloud, UserManager $userManager) {
        $this->autodiscoverUrl = $autodiscoverUrl;
        $this->userManager = $userManager;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->actAsBrokerCloud = $actAsBrokerCloud;
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

    abstract protected function remoteRequest($endpoint, $post = false, &$responseStatus = null);

    public function getAuthServerForUser(string $username): TargetSuplaCloud {
        $domainFromAutodiscover = false;
        if (!$this->userManager->userByEmail($username) && filter_var($username, FILTER_VALIDATE_EMAIL) && $this->enabled()) {
            $result = $this->remoteRequest('/users/' . urlencode($username));
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
        $this->remoteRequest('/users', ['email' => $user->getUsername()]);
    }

    /** @return string|null */
    public function getTargetCloudClientId(TargetSuplaCloud $targetCloud, $clientPublicId) {
        if (!$this->isBroker()) {
            return null;
        }
        $response = $this->remoteRequest('/mapped-client-id/' . urlencode($clientPublicId) . '/' . urlencode($targetCloud->getAddress()));
        return $response['mapped_client_id'] ?? null;
    }

    public function fetchTargetCloudClientData(string $clientId) {
        $response = $this->remoteRequest(
            '/mapped-client-data/' . urlencode($clientId) . '/' . urlencode($this->localSuplaCloud->getAddress())
        );
        return is_array($response) && isset($response['name']) ? $response : false;
    }

    public function updateTargetCloudClientData(string $clientId, ApiClient $client) {
        $this->remoteRequest('/mapped-client-data/' . urlencode($clientId) . '/' . urlencode($this->localSuplaCloud->getAddress()), [
            'clientId' => $client->getPublicId(),
            'secret' => $client->getSecret(),
        ], $responseStatus);
        if (!in_array($responseStatus, [Response::HTTP_OK, Response::HTTP_NO_CONTENT])) {
            throw new ApiException('Autodiscover has rejected the new client data.');
        }
    }

    public function fetchTargetCloudClientSecret(ClientInterface $client, TargetSuplaCloud $targetCloud) {
        if (!$this->isBroker()) {
            return false;
        }
        $url = '/mapped-client-secret/' . urlencode($client->getPublicId()) . '/' . urlencode($targetCloud->getAddress());
        $response = $this->remoteRequest($url, ['secret' => $client->getSecret()]);
        return is_array($response) && isset($response['secret']) ? $response : false;
    }

    public function issueRegistrationTokenForTargetCloud(TargetSuplaCloud $targetCloud, $email): string {
        $response = $this->remoteRequest('/target-cloud-registration-token', [
            'targetCloud' => $targetCloud->getAddress(),
            'email' => $email,
        ]);
        $token = is_array($response) && isset($response['token']) ? $response['token'] : '';
        Assertion::notEmpty($token, 'Could not contact Autodiscover service. Try again in a while.');
        return $token;
    }

    public function registerTargetCloud(string $registrationToken): string {
        $response = $this->remoteRequest('/register-target-cloud', [
            'targetCloud' => $this->localSuplaCloud->getAddress(),
            'registrationToken' => $registrationToken,
        ]);
        $token = is_array($response) && isset($response['token']) ? $response['token'] : '';
        Assertion::notEmpty($token, 'Could not contact Autodiscover service. Try again in a while.');
        return $token;
    }
}

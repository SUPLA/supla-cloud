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

use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\User;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Model\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class SuplaAutodiscover {
    protected $autodiscoverUrl = null;

    private $suplaUrl;
    /** @var UserManager */
    private $userManager;
    /** @var string */
    private $suplaProtocol;

    public function __construct($autodiscoverUrl, string $suplaProtocol, string $suplaUrl, UserManager $userManager) {
        $this->autodiscoverUrl = $autodiscoverUrl;
        $this->suplaUrl = $suplaUrl;
        $this->userManager = $userManager;
        $this->suplaProtocol = $suplaProtocol;
    }

    public function enabled(): bool {
        return !!$this->autodiscoverUrl;
    }

    abstract protected function remoteRequest($endpoint, $post = false, &$responseStatus = null);

    public function getAuthServerForUser(string $username): TargetSuplaCloud {
        $domainFromAutodiscover = false;
        if (!$this->userManager->userByEmail($username) && filter_var($username, FILTER_VALIDATE_EMAIL) && $this->enabled()) {
            $result = $this->remoteRequest('/users/' . urlencode($username));
            $domainFromAutodiscover = $result ? ($result['server'] ?? false) : false;
        }
        $serverUrl = $domainFromAutodiscover ? $this->suplaProtocol . '://' . $domainFromAutodiscover : $this->suplaUrl;
        return new TargetSuplaCloud($serverUrl, !$domainFromAutodiscover || $domainFromAutodiscover == $this->suplaUrl);
    }

    public function getRegisterServerForUser(Request $request): TargetSuplaCloud {
        $domainFromAutodiscover = false;
        if ($this->enabled()) {
            $result = $this->remoteRequest('/new-account-server/' . urlencode($request->getClientIp()));
            $domainFromAutodiscover = $result ? ($result['server'] ?? false) : false;
        }
        $serverUrl = $domainFromAutodiscover ? $this->suplaProtocol . '://' . $domainFromAutodiscover : $this->suplaUrl;
        return new TargetSuplaCloud($serverUrl, !$domainFromAutodiscover || $domainFromAutodiscover == $this->suplaUrl);
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
        $response = $this->remoteRequest('/mapped-client-id/' . urlencode($clientPublicId) . '/' . urlencode($targetCloud->getAddress()));
        return $response['mapped_client_id'] ?? null;
    }

    public function fetchTargetCloudClientData(string $clientId) {
        $response = $this->remoteRequest('/mapped-client-data/' . urlencode($clientId) . '/' . urlencode($this->suplaUrl));
        return is_array($response) && isset($response['name']) ? $response : false;
    }

    public function updateTargetCloudClientData(string $clientId, ApiClient $client) {
        $this->remoteRequest('/mapped-client-data/' . urlencode($clientId) . '/' . urlencode($this->suplaUrl), [
            'clientId' => $client->getPublicId(),
            'secret' => $client->getSecret(),
        ], $responseStatus);
        if (!in_array($responseStatus, [Response::HTTP_OK, Response::HTTP_NO_CONTENT])) {
            throw new ApiException('Autodiscover has rejected the new client data.');
        }
    }
}

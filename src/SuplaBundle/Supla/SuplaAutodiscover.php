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

use SuplaBundle\Entity\User;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Model\UserManager;
use Symfony\Component\HttpFoundation\Request;

abstract class SuplaAutodiscover {
    protected $autodiscoverUrl = null;
    private $suplaUrl;
    /** @var UserManager */
    private $userManager;

    public function __construct($autodiscoverUrl, string $suplaUrl, UserManager $userManager) {
        $this->autodiscoverUrl = $autodiscoverUrl;
        $this->suplaUrl = $suplaUrl;
        $this->userManager = $userManager;
    }

    public function enabled(): bool {
        return !!$this->autodiscoverUrl;
    }

    abstract protected function remoteRequest($endpoint, $post = false);

    public function getAuthServerForUser(string $username): TargetSuplaCloud {
        $domainFromAutodiscover = false;
        if (filter_var($username, FILTER_VALIDATE_EMAIL) && $this->enabled()) {
            $result = $this->remoteRequest('/users/' . urlencode($username));
            $domainFromAutodiscover = $result ? ($result['server'] ?? false) : false;
        }
        $serverUrl = $domainFromAutodiscover ? 'https://' . $domainFromAutodiscover : $this->suplaUrl;
        return new TargetSuplaCloud($serverUrl, !$domainFromAutodiscover);
    }

    public function getRegisterServerForUser(string $username, Request $request): TargetSuplaCloud {
        $domainFromAutodiscover = false;
        if (filter_var($username, FILTER_VALIDATE_EMAIL) && $this->enabled()) {
            $result = $this->remoteRequest('/servers/' . urlencode($username) . '?ip=' . urlencode($request->getClientIp()));
            $domainFromAutodiscover = $result ? ($result['server'] ?? false) : false;
        }
        $serverUrl = $domainFromAutodiscover ? 'https://' . $domainFromAutodiscover : $this->suplaUrl;
        return new TargetSuplaCloud($serverUrl, !$domainFromAutodiscover);
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

    public function findServer(string $username) {
        $result = $this->remoteRequest('/users/' . urlencode($username));
        if ($result && strlen(@$result['server']) > 0) {
            return $result['server'];
        }
        return $result;
    }

    public function registerUser(User $user) {
        $this->remoteRequest('/users', ['email' => $user->getUsername()]);
    }
}

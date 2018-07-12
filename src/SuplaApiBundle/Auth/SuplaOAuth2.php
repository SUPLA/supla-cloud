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
namespace SuplaApiBundle\Auth;

use OAuth2\IOAuth2Storage;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2;
use SuplaApiBundle\Entity\OAuth\ApiClient;

class SuplaOAuth2 extends OAuth2 {
    /** @var string */
    private $suplaUrl;

    public function __construct(IOAuth2Storage $storage, array $config = [], string $suplaUrl = '') {
        parent::__construct($storage, $config);
        $this->suplaUrl = $suplaUrl;
    }

    protected function genAccessToken() {
        return parent::genAccessToken() . '.' . base64_encode($this->suplaUrl);
    }

    /** @param ApiClient $client */
    public function createAccessToken(
        IOAuth2Client $client,
        $data,
        $scope = null,
        $accessTokenLifetime = null,
        $issueRefreshToken = true,
        $refreshTokenLifetime = null
    ) {
        return parent::createAccessToken(
            $client,
            $data,
            $scope,
            $accessTokenLifetime,
            $issueRefreshToken && !$client->issueNeverExpiringAccessToken(),
            $refreshTokenLifetime
        );
    }
}

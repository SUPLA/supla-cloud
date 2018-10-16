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
namespace SuplaBundle\Auth;

use OAuth2\IOAuth2Storage;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2;
use OAuth2\OAuth2ServerException;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\OAuth\ApiClient;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ApiClientType;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Supla\SuplaAutodiscover;

class SuplaOAuth2 extends OAuth2 {
    /** @var string */
    private $suplaUrl;
    /** @var array */
    private $tokensLifetime;
    /** @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(
        IOAuth2Storage $storage,
        array $config,
        string $suplaUrl,
        array $tokensLifetime,
        SuplaAutodiscover $autodiscover
    ) {
        parent::__construct($storage, $config);
        $this->suplaUrl = $suplaUrl;
        $this->tokensLifetime = $tokensLifetime;
        $this->setVariable(self::CONFIG_SUPPORTED_SCOPES, OAuthScope::getAllKnownScopes());
        $this->autodiscover = $autodiscover;
    }

    protected function genAccessToken() {
        return parent::genAccessToken() . '.' . base64_encode($this->suplaUrl);
    }

    /**
     * @param ApiClient $client
     * @inheritdoc
     */
    public function createAccessToken(
        IOAuth2Client $client,
        $data,
        $scope = null,
        $accessTokenLifetime = null,
        $issueRefreshToken = true,
        $refreshTokenLifetime = null
    ) {
        $clientType = $client->getType()->getValue();
        $accessTokenLifetime = $this->randomizeTokenLifetime($this->tokensLifetime[$clientType]['access']);
        $token = parent::createAccessToken(
            $client,
            $data,
            $scope,
            $accessTokenLifetime,
            (new OAuthScope($scope))->hasScope('offline_access'),
            $this->randomizeTokenLifetime($this->tokensLifetime[$clientType]['refresh'])
        );
        if ($clientType == ApiClientType::WEBAPP) {
            $tokenUsedForFilesDownload = parent::createAccessToken(
                $client,
                $data,
                'channels_files',
                $accessTokenLifetime,
                false
            );
            $token['download_token'] = $tokenUsedForFilesDownload['access_token'];
        }
        return $token;
    }

    public function createPersonalAccessToken(User $user, string $name, OAuthScope $scope): AccessToken {
        $token = new AccessToken();
        $token->setScope((string)($scope->ensureThatAllScopesAreSupported()->addImplicitScopes()));
        $token->setUser($user);
        $token->setName($name);
        $token->setToken($this->genAccessToken());
        return $token;
    }

    private function randomizeTokenLifetime(int $lifetime): int {
        return $lifetime + floor($lifetime * .001 * rand(1, 100));
    }

    protected function grantAccessTokenAuthCode(IOAuth2Client $client, array $input) {
        if ($client instanceof AutodiscoverPublicClientStub && isset($input['code'])) {
            $code = explode('.', $input['code']);
            if (count($code) === 2 && ($targetCloudUrl = base64_decode($code[1]))) {
                if ($targetCloudUrl != $this->suplaUrl) {
                    // we hit authCode as SUPLA Broker! let's verify the public client credentials now
                    $targetCloud = new TargetSuplaCloud($targetCloudUrl, false);
                    $mappedClientData = $this->autodiscover->fetchTargetCloudClientSecret($client, $targetCloud);
                    if ($mappedClientData) {
                        throw new ForwardRequestToTargetCloudException($targetCloud, $mappedClientData);
                    } else {
                        throw new OAuth2ServerException(
                            self::HTTP_BAD_REQUEST,
                            self::ERROR_INVALID_CLIENT,
                            'The client credentials are invalid'
                        );
                    }
                }
            }
        }
        return parent::grantAccessTokenAuthCode($client, $input);
    }
}

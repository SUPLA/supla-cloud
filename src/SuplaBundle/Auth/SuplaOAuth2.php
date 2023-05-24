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
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use SuplaBundle\Entity\Main\OAuth\ApiClient;
use SuplaBundle\Entity\Main\OAuth\RefreshToken;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ApiClientType;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Repository\ApiClientAuthorizationRepository;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class SuplaOAuth2 extends OAuth2 {
    /** @var array */
    private $tokensLifetime;
    /** @var SuplaAutodiscover */
    private $autodiscover;
    /** @var LocalSuplaCloud */
    private $localSuplaCloud;
    /** @var ApiClientAuthorizationRepository */
    private $apiClientAuthorizationRepository;
    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        SuplaOAuthStorage $storage,
        array $config,
        array $tokensLifetime,
        LocalSuplaCloud $localSuplaCloud,
        SuplaAutodiscover $autodiscover,
        ApiClientAuthorizationRepository $apiClientAuthorizationRepository,
        RequestStack $requestStack
    ) {
        parent::__construct($storage, $config);
        $this->tokensLifetime = $tokensLifetime;
        $this->setVariable(self::CONFIG_SUPPORTED_SCOPES, OAuthScope::getAllKnownScopes());
        $this->localSuplaCloud = $localSuplaCloud;
        $this->autodiscover = $autodiscover;
        $this->apiClientAuthorizationRepository = $apiClientAuthorizationRepository;
        $this->requestStack = $requestStack;
    }

    protected function genAccessToken() {
        return parent::genAccessToken() . '.' . base64_encode($this->localSuplaCloud->getAddress());
    }

    /**
     * @param ApiClient $client
     * @inheritdoc
     */
    public function createAccessToken(
        IOAuth2Client $client,
        $user,
        $scope = null,
        $accessTokenLifetime = null,
        $issueRefreshToken = true,
        $refreshTokenLifetime = null
    ) {
        $clientType = $client->getType()->getValue();
        $accessTokenLifetime = $this->randomizeTokenLifetime($this->tokensLifetime[$clientType]['access']);
        $refreshToken = null;
        if ($this->oldRefreshToken) {
            $refreshToken = $this->storage->getRefreshToken($this->oldRefreshToken);
            $this->oldRefreshToken = null;
        }
        $token = parent::createAccessToken(
            $client,
            $user,
            $scope,
            $accessTokenLifetime,
            (new OAuthScope($scope))->hasScope('offline_access'),
            $this->randomizeTokenLifetime($this->tokensLifetime[$clientType]['refresh'])
        );
        if ($refreshToken) {
            $accessToken = $this->storage->getAccessToken($token['access_token']);
            $this->storage->markAccessTokenIssuedWithRefreshToken($accessToken, $refreshToken);
        }
        if ($clientType == ApiClientType::WEBAPP) {
            $tokenUsedForFilesDownload = parent::createAccessToken(
                $client,
                $user,
                'channels_files',
                $accessTokenLifetime,
                false
            );
            $token['download_token'] = $tokenUsedForFilesDownload['access_token'];
        }
        $token['target_url'] = $this->localSuplaCloud->getAddress();
        return $token;
    }

    public function createPersonalAccessToken(User $user, string $name, OAuthScope $scope): AccessToken {
        $token = new AccessToken($this->requestStack->getCurrentRequest());
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
        $this->forwardIssueTokenRequestIfBroker($client, $input['code'] ?? '');
        return parent::grantAccessTokenAuthCode($client, $input);
    }

    protected function grantAccessTokenRefreshToken(IOAuth2Client $client, array $input) {
        $this->forwardIssueTokenRequestIfBroker($client, $input['refresh_token'] ?? '');
        try {
            return parent::grantAccessTokenRefreshToken($client, $input);
        } catch (OAuth2ServerException $e) {
            /** @var RefreshToken $token */
            $token = $this->storage->getRefreshToken($input["refresh_token"]);
            if ($token && $token->hasExpired()) {
                /** @var SuplaOAuthStorage $storage */
                $storage = $this->storage;
//                $storage->refreshTokenReuseDetected($token);
            }
            throw $e;
        }
    }

    private function forwardIssueTokenRequestIfBroker(IOAuth2Client $client, string $authCodeOrRefreshToken) {
        if ($client instanceof AutodiscoverPublicClientStub && $authCodeOrRefreshToken) {
            $tokenParts = explode('.', $authCodeOrRefreshToken);
            if (count($tokenParts) === 2 && ($targetCloudUrl = base64_decode($tokenParts[1]))) {
                // we hit token issue request as SUPLA Broker! let's verify the public client credentials now
                $targetCloud = new TargetSuplaCloud($targetCloudUrl, false);
                $mappedClientData = $this->autodiscover->fetchTargetCloudClientSecret($client, $targetCloud);
                if ($mappedClientData) {
                    throw new ForwardRequestToTargetCloudException($targetCloud, $mappedClientData);
                } else {
                    throw new OAuth2ServerException(
                        Response::HTTP_BAD_REQUEST,
                        self::ERROR_INVALID_CLIENT,
                        'The client credentials are invalid'
                    );
                }
            }
        }
    }

    public function getStorage(): IOAuth2Storage {
        return $this->storage;
    }
}

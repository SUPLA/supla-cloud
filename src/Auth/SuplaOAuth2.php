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
namespace App\Auth;

use App\Entity\Main\OAuth\AuthCode;
use App\Entity\Main\OAuth\AccessToken;
use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\OAuth\RefreshToken;
use App\Entity\Main\User;
use App\Enums\ApiClientType;
use App\Model\LocalSuplaCloud;
use App\Model\TargetSuplaCloud;
use App\Repository\ApiClientAuthorizationRepository;
use App\Supla\SuplaAutodiscover;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\ORM\EntityManagerInterface;
use OAuth2\IOAuth2Storage;
use OAuth2\Model\IOAuth2Client;
use OAuth2\OAuth2;
use OAuth2\OAuth2RedirectException;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Request;
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
        RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
        private readonly OAuthPkce $oauthPkce
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
     * oauth2-php 1.10 exposes validation only as a protected hook. Keep the
     * internal API on exactly the validator used by finishClientAuthorization,
     * without probing it with a synthetic denial or issuing a code.
     */
    public function validateAuthorizationRequest(Request $request): array {
        $params = $this->getAuthorizeParams($request);
        try {
            $this->oauthPkce->getChallenge($request);
        } catch (OAuth2ServerException $e) {
            throw new OAuth2RedirectException(
                $params['redirect_uri'],
                self::ERROR_INVALID_REQUEST,
                null,
                $params['state'] ?? null
            );
        }
        return $params;
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
        if ($clientType === ApiClientType::WEBAPP) {
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
        return $lifetime + floor($lifetime * .001 * random_int(1, 100));
    }

    protected function grantAccessTokenAuthCode(IOAuth2Client $client, array $input) {
        $this->forwardIssueTokenRequestIfBroker($client, $input['code'] ?? '');
        if (is_string($input['code'] ?? null)) {
            $this->lockLocalAuthCode($input['code']);
        }
        $stored = parent::grantAccessTokenAuthCode($client, $input);
        if ($this->usedAuthCode instanceof AuthCode && ($request = $this->requestStack->getCurrentRequest())) {
            if ($this->usedAuthCode->getCodeChallenge() !== null) {
                $requestInput = $request->getMethod() === 'POST' ? $request->request->all() : $request->query->all();
                $redirectUri = $requestInput['redirect_uri'] ?? null;
                if (
                    !is_string($redirectUri)
                    || !hash_equals($this->usedAuthCode->getRedirectUri(), $redirectUri)
                ) {
                    throw new OAuth2ServerException((string)Response::HTTP_BAD_REQUEST, self::ERROR_REDIRECT_URI_MISMATCH);
                }
            }
            $this->oauthPkce->verify($this->usedAuthCode, $request);
        }
        return $stored;
    }

    /** Lock local code exchange from lookup through code deletion/token creation. */
    public function grantAccessToken(?Request $request = null) {
        $request ??= $this->requestStack->getCurrentRequest();
        if (!$request) {
            return parent::grantAccessToken();
        }
        $input = $request->getMethod() === 'POST' ? $request->request->all() : $request->query->all();
        if (($input['grant_type'] ?? null) !== OAuth2::GRANT_TYPE_AUTH_CODE || !is_string($input['code'] ?? null)) {
            return parent::grantAccessToken($request);
        }
        $codeParts = explode('.', $input['code']);
        if (count($codeParts) === 2 && base64_decode($codeParts[1], true) !== false) {
            // Keep the established broker-to-target forwarding attempt outside
            // this local transaction; it throws to the forwarding controller.
            return parent::grantAccessToken($request);
        }

        return $this->entityManager->wrapInTransaction(fn() => parent::grantAccessToken($request));
    }

    private function lockLocalAuthCode(string $token): void {
        // A no-op UPDATE obtains the row's write lock without leaving a stale
        // SELECT snapshot if another exchange deletes the code while we wait.
        try {
            $this->entityManager->getConnection()->executeStatement(
                'UPDATE supla_oauth_auth_codes SET id = id WHERE token = ?',
                [$token]
            );
            $exists = $this->entityManager->getConnection()->fetchOne(
                'SELECT id FROM supla_oauth_auth_codes WHERE token = ? FOR UPDATE',
                [$token]
            );
            if ($exists === false) {
                throw new OAuth2ServerException((string)Response::HTTP_BAD_REQUEST, self::ERROR_INVALID_GRANT);
            }
        } catch (DriverException $e) {
            // MariaDB can report ER_RECORD_CHANGED (1020) when the winning
            // exchange deletes a locked row while this UPDATE is waiting.
            if ((int)$e->getCode() === 1020 || str_contains($e->getMessage(), '1020 Record has changed')) {
                throw new OAuth2ServerException((string)Response::HTTP_BAD_REQUEST, self::ERROR_INVALID_GRANT);
            }
            throw $e;
        }
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
                try {
                    // Canonicalize before asking AD. A suffix is only a lookup
                    // hint; it must not select a different spelling, protocol
                    // or path from the trusted-peer record.
                    $targetCloud = TargetSuplaCloud::trustedPeer($targetCloudUrl);
                } catch (\InvalidArgumentException $e) {
                    throw new OAuth2ServerException(
                        Response::HTTP_BAD_REQUEST,
                        self::ERROR_INVALID_CLIENT,
                        'The target cloud is not trusted'
                    );
                }
                $mappedClientData = $this->autodiscover->fetchTargetCloudClientSecret($client, $targetCloud);
                if ($mappedClientData) {
                    // A positive AD mapping is the trusted-peer lookup. The
                    // suffix remains only a hint and is never forwarded as an
                    // unverified destination.
                    throw new ForwardRequestToTargetCloudException(
                        $targetCloud,
                        $mappedClientData
                    );
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

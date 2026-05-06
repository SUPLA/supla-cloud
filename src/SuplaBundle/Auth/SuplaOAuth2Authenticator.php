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

use FOS\OAuthServerBundle\Model\AccessToken;
use FOS\OAuthServerBundle\Security\Authenticator\Oauth2Authenticator;
use FOS\OAuthServerBundle\Security\Authenticator\Passport\Badge\AccessTokenBadge;
use OAuth2\OAuth2;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2ServerException;
use SuplaBundle\Auth\Token\AccessIdAwareToken;
use SuplaBundle\Auth\Token\PublicOauthAppToken;
use SuplaBundle\Auth\Token\WebappToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class SuplaOAuth2Authenticator extends Oauth2Authenticator {
    private const SOURCE_HEADER = 'header';
    private const SOURCE_URL = 'url';
    private const SOURCE_BODY = 'body';

    public function supports(Request $request): ?bool {
        return $this->extractBearerToken($request) !== null;
    }

    public function authenticate(Request $request): Passport {
        try {
            $source = null;
            $tokenString = $this->extractBearerToken($request, $source);

            /** @var AccessToken $accessToken */
            $accessToken = $this->serverService->verifyAccessToken($tokenString);

            $user = $accessToken->getUser();

            if (null !== $user) {
                try {
                    $this->userChecker->checkPreAuth($user);
                } catch (AccountStatusException $e) {
                    throw new OAuth2AuthenticateException(
                        Response::HTTP_UNAUTHORIZED,
                        OAuth2::TOKEN_TYPE_BEARER,
                        $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                        'access_denied',
                        $e->getMessage()
                    );
                }
            }

            $scope = $accessToken->getScope();
            $tokenScopes = $scope ? explode(' ', $scope) : [];

            if ($source === self::SOURCE_HEADER) {
                // Trusted transport — full set of roles derived from token scopes.
                $roles = (null !== $user) ? $user->getRoles() : [];
                foreach ($tokenScopes as $scopeName) {
                    $roles[] = 'ROLE_' . mb_strtoupper($scopeName);
                }
            } else {
                // Token sent via URL query string or form body — leaks easily into
                // server logs, browser history and Referer headers, so we restrict
                // it to file-serving endpoints (ROLE_CHANNELS_FILES) only.
                if (!in_array('channels_files', $tokenScopes, true)) {
                    throw new OAuth2AuthenticateException(
                        Response::HTTP_UNAUTHORIZED,
                        OAuth2::TOKEN_TYPE_BEARER,
                        $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                        'insufficient_scope',
                        'Access token passed via URL is allowed only for the channels_files scope.'
                    );
                }
                $roles = ['ROLE_CHANNELS_FILES'];
            }

            $roles = array_unique($roles, SORT_REGULAR);

            $accessTokenBadge = new AccessTokenBadge($accessToken, $roles);

            return new SelfValidatingPassport(new UserBadge($user->getUsername()), [$accessTokenBadge]);
        } catch (OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }
    }

    /**
     * RFC 6750 §2 — bearer token can be sent in:
     *   1) Authorization header,
     *   2) `access_token` query string parameter,
     *   3) `access_token` form-encoded body parameter.
     *
     * The $source out-parameter tells the caller which transport carried the
     * token, so it can apply different trust levels (see authenticate()).
     */
    private function extractBearerToken(Request $request, ?string &$source = null): ?string {
        $authorization = $request->headers->get('Authorization');
        if ($authorization && stripos($authorization, 'Bearer ') === 0) {
            $source = self::SOURCE_HEADER;
            return substr($authorization, 7);
        }
        if ($token = $request->query->get('access_token')) {
            $source = self::SOURCE_URL;
            return $token;
        }
        if ($token = $request->request->get('access_token')) {
            $source = self::SOURCE_BODY;
            return $token;
        }
        $source = null;
        return null;
    }

    public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface {
        $authenticatedToken = parent::createAuthenticatedToken($passport, $firewallName);
        $accessToken = $this->serverService->verifyAccessToken($authenticatedToken->getToken());
        $authenticatedToken->setUser($accessToken->getUser());
        $authenticatedToken->setAuthenticated(true);
        /** @var \SuplaBundle\Entity\Main\OAuth\AccessToken $accessToken */
        if ($accessToken->getIssuedWithRefreshToken()) {
            /** @var SuplaOAuthStorage $storage */
            $storage = $this->serverService->getStorage();
            $storage->unsetRefreshToken($accessToken->getIssuedWithRefreshToken()->getToken());
            $storage->markAccessTokenIssuedWithRefreshToken($accessToken, null);
        }
        if ($accessToken->isForWebapp()) {
            $authenticatedToken = new WebappToken($authenticatedToken);
        } elseif ($accessToken->isForPublicApp()) {
            $authenticatedToken = new PublicOauthAppToken($authenticatedToken);
        }
        if ($accessId = $accessToken->getAccessId()) {
            $authenticatedToken = new AccessIdAwareToken($authenticatedToken, $accessId);
        }
        return $authenticatedToken;
    }
}

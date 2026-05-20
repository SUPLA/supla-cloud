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

use FOS\OAuthServerBundle\Model\AccessToken;
use FOS\OAuthServerBundle\Security\Authenticator\Oauth2Authenticator;
use FOS\OAuthServerBundle\Security\Authenticator\Passport\Badge\AccessTokenBadge;
use OAuth2\OAuth2;
use OAuth2\OAuth2AuthenticateException;
use OAuth2\OAuth2ServerException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

class SuplaOAuth2Authenticator extends Oauth2Authenticator {
    public const REQUEST_ATTRIBUTE_ACCESS_TOKEN = '_supla_oauth_access_token';
    public const REQUEST_ATTRIBUTE_TOKEN_STRING = '_supla_oauth_token_string';
    public const REQUEST_ATTRIBUTE_IS_WEBAPP = '_supla_oauth_is_webapp';
    public const REQUEST_ATTRIBUTE_IS_PUBLIC_APP = '_supla_oauth_is_public_app';
    public const REQUEST_ATTRIBUTE_ACCESS_ID = '_supla_oauth_access_id';

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

            /** @var AccessToken|\App\Entity\Main\OAuth\AccessToken $accessToken */
            $accessToken = $this->serverService->verifyAccessToken($tokenString);

            $user = $accessToken->getUser();
            if (null === $user) {
                throw new OAuth2AuthenticateException(
                    Response::HTTP_UNAUTHORIZED,
                    OAuth2::TOKEN_TYPE_BEARER,
                    $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                    'invalid_token',
                    'The access token is not associated with a user.'
                );
            }

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

            $scope = $accessToken->getScope();
            $tokenScopes = $scope ? explode(' ', $scope) : [];

            if ($source === self::SOURCE_HEADER) {
                $roles = $user->getRoles();
                foreach ($tokenScopes as $scopeName) {
                    $roles[] = 'ROLE_' . mb_strtoupper($scopeName);
                }
                if ($accessToken->isForWebapp()) {
                    $roles[] = 'ROLE_WEBAPP';
                }
                if ($accessToken->isForPublicApp()) {
                    $roles[] = 'ROLE_PUBLIC_OAUTH_APP';
                }
            } else {
                if (!in_array('channels_files', $tokenScopes, true)) {
                    throw new OAuth2AuthenticateException(
                        Response::HTTP_UNAUTHORIZED,
                        OAuth2::TOKEN_TYPE_BEARER,
                        $this->serverService->getVariable(OAuth2::CONFIG_WWW_REALM),
                        'insufficient_scope',
                        'Access token passed via URL or form body is allowed only for the channels_files scope.'
                    );
                }

                $roles = ['ROLE_CHANNELS_FILES'];
            }

            $roles = array_values(array_unique($roles, SORT_REGULAR));

            $request->attributes->set(self::REQUEST_ATTRIBUTE_ACCESS_TOKEN, $accessToken);
            $request->attributes->set(self::REQUEST_ATTRIBUTE_TOKEN_STRING, $tokenString);
            $request->attributes->set(self::REQUEST_ATTRIBUTE_IS_WEBAPP, $accessToken->isForWebapp());
            $request->attributes->set(self::REQUEST_ATTRIBUTE_IS_PUBLIC_APP, $accessToken->isForPublicApp());
            $request->attributes->set(self::REQUEST_ATTRIBUTE_ACCESS_ID, $accessToken->getAccessId());

            $accessTokenBadge = new AccessTokenBadge($accessToken, $roles);

            $passport = new SelfValidatingPassport(
                new UserBadge($user->getUsername(), fn() => $user),
                [$accessTokenBadge]
            );

            $passport->setAttribute('supla_oauth_access_token', $accessToken);
            $passport->setAttribute('supla_oauth_token_string', $tokenString);
            $passport->setAttribute('supla_oauth_roles', $roles);

            return $passport;
        } catch (OAuth2ServerException $e) {
            throw new AuthenticationException('OAuth2 authentication failed', 0, $e);
        }
    }

    public function createAuthenticatedToken(PassportInterface $passport, string $firewallName): TokenInterface {
        /** @var AccessToken|\App\Entity\Main\OAuth\AccessToken $accessToken */
        $accessToken = $passport->getAttribute('supla_oauth_access_token');
        $tokenString = $passport->getAttribute('supla_oauth_token_string');
        $roles = $passport->getAttribute('supla_oauth_roles');

        $this->consumeRefreshTokenIfNeeded($accessToken);

        $token = new PostAuthenticationToken(
            $passport->getUser(),
            $firewallName,
            $roles
        );

        $token->setAttribute(self::REQUEST_ATTRIBUTE_ACCESS_TOKEN, $accessToken);
        $token->setAttribute(self::REQUEST_ATTRIBUTE_TOKEN_STRING, $tokenString);
        $token->setAttribute(self::REQUEST_ATTRIBUTE_IS_WEBAPP, $accessToken->isForWebapp());
        $token->setAttribute(self::REQUEST_ATTRIBUTE_IS_PUBLIC_APP, $accessToken->isForPublicApp());
        $token->setAttribute(self::REQUEST_ATTRIBUTE_ACCESS_ID, $accessToken->getAccessId());

        return $token;
    }

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

    /**
     * @param AccessToken|\APp\Entity\Main\OAuth\AccessToken $accessToken
     */
    private function consumeRefreshTokenIfNeeded(AccessToken $accessToken): void {
        if (!$accessToken->getIssuedWithRefreshToken()) {
            return;
        }

        /** @var SuplaOAuthStorage $storage */
        $storage = $this->serverService->getStorage();

        $storage->unsetRefreshToken($accessToken->getIssuedWithRefreshToken()->getToken());
        $storage->markAccessTokenIssuedWithRefreshToken($accessToken, null);
    }
}

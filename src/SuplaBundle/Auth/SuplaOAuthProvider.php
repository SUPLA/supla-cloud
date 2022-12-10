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

use FOS\OAuthServerBundle\Security\Authentication\Provider\OAuthProvider;
use SuplaBundle\Auth\Token\AccessIdAwareToken;
use SuplaBundle\Auth\Token\PublicOauthAppToken;
use SuplaBundle\Auth\Token\WebappToken;
use SuplaBundle\Entity\Main\OAuth\AccessToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SuplaOAuthProvider extends OAuthProvider {
    public function authenticate(TokenInterface $token) {
        $authenticatedToken = parent::authenticate($token);
        $accessToken = $this->serverService->verifyAccessToken($token->getToken());
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

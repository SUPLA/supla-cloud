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

namespace SuplaBundle\Model;

use Assert\Assertion;
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
use OAuth2\Model\OAuth2AccessToken;
use SuplaBundle\Entity\Main\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

trait CurrentUserAware {
    /** @var TokenStorageInterface */
    protected $tokenStorage;
    /** @var AccessTokenManagerInterface */
    protected $accessTokenManager;

    /** @required */
    public function setTokenStorage(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    /** @required */
    public function setAccessTokenManager(AccessTokenManagerInterface $accessTokenManager) {
        $this->accessTokenManager = $accessTokenManager;
    }

    /** @return \SuplaBundle\Entity\Main\User|null */
    protected function getCurrentUser() {
        if (null === $token = $this->getCurrentUserToken()) {
            return null;
        }
        if (!is_object($user = $token->getUser())) {
            return null;
        }
        return $user;
    }

    protected function getCurrentUserOrThrow(): User {
        $user = $this->getCurrentUser();
        Assertion::notNull($user, 'You must be authenticated to perform this action.');
        return $user;
    }

    /** @return TokenInterface|null */
    protected function getCurrentUserToken() {
        return $this->tokenStorage->getToken();
    }

    /** @return \SuplaBundle\Entity\Main\OAuth\ApiClient|null */
    protected function getCurrentApiClient() {
        /** @var OAuth2AccessToken $token */
        if (null === $token = $this->getCurrentUserToken()) {
            return null;
        }
        /** @var \SuplaBundle\Entity\Main\OAuth\AccessToken $accessToken */
        $accessToken = $this->accessTokenManager->findTokenByToken($token->getToken());
        return $accessToken ? $accessToken->getClient() : null;
    }
}

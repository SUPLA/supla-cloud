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

namespace App\Model;

use App\Auth\SuplaOAuth2Authenticator;
use App\Entity\Main\OAuth\AccessToken;
use App\Entity\Main\OAuth\ApiClient;
use App\Entity\Main\User;
use Assert\Assertion;
use FOS\OAuthServerBundle\Model\AccessTokenManagerInterface;
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

    protected function getCurrentUser(): ?User {
        if (null === $token = $this->getCurrentUserToken()) {
            return null;
        }
        $user = $token->getUser();
        if (!is_object($user)) {
            return null;
        }
        return $user;
    }

    protected function getCurrentUserOrThrow(): User {
        $user = $this->getCurrentUser();
        Assertion::notNull($user, 'You must be authenticated to perform this action.');
        return $user;
    }

    protected function getCurrentUserToken(): ?TokenInterface {
        return $this->tokenStorage->getToken();
    }

    protected function getCurrentAccessToken(): ?AccessToken {
        if (null === $token = $this->getCurrentUserToken()) {
            return null;
        }
        $accessToken = $token->getAttribute(SuplaOAuth2Authenticator::REQUEST_ATTRIBUTE_ACCESS_TOKEN);
        if (!$accessToken) {
            return null;
        }
        return $accessToken;
    }

    protected function getCurrentApiClient(): ?ApiClient {
        $accessToken = $this->getCurrentAccessToken();
        return $accessToken ? $accessToken->getClient() : null;
    }
}

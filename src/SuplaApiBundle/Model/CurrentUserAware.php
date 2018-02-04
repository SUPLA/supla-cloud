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

namespace SuplaApiBundle\Model;

use Assert\Assertion;
use SuplaApiBundle\Entity\ApiUser;
use SuplaBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

trait CurrentUserAware {
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @required */
    public function setTokenStorage(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    /** @return User|null */
    protected function getCurrentUser() {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }
        if (!is_object($user = $token->getUser())) {
            return null;
        }
        if ($user instanceof ApiUser) {
            return $user->getParentUser();
        } else {
            return $user;
        }
    }

    protected function getCurrentUserOrThrow() {
        $user = $this->getCurrentUser();
        Assertion::notNull($user, 'You must be authenticated to perform this action.');
        return $user;
    }
}

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

use Doctrine\Persistence\ManagerRegistry;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\Audit\FailedAuthAttemptsUserBlocker;
use SuplaBundle\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;

class UserProvider extends EntityUserProvider {
    /** @var FailedAuthAttemptsUserBlocker */
    private $failedAuthAttemptsUserBlocker;
    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        ManagerRegistry $registry,
        FailedAuthAttemptsUserBlocker $failedAuthAttemptsUserBlocker,
        UserRepository $userRepository
    ) {
        parent::__construct($registry, User::class, 'email');
        $this->failedAuthAttemptsUserBlocker = $failedAuthAttemptsUserBlocker;
        $this->userRepository = $userRepository;
    }

    public function loadUserByUsername($username) {
        $user = null;
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            if ($this->failedAuthAttemptsUserBlocker->isAuthenticationFailureLimitExceeded($username)) {
                throw new LockedException();
            }
            /** @var User $user */
            $user = parent::loadUserByUsername($username);
            if ($user) {
                if (!$user->isEnabled()) {
                    throw new DisabledException();
                }
            }
        } elseif (preg_match('/^api_[0-9]+$/', $username)) {
            $user = $this->userRepository->findOneBy(['oauthCompatUserName' => $username]);
            if ($user) {
                $user->setOAuthOldApiCompatEnabled();
            }
        }
        return $user;
    }
}

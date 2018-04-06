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

namespace SuplaBundle\EventListener;

use SuplaApiBundle\Model\Audit\AuditAware;
use SuplaBundle\Enums\AuditedAction;
use SuplaBundle\Enums\AuthenticationFailureReason;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserLoginAttemptListener {
    use AuditAware;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function onAuthenticationSuccess(InteractiveLoginEvent $event) {
        $this->auditEntry(AuditedAction::AUTHENTICATION())
            ->setTextParam($event->getAuthenticationToken()->getUsername())
            ->buildAndFlush();
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event) {
        $user = $this->userRepository->findOneByEmail($event->getAuthenticationToken()->getUsername());
        $reason = AuthenticationFailureReason::fromException($event->getAuthenticationException());
        $this->auditEntry(AuditedAction::AUTHENTICATION())
            ->setTextParam($event->getAuthenticationToken()->getUsername())
            ->setIntParam($reason->getValue())
            ->setUser($user)
            ->unsuccessful()
            ->buildAndFlush();
    }
}

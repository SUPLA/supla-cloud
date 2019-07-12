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

use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\AuthenticationFailureReason;
use SuplaBundle\Mailer\SuplaMailer;
use SuplaBundle\Model\Audit\AuditAware;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class UserLoginAttemptListener {
    use AuditAware;

    /** @var UserRepository */
    private $userRepository;
    /** @var SuplaMailer */
    private $mailer;

    public function __construct(UserRepository $userRepository, SuplaMailer $mailer) {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    public function onAuthenticationSuccess(string $username) {
        $this->auditEntry(AuditedEvent::AUTHENTICATION_SUCCESS())
            ->setTextParam($username)
            ->setUser($this->userRepository->findOneByEmail($username))
            ->buildAndFlush();
    }

    public function onAuthenticationFailure(string $username, AuthenticationFailureReason $reason) {
        $user = $this->userRepository->findOneByEmail($username);
        $entry = $this->auditEntry(AuditedEvent::AUTHENTICATION_FAILURE())
            ->setTextParam($username)
            ->setIntParam($reason->getValue())
            ->setUser($user)
            ->buildAndFlush();
        if ($user && $user->isEnabled() && $entry->getIntParam() != AuthenticationFailureReason::BLOCKED) {
            $this->mailer->sendFailedAuthenticationAttemptWarning($user, $entry->getIpv4());
        }
    }

    /* these two methods are called when authenticating to authorize an OAuth app */
    public function onInteractiveAuthenticationSuccess(InteractiveLoginEvent $event) {
        $this->onAuthenticationSuccess($event->getAuthenticationToken()->getUsername());
    }

    public function onInteractiveAuthenticationFailure(AuthenticationFailureEvent $event) {
        $reason = AuthenticationFailureReason::fromException($event->getAuthenticationException());
        $this->onAuthenticationFailure($event->getAuthenticationToken()->getUsername(), $reason);
    }
}

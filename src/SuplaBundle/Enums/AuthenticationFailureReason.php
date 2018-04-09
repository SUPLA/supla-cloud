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

namespace SuplaBundle\Enums;

use MyCLabs\Enum\Enum;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

final class AuthenticationFailureReason extends Enum {
    const UNKNOWN = 0;
    const NOT_EXISTS = 1;
    const BAD_CREDENTIALS = 2;
    const BLOCKED = 3;
    const DISABLED = 4;

    public static function fromException(AuthenticationException $e): AuthenticationFailureReason {
        if ($e instanceof AuthenticationServiceException) {
            $e = $e->getPrevious();
        }
        switch (get_class($e)) {
            case UsernameNotFoundException::class:
                return self::NOT_EXISTS();
            case BadCredentialsException::class:
                return self::BAD_CREDENTIALS();
            case LockedException::class:
                return self::BLOCKED();
            case DisabledException::class:
                return self::DISABLED();
            default:
                return self::UNKNOWN();
        }
    }
}

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

namespace SuplaBundle\Tests\Entity\OAuth;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\OAuth\AccessToken;

class AccessTokenTest extends TestCase {
    public function testConstructorInitializesExpiresAtToZero() {
        $token = new AccessToken();
        $this->assertSame(0, $token->getExpiresAt());
    }

    public function testNonExpiringTokenIsNotConsideredExpired() {
        $token = new AccessToken();
        EntityUtils::setField($token, 'expiresAt', 0);
        $this->assertFalse($token->hasExpired());
    }

    public function testTokenWithPastExpiryHasExpired() {
        $token = new AccessToken();
        EntityUtils::setField($token, 'expiresAt', strtotime('-1 minute'));
        $this->assertTrue($token->hasExpired());
    }

    public function testTokenWithFutureExpiryHasNotExpired() {
        $token = new AccessToken();
        EntityUtils::setField($token, 'expiresAt', strtotime('+1 hour'));
        $this->assertFalse($token->hasExpired());
    }
}

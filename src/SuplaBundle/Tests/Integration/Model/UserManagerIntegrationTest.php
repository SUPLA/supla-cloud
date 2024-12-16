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

namespace SuplaBundle\Tests\Integration\Model;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Tests\Integration\IntegrationTestCase;

/** @small */
class UserManagerIntegrationTest extends IntegrationTestCase {
    public function testCreatingUser() {
        $userManager = self::$container->get(UserManager::class);
        $user = new User();
        $user->setEmail('test@supla.org');
        $userManager->create($user);
        $this->assertNotNull($user);
        $this->assertGreaterThan(0, $user->getId());
        return $user->getId();
    }

    /** @depends testCreatingUser */
    public function testCheckingPasswordValidity(int $userId) {
        $userManager = self::$container->get(UserManager::class);
        $user = $this->freshEntityById(User::class, $userId);
        $userManager->setPassword('ala', $user, true);
        $this->assertTrue($userManager->isPasswordValid($user, 'ala'));
        $this->assertFalse($userManager->isPasswordValid($user, 'Ala'));
        $this->assertFalse($userManager->isPasswordValid($user, 'ala '));
        $this->assertFalse($userManager->isPasswordValid($user, 'ola'));
    }
}

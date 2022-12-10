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

namespace SuplaBundle\Tests\Integration\Command\Cyclic;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

/**
 * @small
 */
class DeleteNotConfirmedUsersCommandIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    private $userId;

    protected function initializeDatabaseForTests() {
        $userManager = self::$container->get(UserManager::class);
        $user = new User();
        $user->setEmail('janusz@supla.org');
        $user->setPlainPassword('januszowe');
        $userManager->create($user);
        $this->userId = $user->getId();
    }

    public function testNotDeletingUserImmediately() {
        $this->executeCommand('supla:clean:not-confirmed-users');
        $this->getEntityManager()->clear();
        $this->assertNotNull($this->getEntityManager()->find(User::class, $this->userId));
    }

    public function testNotDeletingUsersAfter20Hours() {
        TestTimeProvider::setTime('+20 hours');
        $this->executeCommand('supla:clean:not-confirmed-users');
        $this->getEntityManager()->clear();
        $this->assertNotNull($this->getEntityManager()->find(User::class, $this->userId));
    }

    public function testDeletesUserAfter50Hours() {
        TestTimeProvider::setTime('+50 hours');
        $this->executeCommand('supla:clean:not-confirmed-users');
        $this->getEntityManager()->clear();
        $this->assertNull($this->getEntityManager()->find(User::class, $this->userId));
    }

    public function testDeletingUserWithHyphenAtTheBeginning() {
        $userManager = self::$container->get(UserManager::class);
        $user = new User();
        $user->setEmail('-zenon@supla.org');
        $user->setPlainPassword('januszowe');
        $userManager->create($user);
        $userId = $user->getId();
        TestTimeProvider::setTime('+50 hours');
        $this->getEntityManager()->clear();
        $this->executeCommand('supla:clean:not-confirmed-users');
        $this->assertNull($this->getEntityManager()->find(User::class, $userId));
    }
}

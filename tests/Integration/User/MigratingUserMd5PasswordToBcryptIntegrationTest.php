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

namespace App\Tests\Integration\User;

use App\Entity\Main\User;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class MigratingUserMd5PasswordToBcryptIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    /** @var User */
    private $user;
    /** @var PasswordHasherFactoryInterface */
    private $passwordHasherFactory;

    protected function setUp(): void {
        $this->user = $this->createConfirmedUser();
        $this->passwordHasherFactory = self::getContainer()->get('security.password_hasher_factory');
        $passwordHasherFactory = $this->passwordHasherFactory;
        $legacyEncoder = $passwordHasherFactory->getPasswordHasher('legacy_encoder');
        $legacyPasswordSetter = function ($password) use ($legacyEncoder) {
            $this->password = null;
            $this->legacyPassword = $legacyEncoder->hash($password, $this->getSalt());
        };
        $legacyPasswordSetter->call($this->user, 'supla123');
        $this->assertTrue($this->user->hasLegacyPassword());
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
    }

    public function testMigratePasswordOnAuthSuccess() {
        $this->authenticate('supler@supla.org', 'supla123');
        $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail('supler@supla.org');
        $this->assertEquals('supler@supla.org', $user->getEmail());
        $this->assertFalse($user->hasLegacyPassword());
        $this->assertTrue($this->passwordHasherFactory->getPasswordHasher(User::class)
            ->verify($user->getPassword(), 'supla123', $user->getSalt()));
        $this->assertFalse($this->passwordHasherFactory->getPasswordHasher('legacy_encoder')
            ->verify($user->getPassword(), 'supla123', $user->getSalt()));
    }

    public function testDoNotMigratePasswordOnAuthFailure() {
        $this->authenticate('supler@supla.org', 'supla321');
        $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail('supler@supla.org');
        $this->assertTrue($user->hasLegacyPassword());
        $this->assertFalse($this->passwordHasherFactory->getPasswordHasher(User::class)
            ->verify($user->getPassword(), 'supla123', $user->getSalt()));
        $this->assertTrue($this->passwordHasherFactory->getPasswordHasher('legacy_encoder')
            ->verify($user->getPassword(), 'supla123', $user->getSalt()));
    }
}

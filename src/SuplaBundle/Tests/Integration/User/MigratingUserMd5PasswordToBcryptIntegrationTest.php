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

namespace SuplaBundle\Tests\Integration\User;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class MigratingUserMd5PasswordToBcryptIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    /** @var User */
    private $user;
    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    protected function setUp(): void {
        $this->user = $this->createConfirmedUser();
        $this->encoderFactory = self::$container->get('security.encoder_factory');
        $encoderFactory = $this->encoderFactory;
        $legacyPasswordSetter = function ($password) use ($encoderFactory) {
            $this->password = null;
            $this->legacyPassword = $encoderFactory->getEncoder('legacy_encoder')->encodePassword($password, $this->getSalt());
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
        $this->assertTrue($this->encoderFactory->getEncoder(User::class)
            ->isPasswordValid($user->getPassword(), 'supla123', $user->getSalt()));
        $this->assertFalse($this->encoderFactory->getEncoder('legacy_encoder')
            ->isPasswordValid($user->getPassword(), 'supla123', $user->getSalt()));
    }

    public function testDoNotMigratePasswordOnAuthFailure() {
        $this->authenticate('supler@supla.org', 'supla321');
        $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail('supler@supla.org');
        $this->assertTrue($user->hasLegacyPassword());
        $this->assertFalse($this->encoderFactory->getEncoder(User::class)
            ->isPasswordValid($user->getPassword(), 'supla123', $user->getSalt()));
        $this->assertTrue($this->encoderFactory->getEncoder('legacy_encoder')
            ->isPasswordValid($user->getPassword(), 'supla123', $user->getSalt()));
    }
}

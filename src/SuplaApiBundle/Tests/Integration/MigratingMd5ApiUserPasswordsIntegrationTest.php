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

namespace SuplaApiBundle\Tests\Integration;

use SuplaApiBundle\Entity\ApiUser;
use SuplaApiBundle\Entity\Client;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;

class MigratingMd5ApiUserPasswordsIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var User */
    private $user;
    /** @var ApiUser */
    private $apiUser;

    protected function setUp() {
        $this->user = $this->createConfirmedUserWithApiAccess();
        $this->apiUser = $this->getApiUser($this->user);
    }

    public function testNewPasswordIsEncryptedWithNewEncoder() {
        $newEncoder = $this->container->get('security.encoder_factory')->getEncoder($this->apiUser);
        $this->assertTrue($newEncoder->isPasswordValid($this->apiUser->getPassword(), '123', $this->apiUser->getSalt()));
    }

    public function testMigratingMd5PasswordAfterSuccessfulLogin() {
        $this->setLegacyPasswordToApiUser('123');
        $token = $this->authenticateApiUser($this->user);
        $this->assertNotNull($token);
        $this->apiUser = $this->getApiUser($this->user);
        $legacyEncoder = $this->container->get('security.encoder_factory')->getEncoder('legacy_encoder');
        $newEncoder = $this->container->get('security.encoder_factory')->getEncoder($this->apiUser);
        $this->assertFalse($legacyEncoder->isPasswordValid($this->apiUser->getPassword(), '123', $this->apiUser->getSalt()));
        $this->assertTrue($newEncoder->isPasswordValid($this->apiUser->getPassword(), '123', $this->apiUser->getSalt()));
    }

    public function testNotMigratingMd5PasswordAfterFailedLogin() {
        $this->setLegacyPasswordToApiUser('123');
        $token = $this->authenticateApiUser($this->user, '12345');
        $this->assertNull($token);
        $this->apiUser = $this->getApiUser($this->user);
        $legacyEncoder = $this->container->get('security.encoder_factory')->getEncoder('legacy_encoder');
        $newEncoder = $this->container->get('security.encoder_factory')->getEncoder($this->apiUser);
        $this->assertTrue($legacyEncoder->isPasswordValid($this->apiUser->getPassword(), '123', $this->apiUser->getSalt()));
        $this->assertFalse($newEncoder->isPasswordValid($this->apiUser->getPassword(), '123', $this->apiUser->getSalt()));
    }

    private function setLegacyPasswordToApiUser(string $password) {
        $legacyEncoder = $this->container->get('security.encoder_factory')->getEncoder('legacy_encoder');
        $this->apiUser->setPassword($legacyEncoder->encodePassword($password, $this->apiUser->getSalt()));
        $this->container->get('doctrine')->getManager()->persist($this->apiUser);
        $this->container->get('doctrine')->getManager()->flush();
    }
}

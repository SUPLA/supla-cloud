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

namespace SuplaBundle\Tests\Integration\Command;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class CreateConfirmedUserCommandIntegrationTest extends IntegrationTestCase {
    public function testCreatingUserWithCommand() {
        $command = $this->application->find('supla:user:create');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['newuser@supla.org', 'ala123']);
        $exitCode = $commandTester->execute([]);
        $this->assertEquals(0, $exitCode);
        $user = $this->getEntityManager()->getRepository(User::class)->findOneByEmail('newuser@supla.org');
        $this->assertEquals('newuser@supla.org', $user->getEmail());
        $this->assertTrue($user->isEnabled());
    }

    /** @depends testCreatingUserWithCommand */
    public function testCreatingUserInAd() {
        $this->assertCount(2, SuplaAutodiscoverMock::$requests); // 1st - checking if exists, 2nd - registering
        $this->assertEquals(['email' => 'newuser@supla.org'], SuplaAutodiscoverMock::$requests[1]['post']);
    }

    /** @depends testCreatingUserWithCommand */
    public function testCreatingUserThatExists() {
        $command = $this->application->find('supla:user:create');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(['username' => 'newuser@supla.org', '--if-not-exists' => true]);
        $this->assertEquals(0, $exitCode);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('already exists', $output);
    }

    public function testCreatingUserWithAdDisabled() {
        SuplaAutodiscoverMock::clear(false, false);
        $command = $this->application->find('supla:user:create');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['localuser@supla.org', 'ala123']);
        $exitCode = $commandTester->execute([]);
        $this->assertEquals(0, $exitCode);
        $user = $this->getEntityManager()->getRepository(User::class)->findOneByEmail('localuser@supla.org');
        $this->assertEquals('localuser@supla.org', $user->getEmail());
        $this->assertTrue($user->isEnabled());
    }
}

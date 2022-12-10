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
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class ChangeUserLimitsCommandIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    public function testChangingLimitsInteractively() {
        $command = $this->application->find('supla:user:change-limits');
        $commandTester = new CommandTester($command);
        $limits = range(1, count(User::PREDEFINED_LIMITS['default']));
        $limits[] = '5/10'; // API rate limit
        $commandTester->setInputs($limits);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername()]);
        $this->assertEquals(0, $exitCode);
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertEquals(3, $this->user->getLimitAid());
        $this->assertEquals(4, $this->user->getLimitChannelGroup());
        $this->assertEquals(10, $this->user->getLimitOperationsPerScene());
        $this->assertEquals(11, $this->user->getLimitSchedule());
        $this->assertEquals('5/10', $this->user->getApiRateLimit());
    }

    public function testChangingLimitsToConstant() {
        $command = $this->application->find('supla:user:change-limits');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername(), 'limitForAll' => 123]);
        $this->assertEquals(0, $exitCode);
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertEquals(123, $this->user->getLimitAid());
        $this->assertEquals(123, $this->user->getLimitChannelGroup());
        $this->assertEquals(123, $this->user->getLimitSchedule());
    }

    public function testChangingLimitsToPredefined() {
        $command = $this->application->find('supla:user:change-limits');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername(), 'limitForAll' => 'big']);
        $this->assertEquals(0, $exitCode);
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertEquals(User::PREDEFINED_LIMITS['big']['limitAid'], $this->user->getLimitAid());
        $this->assertEquals(User::PREDEFINED_LIMITS['big']['limitChannelGroup'], $this->user->getLimitChannelGroup());
        $this->assertEquals(User::PREDEFINED_LIMITS['big']['limitSchedule'], $this->user->getLimitSchedule());
    }

    public function testChangingLimitsToRelativeAdd() {
        $command = $this->application->find('supla:user:change-limits');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername(), 'limitForAll' => 'default']);
        $this->assertEquals(0, $exitCode);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername(), 'limitForAll' => '+3']);
        $this->assertEquals(0, $exitCode);
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertEquals(User::PREDEFINED_LIMITS['default']['limitAid'] + 3, $this->user->getLimitAid());
        $this->assertEquals(User::PREDEFINED_LIMITS['default']['limitChannelGroup'] + 3, $this->user->getLimitChannelGroup());
        $this->assertEquals(User::PREDEFINED_LIMITS['default']['limitSchedule'] + 3, $this->user->getLimitSchedule());
    }

    public function testChangingLimitsToRelativeMul() {
        $command = $this->application->find('supla:user:change-limits');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername(), 'limitForAll' => 'default']);
        $this->assertEquals(0, $exitCode);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername(), 'limitForAll' => 'x3']);
        $this->assertEquals(0, $exitCode);
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $this->assertEquals(User::PREDEFINED_LIMITS['default']['limitAid'] * 3, $this->user->getLimitAid());
        $this->assertEquals(User::PREDEFINED_LIMITS['default']['limitChannelGroup'] * 3, $this->user->getLimitChannelGroup());
        $this->assertEquals(User::PREDEFINED_LIMITS['default']['limitSchedule'] * 3, $this->user->getLimitSchedule());
    }

    public function testChangingLimitsInvalid() {
        $this->expectException(\InvalidArgumentException::class);
        $command = $this->application->find('supla:user:change-limits');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['username' => $this->user->getUsername(), 'limitForAll' => '-3']);
    }

    public function testChangingLimitsInvalid2() {
        $this->expectException(\InvalidArgumentException::class);
        $command = $this->application->find('supla:user:change-limits');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['username' => $this->user->getUsername(), 'limitForAll' => 'ala']);
    }
}

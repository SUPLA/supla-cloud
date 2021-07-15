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

namespace SuplaBundle\Tests\Command\Cyclic;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Command\Cyclic\DeleteNotConfirmedUsersCommand;
use SuplaBundle\Command\Cyclic\DeleteOrphanedMeasurementLogsCommand;
use SuplaBundle\Repository\UserRepository;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

class CyclicCommandTest extends TestCase {
    /** @after */
    public function resetTimeProvider() {
        TestTimeProvider::reset();
    }

    public function testIntervalRun() {
        $command = new DeleteNotConfirmedUsersCommand($this->createMock(UserRepository::class), new TestTimeProvider(), 24);
        $timeProvider = new TestTimeProvider();
        TestTimeProvider::setTime('2018-11-02 00:00:00');
        $this->assertTrue($command->shouldRunNow($timeProvider));
        TestTimeProvider::setTime('2018-11-02 00:00:40');
        $this->assertTrue($command->shouldRunNow($timeProvider));
        TestTimeProvider::setTime('2018-11-02 00:01:00');
        $this->assertFalse($command->shouldRunNow($timeProvider));
        TestTimeProvider::setTime('2018-11-02 06:00:00');
        $this->assertFalse($command->shouldRunNow($timeProvider));
        TestTimeProvider::setTime('2018-11-02 12:00:02');
        $this->assertTrue($command->shouldRunNow($timeProvider));
    }

    public function testCustomRun() {
        $command = new DeleteOrphanedMeasurementLogsCommand($this->createMock(EntityManagerInterface::class));
        $timeProvider = new TestTimeProvider();
        TestTimeProvider::setTime('2018-11-02 00:00:00');
        $this->assertFalse($command->shouldRunNow($timeProvider));
        TestTimeProvider::setTime('2018-11-02 00:01:00');
        $this->assertFalse($command->shouldRunNow($timeProvider));
        TestTimeProvider::setTime('2018-11-02 01:20:00');
        $this->assertTrue($command->shouldRunNow($timeProvider));
        TestTimeProvider::setTime('2018-11-02 11:20:00');
        $this->assertFalse($command->shouldRunNow($timeProvider));
    }
}

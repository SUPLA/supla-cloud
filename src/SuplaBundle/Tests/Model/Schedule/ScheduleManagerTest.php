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

namespace SuplaBundle\Tests\Model\Schedule\SchedulePlanner;

use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\Schedule\SchedulePlanners\CompositeSchedulePlanner;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

class ScheduleManagerTest extends PHPUnit_Framework_TestCase {
    private $doctrine;
    private $deviceManager;
    /** @var CompositeSchedulePlanner|PHPUnit_Framework_MockObject_MockObject */
    private $schedulePlanner;
    /** @var ScheduleManager */
    private $manager;
    private $scheduledExecutionsRepository;

    public function setUp() {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->scheduledExecutionsRepository = $this->createMock(EntityRepository::class);
        $this->doctrine->method('getRepository')->willReturn($this->scheduledExecutionsRepository);
        $this->deviceManager = $this->createMock(IODeviceManager::class);
        $this->schedulePlanner = $this->createMock(CompositeSchedulePlanner::class);
        $this->manager = new ScheduleManager($this->doctrine, $this->deviceManager, $this->schedulePlanner, new TestTimeProvider());
    }

    // https://github.com/SUPLA/supla-cloud/issues/82
    public function testDoNotCalculateForPastDates() {
        $schedule = new ScheduleWithTimezone('*/100');
        $latestExecutionInThePast = new DateTime('-5days');
        $this->scheduledExecutionsRepository->method('findBy')->willReturn([new ScheduledExecution($schedule, $latestExecutionInThePast)]);
        $this->schedulePlanner->method('calculateNextRunDatesUntil')->willReturnArgument(2);
        $currentTimestamp = time();
        $startDate = $this->manager->getNextScheduleExecutions($schedule);
        $this->assertGreaterThanOrEqual($currentTimestamp, $startDate->getTimestamp());
    }
}

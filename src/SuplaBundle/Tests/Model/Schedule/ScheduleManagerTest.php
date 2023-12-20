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

namespace SuplaBundle\Tests\Model\Schedule;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\ScheduledExecution;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\Schedule\SchedulePlanners\CompositeSchedulePlanner;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

class ScheduleManagerTest extends TestCase {
    private $doctrine;
    private $deviceManager;
    /** @var CompositeSchedulePlanner|MockObject */
    private $schedulePlanner;
    /** @var ScheduleManager */
    private $manager;
    private $scheduledExecutionsRepository;

    public function setUp(): void {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->scheduledExecutionsRepository = $this->createMock(EntityRepository::class);
        $this->doctrine->method('getRepository')->willReturn($this->scheduledExecutionsRepository);
        $this->deviceManager = $this->createMock(IODeviceManager::class);
        $this->schedulePlanner = $this->createMock(CompositeSchedulePlanner::class);
        $channelActionExecutor = $this->createMock(ChannelActionExecutor::class);
        $this->manager = new ScheduleManager(
            $this->doctrine,
            $this->deviceManager,
            $this->schedulePlanner,
            new TestTimeProvider(),
            $channelActionExecutor
        );
    }

    // https://github.com/SUPLA/supla-cloud/issues/82
    public function testDoNotCalculateForPastDates() {
        $schedule = new SchedulePlanner\ScheduleWithTimezone('*/100');
        $latestExecutionInThePast = new DateTime('-5days');
        $execution = new ScheduledExecution($schedule, $latestExecutionInThePast, ChannelFunctionAction::CLOSE());
        $this->scheduledExecutionsRepository->method('findBy')->willReturn([$execution]);
        $this->schedulePlanner->method('calculateScheduleExecutionsUntil')->willReturnArgument(2);
        $currentTimestamp = time();
        $startDate = $this->manager->getNextScheduleExecutions($schedule);
        $this->assertGreaterThanOrEqual($currentTimestamp, $startDate->getTimestamp());
    }
}

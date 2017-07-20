<?php

namespace SuplaBundle\Tests\Model\Schedule\SchedulePlanner;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Model\IODeviceManager;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\Schedule\SchedulePlanners\CompositeSchedulePlanner;

class ScheduleManagerTest extends \PHPUnit_Framework_TestCase {
    private $doctrine;
    private $deviceManager;
    /** @var CompositeSchedulePlanner|\PHPUnit_Framework_MockObject_MockObject */
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
        $this->manager = new ScheduleManager($this->doctrine, $this->deviceManager, $this->schedulePlanner);
    }

    // https://github.com/SUPLA/supla-cloud/issues/82
    public function testDoNotCalculateForPastDates() {
        $schedule = new ScheduleWithTimezone('*/100');
        $latestExecutionInThePast = new \DateTime('-5days');
        $this->scheduledExecutionsRepository->method('findBy')->willReturn([new ScheduledExecution($schedule, $latestExecutionInThePast)]);
        $this->schedulePlanner->method('calculateNextRunDatesUntil')->willReturnArgument(2);
        $currentTimestamp = time();
        $startDate = $this->manager->getNextRunDates($schedule);
        $this->assertGreaterThanOrEqual($currentTimestamp, $startDate->getTimestamp());
    }
}

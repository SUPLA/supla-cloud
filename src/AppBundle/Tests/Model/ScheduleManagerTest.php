<?php

use AppBundle\Entity\Schedule;
use AppBundle\Model\ScheduleManager;

class ScheduleManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \AppBundle\Entity\User|PHPUnit_Framework_MockObject_MockObject */
    private $user;
    /** @var \Doctrine\Bundle\DoctrineBundle\Registry|PHPUnit_Framework_MockObject_MockObject */
    private $doctrine;
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var Schedule */
    private $schedule;

    protected function setUp()
    {
        $em = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $repo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $this->doctrine = $this->createMock(\Doctrine\Bundle\DoctrineBundle\Registry::class);
        $this->doctrine->expects($this->any())->method('getManager')->willReturn($em);
        $this->doctrine->expects($this->any())->method('getRepository')->willReturn($repo);
        $this->scheduleManager = new ScheduleManager($this->doctrine);
        $this->user = $this->createMock(\AppBundle\Entity\User::class);
        $this->schedule = new Schedule();
        $this->schedule->setUser($this->user);
    }

    public function testGeneratingDatesForSchedule()
    {
        $this->user->expects($this->any())->method('getTimezone')->willReturn(date_default_timezone_get());
        $this->schedule->setCronExpression('*/10 * * * *');
        $this->schedule->setDateStart(\DateTime::createFromFormat(\DateTime::ATOM, '2017-01-29T01:45:00+01:00'));
        $runDates = $this->scheduleManager->getNextRunDates($this->schedule);


    }


}

<?php
namespace SuplaBundle\Tests\Model\SchedulePlanner;

use SuplaBundle\Entity\Schedule;
use SuplaBundle\Model\SchedulePlanners\CompositeSchedulePlanner;
use SuplaBundle\Model\SchedulePlanners\CronExpressionSchedulePlanner;

class CompositeSchedulePlannerTest extends \PHPUnit_Framework_TestCase
{
    /** @var CompositeSchedulePlanner */
    private $planner;

    public function setUp()
    {
        $this->planner = new CompositeSchedulePlanner([new CronExpressionSchedulePlanner()]);
    }

    public function testCalculatingRunDatesUntil()
    {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('*/5 * * * *');
        $runDates = array_map(function (\DateTime $d) {
            return $d->getTimestamp();
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-01-02 00:00', '2017-01-01 00:00'));
        $this->assertNotContains(strtotime('2017-01-01 00:00'), $runDates);
        $this->assertContains(strtotime('2017-01-01 00:05'), $runDates);
        $this->assertContains(strtotime('2017-01-01 00:15'), $runDates);
        $this->assertContains(strtotime('2017-01-01 22:35'), $runDates);
        $this->assertContains(strtotime('2017-01-01 23:55'), $runDates);
        $this->assertContains(strtotime('2017-01-02 00:00'), $runDates);
        $this->assertNotContains(strtotime('2017-01-02 00:10'), $runDates);
    }

    public function testCalculatingRunDatesUntilIfTheFirstOneIsLater()
    {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('23 11 5 12 * 2089');
        $runDates = array_map(function (\DateTime $d) {
            return $d->getTimestamp();
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-01-01 00:00'));
        $this->assertCount(1, $runDates);
        $this->assertContains(strtotime('2089-12-05 11:23'), $runDates);
    }

    public function testCalculatingRunDatesUntilDoesNotThrowAnErrorIfNoMoreDates()
    {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('23 11 5 12 * 2089');
        $runDates = array_map(function ($d) {
            return $d->getTimestamp();
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2099-01-01 00:00'));
        $this->assertCount(1, $runDates);
        $this->assertContains(strtotime('2089-12-05 11:23'), $runDates);
    }

    public function testCalculatingManyDates()
    {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('*/5 * * * *');
        $dates = $this->planner->calculateNextRunDatesUntil($schedule, '+5 days');
        $this->assertGreaterThan(1000, count($dates));
    }
}

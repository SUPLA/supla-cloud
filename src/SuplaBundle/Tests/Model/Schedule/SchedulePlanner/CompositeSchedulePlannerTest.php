<?php
namespace SuplaBundle\Tests\Model\Schedule\SchedulePlanner;

use SuplaBundle\Model\Schedule\SchedulePlanners\CompositeSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\CronExpressionSchedulePlanner;

class CompositeSchedulePlannerTest extends \PHPUnit_Framework_TestCase {
    /** @var CompositeSchedulePlanner */
    private $planner;

    public function setUp() {
        $this->planner = new CompositeSchedulePlanner([new CronExpressionSchedulePlanner()]);
    }

    public function testCalculatingRunDatesUntil() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Europe/Warsaw');
        $runDates = array_map(function (\DateTime $d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-01-02 00:00', '2017-01-01 00:00'));
        $this->assertNotContains('2017-01-01T00:00:00+01:00', $runDates);
        $this->assertContains('2017-01-01T00:05:00+01:00', $runDates);
        $this->assertContains('2017-01-01T00:15:00+01:00', $runDates);
        $this->assertContains('2017-01-01T22:35:00+01:00', $runDates);
        $this->assertContains('2017-01-02T00:00:00+01:00', $runDates);
        $this->assertNotContains('2017-01-02T00:10:00+01:00', $runDates);
    }

    public function testCalculatingWhenDstChanges() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Europe/Warsaw');
        $runDates = array_map(function (\DateTime $d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-03-26 04:00', '2017-03-26 01:00'));
        $this->assertNotContains('2017-03-26T02:00:00+01:00', $runDates);
    }

    public function testCalculatingRunForMelbourne() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Australia/Melbourne');
        $runDates = array_map(function (\DateTime $d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-01-01 01:00', '2017-01-01 00:00'));
        $this->assertNotContains('2017-01-01T00:00:00+11:00', $runDates);
        $this->assertContains('2017-01-01T00:30:00+11:00', $runDates);
        $this->assertContains('2017-01-01T01:00:00+11:00', $runDates);
        $this->assertNotContains('2017-01-01T01:10:00+01:00', $runDates);
    }

    public function testCalculatingRunDatesUntilIfTheFirstOneIsLater() {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('23 11 5 12 * 2089');
        $runDates = array_map(function (\DateTime $d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-01-01 00:00'));
        $this->assertCount(1, $runDates);
        $this->assertContains('2089-12-05T11:23:00+01:00', $runDates);
    }

    public function testCalculatingRunDatesUntilDoesNotThrowAnErrorIfNoMoreDates() {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('23 11 5 12 * 2089');
        $runDates = array_map(function ($d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2099-01-01 00:00'));
        $this->assertCount(1, $runDates);
        $this->assertContains('2089-12-05T11:23:00+01:00', $runDates);
    }

    public function testCalculatingManyDates() {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('*/5 * * * *');
        $dates = $this->planner->calculateNextRunDatesUntil($schedule, '+5 days');
        $this->assertGreaterThan(1000, count($dates));
    }
}

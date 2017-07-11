<?php
namespace SuplaBundle\Tests\Model\Schedule\SchedulePlanner;

use SuplaBundle\Entity\Schedule;
use SuplaBundle\Model\Schedule\SchedulePlanners\IntervalSchedulePlanner;

class IntervalSchedulePlannerTest extends \PHPUnit_Framework_TestCase {
    /**
     * @dataProvider calculatingNextRunDateProvider
     */
    public function testCalculatingNextRunDate($startDate, $cronExpression, $expectedNextRunDate) {
        $schedulePlanner = new IntervalSchedulePlanner();
        $schedule = new ScheduleWithTimezone($cronExpression);
        $format = 'Y-m-d H:i';
        $startDate = \DateTime::createFromFormat($format, $startDate);
        $this->assertTrue($schedulePlanner->canCalculateFor($schedule));
        $nextRunDate = $schedulePlanner->calculateNextRunDate($schedule, $startDate);
        $this->assertEquals($expectedNextRunDate, $nextRunDate->format($format));
    }

    public function calculatingNextRunDateProvider() {
        return [
            ['2017-01-01 00:00', '*/5 * * * *', '2017-01-01 00:05'],
            ['2017-01-01 00:05', '*/5 * * * *', '2017-01-01 00:10'],
            ['2017-01-01 00:01', '*/5 * * * *', '2017-01-01 00:05'],
            ['2017-01-01 00:00', '*/10 * * * *', '2017-01-01 00:10'],
            ['2017-01-01 00:05', '*/10 * * * *', '2017-01-01 00:15'],
            ['2017-01-01 00:04', '*/10 * * * *', '2017-01-01 00:15'],
            ['2017-01-01 00:04', '*/10', '2017-01-01 00:15'],
            ['2017-01-01 14:56', '*/10 * * * *', '2017-01-01 15:05'],
            ['2017-01-01 17:07', '*/45 * * * *', '2017-01-01 17:50'],
            ['2017-01-01 17:08', '*/45 * * * *', '2017-01-01 17:55'],
        ];
    }

    /**
     * @dataProvider invalidCronExpressions
     */
    public function testDoesNotSupportInvalidIntervalExpressions($invalidCronExpression) {
        $schedulePlanner = new IntervalSchedulePlanner();
        $schedule = new Schedule();
        $schedule->setTimeExpression($invalidCronExpression);
        $this->assertFalse($schedulePlanner->canCalculateFor($schedule));
    }

    public function invalidCronExpressions() {
        return [
            [null],
            [''],
            ['*'],
            ['S * * * *'],
            ['* * * * * * *'],
            ['*/5 2 * * *'],
            ['5 * * * *'],
        ];
    }
}

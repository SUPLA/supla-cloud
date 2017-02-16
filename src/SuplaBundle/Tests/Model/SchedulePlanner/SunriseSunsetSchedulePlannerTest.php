<?php
namespace SuplaBundle\Tests\Model\SchedulePlanner;

use SuplaBundle\Entity\Schedule;
use SuplaBundle\Model\SchedulePlanners\CronExpressionSchedulePlanner;
use SuplaBundle\Model\SchedulePlanners\SunriseSunsetSchedulePlanner;

class SunriseSunsetSchedulePlannerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider calculatingNextRunDateProvider
     */
    public function testCalculatingNextRunDate($startDate, $cronExpression, $expectedNextRunDate)
    {
        $schedulePlanner = new SunriseSunsetSchedulePlanner();
//        $schedule = new Schedule();
//        $schedule->setCronExpression($cronExpression);
//        $format = 'Y-m-d H:i';
//        $startDate = \DateTime::createFromFormat($format, $startDate);
//        $this->assertTrue($schedulePlanner->canCalculateFor($schedule));
//        $this->assertEquals($expectedNextRunDate, $schedulePlanner->calculateNextRunDate($schedule, $startDate)->format($format));
    }

    public function calculatingNextRunDateProvider()
    {
        return [
            ['2017-01-01 00:00', '23 11 5 12 * 2089', '2089-12-05 11:23'], // run once
            ['2017-01-01 00:00', '*/5 * * * *', '2017-01-01 00:05'], // every 5 minutes
            ['2017-01-01 00:00', '34 12 * * 4', '2017-01-05 12:34'], // 12:34 in thursdays
            ['2017-01-01 00:00', '0 23 13 * *', '2017-01-13 23:00'], // 13 day of month, 23:00
            ['2017-01-01 00:00', '0 7 29 2 *', '2020-02-29 07:00'], // 29 February, 7:00 from 2017 year
            ['2021-01-01 00:00', '0 7 29 2 *', '2024-02-29 07:00'], // 29 February, 7:00 from 2021 year
            ['2021-01-01 00:00', '0 3,19 * * *', '2021-01-01 03:00'],
        ];
    }

}

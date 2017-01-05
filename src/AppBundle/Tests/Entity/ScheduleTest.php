<?php
namespace AppBundle\Tests\Entity;

use AppBundle\Entity\Schedule;

class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    public function testSettingTheCronExpression()
    {
        $schedule = new Schedule();
        $schedule->setCronExpression('* * * * * *');
        $this->assertEquals('* * * * * *', $schedule->getCronExpression());
    }

    /**
     * @dataProvider calculatingNextRunDateProvider
     */
    public function testCalculatingNextRunDate($startDate, $cronExpression, $expectedNextRunDate)
    {
        $schedule = new Schedule();
        $schedule->setCronExpression($cronExpression);
        $format = 'Y-m-d H:i';
        $this->assertEquals($expectedNextRunDate, $schedule->getNextRunDate($startDate)->format($format));
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
        ];
    }

    public function testCalculatingRunDatesUntil()
    {
        $schedule = new Schedule();
        $schedule->setCronExpression('*/5 * * * *');
        $runDates = array_map(function ($d) {
            return $d->getTimestamp();
        }, $schedule->getRunDatesUntil('2017-01-02 00:00', '2017-01-01 00:00'));
        $this->assertNotContains(strtotime('2017-01-01 00:00'), $runDates);
        $this->assertContains(strtotime('2017-01-01 00:05'), $runDates);
        $this->assertContains(strtotime('2017-01-01 00:15'), $runDates);
        $this->assertContains(strtotime('2017-01-01 22:35'), $runDates);
        $this->assertContains(strtotime('2017-01-01 23:55'), $runDates);
        $this->assertContains(strtotime('2017-01-02 00:00'), $runDates);
        $this->assertNotContains(strtotime('2017-01-02 00:05'), $runDates);
    }

    public function testCalculatingRunDatesUntilIfTheFirstOneIsLater()
    {
        $schedule = new Schedule();
        $schedule->setCronExpression('23 11 5 12 * 2089');
        $runDates = array_map(function ($d) {
            return $d->getTimestamp();
        }, $schedule->getRunDatesUntil('2017-01-01 00:00'));
        $this->assertCount(1, $runDates);
        $this->assertContains(strtotime('2089-12-05 11:23'), $runDates);
    }

    public function testCalculatingRunDatesUntilDoesNotThrowAnErrorIfNoMoreDates()
    {
        $schedule = new Schedule();
        $schedule->setCronExpression('23 11 5 12 * 2089');
        $runDates = array_map(function ($d) {
            return $d->getTimestamp();
        }, $schedule->getRunDatesUntil('2099-01-01 00:00'));
        $this->assertCount(1, $runDates);
        $this->assertContains(strtotime('2089-12-05 11:23'), $runDates);
    }
}

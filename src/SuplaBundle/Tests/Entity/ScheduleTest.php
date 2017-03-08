<?php
namespace SuplaBundle\Tests\Entity;

use Assert\InvalidArgumentException;
use SuplaBundle\Entity\Schedule;

class ScheduleTest extends \PHPUnit_Framework_TestCase {
    public function testSettingTheCronExpression() {
        $schedule = new Schedule();
        $schedule->setTimeExpression('* * * * * *');
        $this->assertEquals('* * * * * *', $schedule->getTimeExpression());
    }

    public function testFillRequiredTimeExpression() {
        $this->setExpectedException(InvalidArgumentException::class);
        $schedule = new Schedule();
        $schedule->fill([]);
    }

    public function testFillFillsCaption() {
        $schedule = new Schedule();
        $schedule->fill(['timeExpression' => '*', 'caption' => 'My Caption']);
        $this->assertEquals('My Caption', $schedule->getCaption());
    }
}

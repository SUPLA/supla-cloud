<?php
namespace SuplaBundle\Tests\Entity;

use SuplaBundle\Entity\Schedule;

class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    public function testSettingTheCronExpression()
    {
        $schedule = new Schedule();
        $schedule->setTimeExpression('* * * * * *');
        $this->assertEquals('* * * * * *', $schedule->getTimeExpression());
    }
}

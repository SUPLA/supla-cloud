<?php
namespace SuplaBundle\Tests\Entity;

use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Enums\ScheduleActionExecutionResult;

class ScheduledExecutionTest extends \PHPUnit_Framework_TestCase {
    public function testUnknownResultByDefault() {
        $execution = new ScheduledExecution($this->createMock(Schedule::class), new \DateTime());
        $this->assertEquals(ScheduleActionExecutionResult::UNKNOWN(), $execution->getResult());
    }
}

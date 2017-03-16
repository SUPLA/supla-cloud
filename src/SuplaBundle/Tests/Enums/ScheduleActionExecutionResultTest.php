<?php
namespace SuplaBundle\Tests\Enums;

use SuplaBundle\Enums\ScheduleActionExecutionResult;

class ScheduleActionExecutionResultTest extends \PHPUnit_Framework_TestCase {
    public function testEveryValueHasCaption() {
        $this->assertCount(count(ScheduleActionExecutionResult::values()), ScheduleActionExecutionResult::captions(),
            'Have you forgot to add a caption for the new ScheduleActionExecutionResult value?');
    }
}

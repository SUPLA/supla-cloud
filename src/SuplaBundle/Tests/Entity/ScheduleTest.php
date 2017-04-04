<?php
namespace SuplaBundle\Tests\Entity;

use Assert\InvalidArgumentException;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Enums\ScheduleAction;

class ScheduleTest extends \PHPUnit_Framework_TestCase {
    public function testSettingTheCronExpression() {
        $schedule = new Schedule();
        $schedule->setTimeExpression('* * * * * *');
        $this->assertEquals('* * * * * *', $schedule->getTimeExpression());
    }

    public function testFillRequiredTimeExpression() {
        $this->expectException(InvalidArgumentException::class);
        $schedule = new Schedule();
        $schedule->fill([]);
    }

    public function testFillFillsCaption() {
        $schedule = new Schedule();
        $schedule->fill(['scheduleMode' => 'hourly', 'timeExpression' => '*', 'caption' => 'My Caption']);
        $this->assertEquals('My Caption', $schedule->getCaption());
    }

    public function testRequiresActionParamsForRgbLighting() {
        $this->expectException(InvalidArgumentException::class);
        $schedule = new Schedule();
        $schedule->fill(['scheduleMode' => 'hourly', 'timeExpression' => '*', 'action' => ScheduleAction::SET_RGBW_PARAMETERS]);
    }

    public function testSettingActionParamsAsArray() {
        $schedule = new Schedule();
        $schedule->fill([
            'scheduleMode' => 'hourly',
            'timeExpression' => '*',
            'action' => ScheduleAction::REVEAL_PARTIALLY,
            'actionParam' => ['percentage' => 12],
        ]);
        $this->assertEquals('{"percentage":12}', $schedule->getActionParam());
    }

    public function testSettingInvalidActionParam() {
        $this->expectException(InvalidArgumentException::class);
        $schedule = new Schedule();
        $schedule->fill(['scheduleMode' => 'hourly', 'timeExpression' => '*', 'actionParam' => '{"color": 123']);
    }
}

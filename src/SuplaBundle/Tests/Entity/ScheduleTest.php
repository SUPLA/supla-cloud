<?php
namespace SuplaBundle\Tests\Entity;

use Assert\InvalidArgumentException;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Supla\SuplaConst;

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
        $schedule->fill(['timeExpression' => '*', 'caption' => 'My Caption']);
        $this->assertEquals('My Caption', $schedule->getCaption());
    }

    public function testRequiresActionParamsForRgbLighting() {
        $this->expectException(InvalidArgumentException::class);
        $schedule = new Schedule();
        $schedule->fill(['timeExpression' => '*', 'action' => SuplaConst::ACTION_SET_DIMRGBW_PARAMETERS]);
    }

    public function testSettingActionParamsAsString() {
        $schedule = new Schedule();
        $schedule->fill(['timeExpression' => '*', 'actionParam' => '{"color":123}']);
        $this->assertEquals('{"color":123}', $schedule->getActionParam());
    }

    public function testSettingActionParamsAsArray() {
        $schedule = new Schedule();
        $schedule->fill(['timeExpression' => '*', 'actionParam' => ['color' => 123]]);
        $this->assertEquals('{"color":123}', $schedule->getActionParam());
    }

    public function testSettingInvalidActionParam() {
        $this->expectException(InvalidArgumentException::class);
        $schedule = new Schedule();
        $schedule->fill(['timeExpression' => '*', 'actionParam' => '{"color": 123']);
    }
}

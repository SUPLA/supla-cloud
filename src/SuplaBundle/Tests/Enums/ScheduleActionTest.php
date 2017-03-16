<?php
namespace SuplaBundle\Tests\Enums;

use Assert\InvalidArgumentException;
use SuplaBundle\Enums\ScheduleAction;

class ScheduleActionTest extends \PHPUnit_Framework_TestCase {
    /**
     * @dataProvider validatingActionParamsProvider
     */
    public function testValidatingActionParams(ScheduleAction $action, $actionParams, bool $expectValid) {
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        $action->validateActionParam($actionParams);
    }

    public function validatingActionParamsProvider() {
        return [
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => 0], true],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => 50], true],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => 100], true],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => -1], false],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => 101], false],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage2' => 50], false],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => 50, 'other' => 50], false],
            [ScheduleAction::REVEAL_PARTIALLY(), [], false],
            [ScheduleAction::REVEAL_PARTIALLY(), null, false],

            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 0, 'color_brightness' => 0], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 359, 'color_brightness' => 100], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 'random', 'color_brightness' => 100], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => 0], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => 100], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => 50, 'hue' => 359, 'color_brightness' => 100], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['blabla' => 50, 'hue' => 359, 'color_brightness' => 100], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 360, 'color_brightness' => 100], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => -1, 'color_brightness' => 100], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 0, 'color_brightness' => 101], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 0, 'color_brightness' => -1], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 0], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => -1], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => 101], false],

            [ScheduleAction::CLOSE(), null, true],
            [ScheduleAction::CLOSE(), [], false],
        ];
    }

    public function testEveryValueHasCaption() {
        $this->assertCount(count(ScheduleAction::values()), ScheduleAction::captions(),
            'Have you forgot to add a caption for the new ScheduleAction value?');
    }
}

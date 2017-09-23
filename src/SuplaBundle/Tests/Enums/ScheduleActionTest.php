<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

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
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => '100'], true],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => -1], false],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => 101], false],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage2' => 50], false],
            [ScheduleAction::REVEAL_PARTIALLY(), ['percentage' => 50, 'other' => 50], false],
            [ScheduleAction::REVEAL_PARTIALLY(), [], false],
            [ScheduleAction::REVEAL_PARTIALLY(), null, false],

            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 0, 'color_brightness' => 0], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 359, 'color_brightness' => 100], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 'random', 'color_brightness' => 100], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 'white', 'color_brightness' => 100], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => 0], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => 100], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => 50, 'hue' => 359, 'color_brightness' => 100], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => '50', 'hue' => '359', 'color_brightness' => '100'], true],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['blabla' => 50, 'hue' => 359, 'color_brightness' => 100], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 360, 'color_brightness' => 100], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => -1, 'color_brightness' => 100], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 0, 'color_brightness' => 101], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 0, 'color_brightness' => -1], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 0], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['hue' => 'black', 'color_brightness' => 100], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => -1], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => 101], false],
            [ScheduleAction::SET_RGBW_PARAMETERS(), ['brightness' => 'ala'], false],

            [ScheduleAction::CLOSE(), null, true],
            [ScheduleAction::CLOSE(), [], false],
        ];
    }

    public function testEveryValueHasCaption() {
        $this->assertCount(
            count(ScheduleAction::values()),
            ScheduleAction::captions(),
            'Have you forgot to add a caption for the new ScheduleAction value?'
        );
    }

    public function testConvertsNumericActionParamsToInts() {
        $params = ['brightness' => '50', 'hue' => '359', 'color_brightness' => '100'];
        $params = ScheduleAction::SET_RGBW_PARAMETERS()->validateActionParam($params);
        $this->assertSame(50, $params['brightness']);
        $this->assertSame(359, $params['hue']);
        $this->assertSame(100, $params['color_brightness']);
    }
}

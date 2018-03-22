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

namespace SuplaBundle\Tests\Entity;

use Assert\InvalidArgumentException;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Enums\ChannelFunctionAction;

class ScheduleTest extends \PHPUnit_Framework_TestCase {
    public function testSettingTheCronExpression() {
        $schedule = new Schedule();
        $schedule->setTimeExpression('*/5 * * * * *');
        $this->assertEquals('*/5 * * * * *', $schedule->getTimeExpression());
    }

    public function testFillRequiredTimeExpression() {
        $this->expectException(InvalidArgumentException::class);
        $schedule = new Schedule();
        $schedule->fill([]);
    }

    public function testFillFillsCaption() {
        $schedule = new Schedule();
        $schedule->fill(['mode' => 'hourly', 'timeExpression' => '6', 'caption' => 'My Caption']);
        $this->assertEquals('My Caption', $schedule->getCaption());
    }

    public function testRequiresActionParamsForRgbLighting() {
        $this->expectException(InvalidArgumentException::class);
        $schedule = new Schedule();
        $schedule->fill(['mode' => 'hourly', 'timeExpression' => '*', 'actionId' => ChannelFunctionAction::SET_RGBW_PARAMETERS]);
    }

    public function testSettingActionParamsAsArray() {
        $schedule = new Schedule();
        $schedule->fill([
            'mode' => 'hourly',
            'timeExpression' => '3',
            'actionId' => ChannelFunctionAction::REVEAL_PARTIALLY,
            'actionParam' => ['percentage' => 12],
        ]);
        $this->assertEquals(['percentage' => 12], $schedule->getActionParam());
    }

    public function testSettingInvalidActionParam() {
        $this->expectException(InvalidArgumentException::class);
        $schedule = new Schedule();
        $schedule->fill(['mode' => 'hourly', 'timeExpression' => '*', 'actionParam' => '{"color": 123']);
    }

    public function testSettingTooShortTimeExpression() {
        $this->expectException(\InvalidArgumentException::class);
        (new Schedule())->setTimeExpression('* * * * *');
    }
}

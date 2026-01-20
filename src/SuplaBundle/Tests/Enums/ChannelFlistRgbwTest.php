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

use PHPUnit\Framework\TestCase;
use SuplaBundle\Enums\ChannelFlistRgbw;
use SuplaBundle\Enums\ChannelFunction;

class ChannelFlistRgbwTest extends TestCase {
    /** @dataProvider supportedFunctionsTestCases */
    public function testGettingSupportedFunctions(int $functionList, array $expectedFuncions) {
        $this->assertEquals($expectedFuncions, ChannelFlistRgbw::getSupportedFunctions($functionList));
    }

    public static function supportedFunctionsTestCases() {
        return [
            [0, []],
            [1, [ChannelFunction::DIMMER()]],
            [2, [ChannelFunction::RGBLIGHTING()]],
            [3, [ChannelFunction::DIMMER(), ChannelFunction::RGBLIGHTING()]],
            [16, [ChannelFunction::DIMMER_CCT_AND_RGB()]],
            [15, [
                ChannelFunction::DIMMER(),
                ChannelFunction::RGBLIGHTING(),
                ChannelFunction::DIMMERANDRGBLIGHTING(),
                ChannelFunction::DIMMER_CCT(),
            ]],
        ];
    }

    public function testEveryBitIsExclusive() {
        $bitsSum = 0;
        foreach (ChannelFlistRgbw::values() as $bit) {
            $newBitsSum = $bitsSum | $bit->getValue();
            $this->assertNotEquals($newBitsSum, $bitsSum, 'Non exclusive detected on ' . $bit->getKey());
            $bitsSum = $newBitsSum;
        }
    }
}

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
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlist;

class ChannelFunctionBitsFlistTest extends TestCase {
    /** @dataProvider supportedFunctionsTestCases */
    public function testGettingSupportedFunctions(int $functionList, array $expectedFuncions) {
        $this->assertEquals($expectedFuncions, ChannelFunctionBitsFlist::getSupportedFunctions($functionList));
    }

    public static function supportedFunctionsTestCases() {
        return [
            [0, []],
            [1, [ChannelFunction::CONTROLLINGTHEGATEWAYLOCK()]],
            [2, [ChannelFunction::CONTROLLINGTHEGATE()]],
            [3, [ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), ChannelFunction::CONTROLLINGTHEGATE()]],
            [16, [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER()]],
            [16, [ChannelFunction::CONTROLLINGTHEROLLERSHUTTER()]],
            [27, [
                ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(),
                ChannelFunction::CONTROLLINGTHEGATE(),
                ChannelFunction::CONTROLLINGTHEDOORLOCK(),
                ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
            ]],
            [100, [
                ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
                ChannelFunction::POWERSWITCH(),
                ChannelFunction::LIGHTSWITCH(),
            ]],
            [32512, [
                ChannelFunction::THERMOMETER(),
                ChannelFunction::HUMIDITYANDTEMPERATURE(),
                ChannelFunction::HUMIDITY(),
                ChannelFunction::WINDSENSOR(),
                ChannelFunction::PRESSURESENSOR(),
                ChannelFunction::RAINSENSOR(),
                ChannelFunction::WEIGHTSENSOR(),
            ]],
            [0xFF000000, [
                ChannelFunction::VERTICAL_BLIND(),
                ChannelFunction::ROLLER_GARAGE_DOOR(),
                ChannelFunction::PUMPSWITCH(),
                ChannelFunction::HEATORCOLDSOURCESWITCH(),
            ]],
            [0x00008000, [ChannelFunction::CONTROLLINGTHEROOFWINDOW()]],
            [0x00040000, [ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL()]],
        ];
    }

    public function testEveryBitIsExclusive() {
        $bitsSum = 0;
        foreach (ChannelFunctionBitsFlist::values() as $bit) {
            $newBitsSum = $bitsSum | $bit->getValue();
            $this->assertNotEquals($newBitsSum, $bitsSum, 'Non exclusive detected on ' . $bit->getKey());
            $bitsSum = $newBitsSum;
        }
    }
}

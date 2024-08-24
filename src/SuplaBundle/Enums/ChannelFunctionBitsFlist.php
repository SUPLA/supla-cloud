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

namespace SuplaBundle\Enums;

final class ChannelFunctionBitsFlist extends ChannelFunctionBits {
    const CONTROLLINGTHEGATEWAYLOCK = 1 << 0;
    const CONTROLLINGTHEGATE = 1 << 1;
    const CONTROLLINGTHEGARAGEDOOR = 1 << 2;
    const CONTROLLINGTHEDOORLOCK = 1 << 3;
    const CONTROLLINGTHEROLLERSHUTTER = 1 << 4;
    const POWERSWITCH = 1 << 5;
    const LIGHTSWITCH = 1 << 6;
    const STAIRCASETIMER = 1 << 7;
    const THERMOMETER = 1 << 8;
    const HUMIDITYANDTEMPERATURE = 1 << 9;
    const HUMIDITY = 1 << 10;
    const WINDSENSOR = 1 << 11;
    const PRESSURESENSOR = 1 << 12;
    const RAINSENSOR = 1 << 13;
    const WEIGHTSENSOR = 1 << 14;
    const CONTROLLINGTHEROOFWINDOW = 1 << 15;
    const CONTROLLINGTHEFACADEBLIND = 1 << 16;
    const HVAC_THERMOSTAT = 1 << 17;
    const HVAC_THERMOSTAT_HEAT_COOL = 1 << 18;
    const HVAC_THERMOSTAT_DIFFERENTIAL = 1 << 19;
    const HVAC_DOMESTIC_HOT_WATER = 1 << 20;
    const TERRACE_AWNING = 1 << 21;
    const PROJECTOR_SCREEN = 1 << 22;
    const CURTAIN = 1 << 23;
    const VERTICAL_BLIND = 1 << 24;
    const ROLLER_GARAGE_DOOR = 1 << 25;
    const PUMPSWITCH = 1 << 26;
    const HEATORCOLDSOURCESWITCH = 1 << 27;

    public static function getSupportedFunctions(int $functionList): array {
        return array_map(function (ChannelFunctionBitsFlist $bit) {
            return ChannelFunction::values()[$bit->getKey()];
        }, self::getSupportedFeatures($functionList));
    }
}

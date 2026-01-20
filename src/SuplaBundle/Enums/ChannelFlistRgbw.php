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

final class ChannelFlistRgbw extends ChannelBits {
    const DIMMER = 0x00000001;
    const RGBLIGHTING = 0x00000002;
    const DIMMERANDRGBLIGHTING = 0x00000004;
    const DIMMER_CCT = 0x00000008;
    const DIMMER_CCT_AND_RGB = 0x00000010;

    public static function getSupportedFunctions(int $functionList): array {
        return array_map(function (ChannelFlistRgbw $bit) {
            return ChannelFunction::values()[$bit->getKey()];
        }, self::getSupportedFeatures($functionList));
    }
}

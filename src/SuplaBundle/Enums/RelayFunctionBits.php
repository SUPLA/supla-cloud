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

use MyCLabs\Enum\Enum;

final class RelayFunctionBits extends Enum {
    const CONTROLLINGTHEGATEWAYLOCK = 0x00000001;
    const CONTROLLINGTHEGATE = 0x00000002;
    const CONTROLLINGTHEGARAGEDOOR = 0x00000004;
    const CONTROLLINGTHEDOORLOCK = 0x00000008;
    const CONTROLLINGTHEROLLERSHUTTER = 0x00000010;
    const POWERSWITCH = 0x00000020;
    const LIGHTSWITCH = 0x00000040;
    const STAIRCASETIMER = 0x00000080;

    public static function getSupportedFunctions(int $functionList): array {
        $supportedFunctions = [];
        foreach (self::values() as $bit) {
            if ($functionList & $bit->getValue()) {
                $supportedFunctions[] = ChannelFunction::values()[$bit->getKey()];
            }
        }
        return $supportedFunctions;
    }
}

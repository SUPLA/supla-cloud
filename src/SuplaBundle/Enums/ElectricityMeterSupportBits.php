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
use SuplaBundle\Utils\StringUtils;

final class ElectricityMeterSupportBits extends Enum {
    const FREQUENCY = 0x0001;
    const VOLTAGE = 0x0002;
    const CURRENT = 0x0004;
    const POWER_ACTIVE = 0x0008;
    const POWER_REACTIVE = 0x0010;
    const POWER_APPARENT = 0x0020;
    const POWER_FACTOR = 0x0040;
    const PHASE_ANGLE = 0x0080;
    const FORWARD_ACTIVE_ENERGY = 0x0100;
    const REVERSE_ACTIVE_ENERGY = 0x0200;
    const FORWARD_REACTIVE_ENERGY = 0x0400;
    const REVERSE_REACTIVE_ENERGY = 0x0800;

    public static function nullifyUnsupportedFeatures(int $supportMask, array $state): array {
        foreach (self::toArray() as $name => $value) {
            if (!($supportMask & $value)) {
                $key = StringUtils::snakeCaseToCamelCase($name);
                $possibleKeys = [$key, $key . 'Phase1', $key . 'Phase2', $key . 'Phase3',
                    'total' . ucfirst($key) . 'Phase1', 'total' . ucfirst($key) . 'Phase2', 'total' . ucfirst($key) . 'Phase3'];
                foreach ($possibleKeys as $keyToNullify) {
                    if (isset($state[$keyToNullify])) {
                        $state[$keyToNullify] = null;
                    }
                }
            }
        }
        return $state;
    }
}

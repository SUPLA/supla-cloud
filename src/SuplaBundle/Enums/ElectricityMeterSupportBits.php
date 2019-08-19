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
    const TOTAL_FORWARD_ACTIVE_ENERGY = 0x0100;
    const TOTAL_REVERSE_ACTIVE_ENERGY = 0x0200;
    const TOTAL_FORWARD_REACTIVE_ENERGY = 0x0400;
    const TOTAL_REVERSE_REACTIVE_ENERGY = 0x0800;

    /**
     * Possible values returned by the SUPLA-Server when queried about EM (ElectricityMeter) state.
     * The order of these elements IS important, because it represents the order in which the values are returned from the SUPLA-Server.
     * @var array
     */
    public static $POSSIBLE_STATE_KEYS = [
        'frequency', 'voltagePhase1', 'voltagePhase2', 'voltagePhase3', 'currentPhase1', 'currentPhase2', 'currentPhase3',
        'powerActivePhase1', 'powerActivePhase2', 'powerActivePhase3', 'powerReactivePhase1', 'powerReactivePhase2',
        'powerReactivePhase3', 'powerApparentPhase1', 'powerApparentPhase2', 'powerApparentPhase3', 'powerFactorPhase1',
        'powerFactorPhase2', 'powerFactorPhase3', 'phaseAnglePhase1', 'phaseAnglePhase2', 'phaseAnglePhase3',
        'totalForwardActiveEnergyPhase1', 'totalForwardActiveEnergyPhase2', 'totalForwardActiveEnergyPhase3',
        'totalReverseActiveEnergyPhase1', 'totalReverseActiveEnergyPhase2', 'totalReverseActiveEnergyPhase3',
        'totalForwardReactiveEnergyPhase1', 'totalForwardReactiveEnergyPhase2', 'totalForwardReactiveEnergyPhase3',
        'totalReverseReactiveEnergyPhase1', 'totalReverseReactiveEnergyPhase2', 'totalReverseReactiveEnergyPhase3',
    ];

    /**
     * Multipliers of the values returned by the SUPLA-Server.
     * @var array
     */
    private static $SUPLA_SERVER_VALUES_MULTIPLIERS = [
        'frequency' => 0.01,
        'voltage' => 0.01,
        'current' => 0.001,
        'powerActive' => 0.00001,
        'powerReactive' => 0.00001,
        'powerApparent' => 0.00001,
        'powerFactor' => 0.001,
        'phaseAngle' => 0.1,
        'totalForwardActiveEnergy' => 0.00001,
        'totalReverseActiveEnergy' => 0.00001,
        'totalForwardReactiveEnergy' => 0.00001,
        'totalReverseReactiveEnergy' => 0.00001,
    ];

    public static function nullifyUnsupportedFeatures(int $supportMask, array $state): array {
        foreach (self::toArray() as $name => $value) {
            if (!($supportMask & $value)) {
                $key = StringUtils::snakeCaseToCamelCase($name);
                $possibleKeys = [$key, $key . 'Phase1', $key . 'Phase2', $key . 'Phase3'];
                foreach ($possibleKeys as $keyToNullify) {
                    if (isset($state[$keyToNullify])) {
                        $state[$keyToNullify] = null;
                    }
                }
            }
        }
        return $state;
    }

    public static function transformValuesFromServer(array $state): array {
        foreach ($state as $key => &$value) {
            $key = preg_replace('#(Phase\d)?$#', '', $key);
            if (isset(self::$SUPLA_SERVER_VALUES_MULTIPLIERS[$key])) {
                $value = $value * self::$SUPLA_SERVER_VALUES_MULTIPLIERS[$key];
            }
        }
        return $state;
    }
}

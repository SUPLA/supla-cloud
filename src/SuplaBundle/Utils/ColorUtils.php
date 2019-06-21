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

namespace SuplaBundle\Utils;

use Assert\Assertion;

final class ColorUtils {
    private function __construct() {
    }

    public static function hueToDec(int $hue): int {
        Assertion::between($hue, 0, 359, 'Hue value must be between 0 and 359');
        list($r, $g, $b) = self::hsvToRgb($hue);
        return ($r << 16) + ($g << 8) + $b;
    }

    /** @see https://gist.github.com/vkbo/2323023 */
    private static function hsvToRgb(int $iH, int $iS = 1, int $iV = 1): array {
        $dS = $iS;
        $dV = $iV;
        $dC = $dV * $dS;
        $dH = $iH / 60.0;
        $dT = $dH;
        while ($dT >= 2.0) $dT -= 2.0;
        $dX = $dC * (1 - abs($dT - 1));
        switch (floor($dH)) {
            case 0:
                $dR = $dC;
                $dG = $dX;
                $dB = 0.0;
                break;
            case 1:
                $dR = $dX;
                $dG = $dC;
                $dB = 0.0;
                break;
            case 2:
                $dR = 0.0;
                $dG = $dC;
                $dB = $dX;
                break;
            case 3:
                $dR = 0.0;
                $dG = $dX;
                $dB = $dC;
                break;
            case 4:
                $dR = $dX;
                $dG = 0.0;
                $dB = $dC;
                break;
            case 5:
                $dR = $dC;
                $dG = 0.0;
                $dB = $dX;
                break;
            default:
                $dR = 0.0;
                $dG = 0.0;
                $dB = 0.0;
                break;
        }
        $dM = $dV - $dC;
        $dR += $dM;
        $dG += $dM;
        $dB += $dM;
        $dR *= 255;
        $dG *= 255;
        $dB *= 255;
        return [round($dR), round($dG), round($dB)];
    }
}

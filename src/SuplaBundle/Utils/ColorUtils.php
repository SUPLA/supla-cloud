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

    public static function decToHue(int $dec): int {
        list($hue, ,) = self::hexToHsl(self::decToHex($dec, ''));
        return $hue;
    }

    /** @see https://gist.github.com/bedeabza/10463089 */
    private static function hexToHsl(string $hex): array {
        $hex = [$hex[0] . $hex[1], $hex[2] . $hex[3], $hex[4] . $hex[5]];
        $rgb = array_map(function ($part) {
            return hexdec($part) / 255;
        }, $hex);
        $max = max($rgb);
        $min = min($rgb);
        $l = ($max + $min) / 2;
        if ($max == $min) {
            $h = $s = 0;
        } else {
            $diff = $max - $min;
            $s = $l > 0.5 ? $diff / (2 - $max - $min) : $diff / ($max + $min);
            switch ($max) {
                case $rgb[0]:
                    $h = ($rgb[1] - $rgb[2]) / $diff + ($rgb[1] < $rgb[2] ? 6 : 0);
                    break;
                case $rgb[1]:
                    $h = ($rgb[2] - $rgb[0]) / $diff + 2;
                    break;
                case $rgb[2]:
                    $h = ($rgb[0] - $rgb[1]) / $diff + 4;
                    break;
            }
            $h /= 6;
        }
        return [round($h * 360), $s, $l];
    }

    public static function decToHex(int $dec, string $prefix = '0x'): string {
        return $prefix . sprintf('%06X', $dec);
    }

    public static function hexToDec(string $color): int {
        return hexdec($color);
    }
}

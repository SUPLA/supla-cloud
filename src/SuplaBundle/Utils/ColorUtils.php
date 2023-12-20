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
        return self::hsvToDec([$hue, 100, 100]);
    }

    /** @see https://gist.github.com/vkbo/2323023 */
    public static function hsvToDec(array $hsv): int {
        [$iH, $iS, $iV] = $hsv;
        $iS /= 100;
        $iV /= 100;
        $dS = $iS;
        $dV = $iV;
        $dC = $dV * $dS;
        $dH = $iH / 60.0;
        $dT = $dH;
        while ($dT >= 2.0) {
            $dT -= 2.0;
        }
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
        $rgb = array_map('intval', array_map('round', [$dR * 255, $dG * 255, $dB * 255]));
        return self::rgbToDec($rgb);
    }

    public static function decToHue(int $dec): int {
        [$hue, ,] = self::hexToHsv(self::decToHex($dec));
        return $hue;
    }

    public static function decToHsv(int $dec): array {
        return self::hexToHsv(self::decToHex($dec));
    }

    /** @see https://stackoverflow.com/a/13887939/878514 */
    public static function hexToHsv(string $hex): array {
        if ($hex[1] == 'x') {
            $hex = substr($hex, 2);
        }
        $hex = [$hex[0] . $hex[1], $hex[2] . $hex[3], $hex[4] . $hex[5]];
        [$R, $G, $B] = array_map(function ($part) {
            return hexdec($part) / 255;
        }, $hex);
        $maxRGB = max($R, $G, $B);
        $minRGB = min($R, $G, $B);
        $chroma = $maxRGB - $minRGB;
        $computedV = 100 * $maxRGB;
        if ($chroma == 0) {
            return [0, 0, $computedV];
        }
        $computedS = 100 * ($chroma / $maxRGB);
        if ($R == $minRGB) {
            $h = 3 - (($G - $B) / $chroma);
        } elseif ($B == $minRGB) {
            $h = 1 - (($R - $G) / $chroma);
        } else {
            $h = 5 - (($B - $R) / $chroma);
        }
        $computedH = 60 * $h;
        return array_map('intval', array_map('round', [$computedH, $computedS, $computedV]));
    }

    public static function decToHex(int $dec, string $prefix = '0x'): string {
        return $prefix . sprintf('%06X', $dec);
    }

    public static function hexToDec(string $color): int {
        return hexdec($color);
    }

    public static function rgbToDec(array $rgb): int {
        return ($rgb[0] << 16) + ($rgb[1] << 8) + $rgb[2];
    }

    public static function decToRgb(int $rgb): array {
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return [$r, $g, $b];
    }

    public static function hsvToRgb(array $hsv): array {
        $dec = self::hsvToDec($hsv);
        return self::decToRgb($dec);
    }
}

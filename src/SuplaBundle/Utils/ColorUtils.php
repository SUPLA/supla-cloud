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
    private const FLOAT_ROUND_EPSILON = 1e-9;

    private function __construct() {
    }

    public static function hueToDec(int $hue): int {
        Assertion::between($hue, 0, 359, 'Hue value must be between 0 and 359');
        return self::hsvToDec([$hue, 100, 100]);
    }

    /** @see https://gist.github.com/vkbo/2323023 */
    public static function hsvToDec(array $hsv): int {
        Assertion::count($hsv, 3, 'HSV value must contain exactly 3 elements.');

        Assertion::numeric($hsv[0], 'Hue must be an integer.');
        Assertion::numeric($hsv[1], 'Saturation must be an integer.');
        Assertion::numeric($hsv[2], 'Value must be an integer.');

        $hsv = array_map('intval', $hsv);

        Assertion::between($hsv[0], 0, 359, 'Hue value must be between 0 and 359.');
        Assertion::between($hsv[1], 0, 100, 'Saturation value must be between 0 and 100.');
        Assertion::between($hsv[2], 0, 100, 'Value/Brightness must be between 0 and 100.');

        [$iH, $iS, $iV] = $hsv;

        $iS /= 100;
        $iV /= 100;

        $dC = $iV * $iS;
        $dH = $iH / 60.0;

        $dT = $dH;
        while ($dT >= 2.0) {
            $dT -= 2.0;
        }

        $dX = $dC * (1 - abs($dT - 1));

        switch ((int)floor($dH)) {
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

        $dM = $iV - $dC;

        $dR += $dM;
        $dG += $dM;
        $dB += $dM;

        $rgb = array_map(fn(float $v): int => self::floatColorPartToInt($v), [$dR, $dG, $dB]);

        return self::rgbToDec($rgb);
    }

    public static function decToHue(int $dec): int {
        [$hue] = self::decToHsv($dec);

        return $hue;
    }

    public static function decToHsv(int $dec): array {
        return self::hexToHsv(self::decToHex($dec));
    }

    /** @see https://stackoverflow.com/a/13887939/878514 */
    public static function hexToHsv(string $hex): array {
        $hex = self::normalizeHex($hex);

        $hex = [
            $hex[0] . $hex[1],
            $hex[2] . $hex[3],
            $hex[4] . $hex[5],
        ];

        [$R, $G, $B] = array_map(static fn(string $part): float => hexdec($part) / 255, $hex);

        $maxRGB = max($R, $G, $B);
        $minRGB = min($R, $G, $B);

        $chroma = $maxRGB - $minRGB;

        $computedV = self::floatPercentageToInt($maxRGB);

        if ($chroma == 0.0) {
            return [0, 0, $computedV];
        }

        $computedS = self::floatPercentageToInt($chroma / $maxRGB);

        if ($R == $minRGB) {
            $h = 3 - (($G - $B) / $chroma);
        } elseif ($B == $minRGB) {
            $h = 1 - (($R - $G) / $chroma);
        } else {
            $h = 5 - (($B - $R) / $chroma);
        }

        $computedH = ((int)round(60 * $h + self::FLOAT_ROUND_EPSILON, 0, PHP_ROUND_HALF_UP)) % 360;
        return [$computedH, $computedS, $computedV];
    }

    public static function decToHex(int $dec, string $prefix = '0x'): string {
        Assertion::between($dec, 0, 0xFFFFFF, 'Decimal RGB color must be between 0 and 0xFFFFFF.');
        return $prefix . sprintf('%06X', $dec);
    }

    public static function hexToDec(string $color): int {
        return hexdec(self::normalizeHex($color));
    }

    public static function rgbToDec(array $rgb): int {
        Assertion::count($rgb, 3, 'RGB value must contain exactly 3 elements.');

        foreach ($rgb as $value) {
            Assertion::integer($value, 'RGB components must be integers.');
            Assertion::between($value, 0, 255, 'RGB components must be between 0 and 255.');
        }

        return ($rgb[0] << 16) + ($rgb[1] << 8) + $rgb[2];
    }

    public static function decToRgb(int $rgb): array {
        Assertion::between(
            $rgb,
            0,
            0xFFFFFF,
            'Decimal RGB color must be between 0 and 0xFFFFFF.'
        );

        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        return [$r, $g, $b];
    }

    public static function hsvToRgb(array $hsv): array {
        return self::decToRgb(self::hsvToDec($hsv));
    }

    private static function normalizeHex(string $hex): string {
        $hex = trim($hex);

        if (str_starts_with($hex, '#')) {
            $hex = substr($hex, 1);
        }

        if (str_starts_with(strtolower($hex), '0x')) {
            $hex = substr($hex, 2);
        }

        Assertion::regex(
            $hex,
            '/\A[0-9a-fA-F]{6}\z/',
            'Hex color must be in RRGGBB format.'
        );

        return strtoupper($hex);
    }

    private static function floatColorPartToInt(float $value): int {
        return max(0, min(255, (int)round(($value * 255) + self::FLOAT_ROUND_EPSILON, 0, PHP_ROUND_HALF_UP)));
    }

    private static function floatPercentageToInt(float $value): int {
        return (int)round(($value * 100) + self::FLOAT_ROUND_EPSILON, 0, PHP_ROUND_HALF_UP);
    }
}

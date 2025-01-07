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

namespace SuplaBundle\Tests\Utils;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Utils\ColorUtils;

class ColorUtilsTest extends TestCase {
    /** @dataProvider colorExamples */
    public function testHueToDec(array $hsv, int $dec) {
        if ($hsv[1] == 100 && $hsv[2] == 100) {
            $this->assertEquals($dec, ColorUtils::hueToDec($hsv[0]));
        } else {
            $this->assertTrue(true);
        }
    }

    /** @dataProvider colorExamples */
    public function testDecToHue(array $hsv, int $dec) {
        $this->assertEquals($hsv[0], ColorUtils::decToHue($dec));
    }

    /** @dataProvider colorExamples */
    public function testDecToHex(array $hsv, int $dec, string $hex) {
        $this->assertEquals($hex, ColorUtils::decToHex($dec));
    }

    /** @dataProvider colorExamples */
    public function testHexToDec(array $hsv, int $dec, string $hex) {
        $this->assertEquals($dec, ColorUtils::hexToDec($hex));
    }

    /** @dataProvider colorExamples */
    public function testDecToHsv(array $hsv, int $dec, string $hex) {
        $this->assertEquals($hsv, ColorUtils::decToHsv($dec));
    }

    /** @dataProvider colorExamples */
    public function testHsvToDec(array $hsv, int $dec, string $hex) {
        $this->assertEqualsWithDelta($dec, ColorUtils::hsvToDec($hsv), 1.0);
    }

    /** @dataProvider colorExamples */
    public function testHexToHsv(array $hsv, int $dec, string $hex) {
        $this->assertEquals($hsv, ColorUtils::hexToHsv($hex));
    }

    /** @dataProvider colorExamples */
    public function testHsvToRgb(array $hsv, int $dec, string $hex, array $rgb) {
        $this->assertEquals($rgb, ColorUtils::hsvToRgb($hsv));
    }

    /** @dataProvider colorExamples */
    public function testRgbToDec(array $hsv, int $dec, string $hex, array $rgb) {
        $this->assertEqualsWithDelta($dec, ColorUtils::rgbToDec($rgb), 1);
    }

    public static function colorExamples(): array {
        return [
            [[0, 100, 100], 16711680, '0xFF0000', [255, 0, 0]],
            [[1, 100, 100], 16712704, '0xFF0400', [255, 4, 0]],
            [[10, 100, 100], 16722688, '0xFF2B00', [255, 43, 0]],
            [[100, 100, 100], 5635840, '0x55FF00', [85, 255, 0]],
            [[100, 100, 80], 4508672, '0x44CC00', [68, 204, 0]],
            [[200, 100, 100], 43775, '0x00AAFF', [0, 170, 255]],
            [[333, 100, 100], 16711795, '0xFF0073', [255, 0, 115]],
            [[359, 100, 100], 16711684, '0xFF0004', [255, 0, 4]],
            [[203, 46, 98], 8900346, '0x87CEFA', [135, 206, 250]],
            [[143, 100, 82], 53585, '0x00D151', [0, 209, 80]],
        ];
    }
}

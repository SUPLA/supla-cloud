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
use SuplaBundle\Utils\NumberUtils;

class NumberUtilsTest extends TestCase {
    /** @dataProvider maximumDecimalPrecisionExamples */
    public function testMaximumDecimalPrecision($number, float $expected, int $decimals = 2) {
        $this->assertEquals($expected, NumberUtils::maximumDecimalPrecision($number, $decimals));
    }

    public static function maximumDecimalPrecisionExamples(): array {
        return [
            ['', 0],
            [1, 1],
            [1.5, 1.5],
            [1.51, 1.51],
            [1.512, 1.51],
            [1.512, 1.512, 3],
            [0.000000009, 0],
            [0.0899999999999, 0.09],
            [56989.82, 56989.82],
        ];
    }
}

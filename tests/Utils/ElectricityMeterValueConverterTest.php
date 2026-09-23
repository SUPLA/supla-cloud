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

namespace App\Tests\Utils;

use App\Utils\ElectricityMeterValueConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ElectricityMeterValueConverterTest extends TestCase {
    #[DataProvider('rawEnergyExamples')]
    public function testConvertsRawEnergyToExactDecimal(int $raw, string $expected): void {
        $this->assertSame($expected, ElectricityMeterValueConverter::rawEnergyToDecimal($raw));
    }

    /** @return list<array{int, string}> */
    public static function rawEnergyExamples(): array {
        return [
            [0, '0'],
            [1, '0.00001'],
            [10, '0.0001'],
            [100000, '1'],
            [100001, '1.00001'],
            [123456789, '1234.56789'],
            [-1, '-0.00001'],
            [-100000, '-1'],
        ];
    }
}

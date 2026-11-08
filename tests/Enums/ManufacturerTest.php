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

namespace App\Tests\Enums;

use App\Enums\Manufacturer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ManufacturerTest extends TestCase {
    // SUPLA_MFR_* in supla-core proto.h at 3fcea0e5f4ea3bde7d44fcb08c804d011eee3d4f.
    public static function manufacturers(): array {
        return [
            ['UNKNOWN', 0], ['ACSOFTWARE', 1], ['TRANSCOM', 2], ['LOGI', 3],
            ['ZAMEL', 4], ['NICE', 5], ['ITEAD', 6], ['DOYLETRATT', 7],
            ['HEATPOL', 8], ['FAKRO', 9], ['PEVEKO', 10], ['WEKTA', 11],
            ['STA_SYSTEM', 12], ['DGF', 13], ['COMELIT', 14], ['POLIER', 15],
            ['ERGO_ENERGIA', 16], ['SOMEF', 17], ['AURATON', 18], ['HPD', 19],
            ['LUKFUD', 20], ['WALA', 21], ['PROVENT', 22], ['SMARTBOB', 23],
        ];
    }

    public function testConstantsMatchCore() {
        $expected = [];
        foreach (self::manufacturers() as [$name, $id]) {
            $expected[$name] = $id;
        }
        $this->assertSame($expected, Manufacturer::toArray());
        $this->assertSame(array_values($expected), array_keys(Manufacturer::captions()));
    }

    #[DataProvider('manufacturers')]
    public function testManufacturerConversions(string $name, int $id) {
        foreach ([new Manufacturer($id), Manufacturer::fromString((string)$id), Manufacturer::fromString(' ' . strtolower($name) . ' ')] as $manufacturer) {
            $this->assertSame($id, $manufacturer->getId());
            $this->assertSame($name, $manufacturer->getName());
            $this->assertNotSame('', trim($manufacturer->getCaption()));
            $this->assertSame(Manufacturer::captions()[$id], $manufacturer->getCaption());
        }
    }

    public function testUnknownNumbersAndNullFallBackToUnknown() {
        foreach ([null, -1, 24, 32767] as $value) {
            $manufacturer = new Manufacturer($value);
            $this->assertSame(0, $manufacturer->getId());
            $this->assertSame('UNKNOWN', $manufacturer->getName());
            $this->assertSame('Unknown', $manufacturer->getCaption());
        }
        $this->assertSame(0, Manufacturer::fromString('24')->getId());
        $this->assertSame(0, Manufacturer::fromString('UNKNOWN')->getId());
    }

    #[DataProvider('malformedNames')]
    public function testMalformedNamesAreRejected(string $name) {
        $this->expectException(\BadMethodCallException::class);
        Manufacturer::fromString($name);
    }

    public static function malformedNames(): array {
        return [[''], ['not-a-manufacturer'], ['23foo']];
    }
}

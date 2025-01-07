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

namespace SuplaBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\ActiveHours;

class ActiveHoursTest extends TestCase {
    /** @dataProvider validActiveHours */
    public function testSetActiveHoursToHours($validActiveHours) {

        $activeHours = ActiveHours::fromArray($validActiveHours);
        $this->assertEquals($validActiveHours, $activeHours->toArray());
    }

    public static function validActiveHours() {
        return [
            [[1 => [22], 6 => [23]]],
            [[1 => [22], 6 => [23, 0, 4]]],
        ];
    }

    public function testDatabaseRepresentation() {
        $activeHours = ActiveHours::fromArray([1 => [22], 6 => [23, 0, 4]]);
        $this->assertEquals(',122,623,60,64,', $activeHours->toString());
    }

    public function testArrayRepresentation() {
        $activeHours = ActiveHours::fromString(',122,623,60,64,');
        $this->assertEquals([1 => [22], 6 => [23, 0, 4]], $activeHours->toArray());
    }

    public function testSetActiveHoursToNull() {
        $activeHours = ActiveHours::fromArray(null);
        $this->assertNull($activeHours->toArray());
        $this->assertNull($activeHours->toString());
    }

    public function testSetActiveHoursToEmptyArray() {
        $activeHours = ActiveHours::fromArray([]);
        $this->assertNull($activeHours->toArray());
        $this->assertNull($activeHours->toString());
    }

    public function testSetActiveHoursToEmptyDef() {
        $activeHours = ActiveHours::fromArray([1 => []]);
        $this->assertNull($activeHours->toArray());
        $this->assertNull($activeHours->toString());
    }

    /** @dataProvider invalidActiveHours */
    public function testSetInvalidActiveHours($invalidActiveHours) {
        $this->expectException(\InvalidArgumentException::class);
        ActiveHours::fromArray($invalidActiveHours);
    }

    public static function invalidActiveHours() {
        return [
            [[1]],
            [[1 => 22]],
            [[1 => [24]]],
            [[8 => [22]]],
        ];
    }
}

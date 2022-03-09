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
use SuplaBundle\Entity\AccessID;
use SuplaBundle\Entity\EntityUtils;

class AccessIDTest extends TestCase {
    /** @dataProvider validActiveHours */
    public function testSetActiveHoursToHours($validActiveHours) {
        $accessId = new AccessID();
        $accessId->setActiveHours($validActiveHours);
        $this->assertEquals($validActiveHours, $accessId->getActiveHours());
    }

    public function validActiveHours() {
        return [
            [[1 => [22], 6 => [23]]],
            [[1 => [22], 6 => [23, 0, 4]]],
        ];
    }

    public function testSetActiveHoursDuplicates() {
        $accessId = new AccessID();
        $accessId->setActiveHours([1 => [11, 11]]);
        $this->assertEquals([1 => [11]], $accessId->getActiveHours());
    }

    public function testDatabaseRepresentation() {
        $accessId = new AccessID();
        $accessId->setActiveHours([1 => [22], 6 => [23, 0, 4]]);
        $databaseRepresentation = EntityUtils::getField($accessId, 'activeHours');
        $this->assertEquals(',122,623,60,64,', $databaseRepresentation);
    }

    public function testSetActiveHoursToNull() {
        $accessId = new AccessID();
        $accessId->setActiveHours(null);
        $this->assertNull($accessId->getActiveHours());
    }

    public function testSetActiveHoursToEmptyArray() {
        $accessId = new AccessID();
        $accessId->setActiveHours([]);
        $this->assertNull($accessId->getActiveHours());
    }

    public function testSetActiveHoursToEmptyDef() {
        $accessId = new AccessID();
        $accessId->setActiveHours([1 => []]);
        $this->assertNull($accessId->getActiveHours());
    }

    /** @dataProvider invalidActiveHours */
    public function testSetInvalidActiveHours($invalidActiveHours) {
        $this->expectException(\InvalidArgumentException::class);
        $accessId = new AccessID();
        $accessId->setActiveHours($invalidActiveHours);
    }

    public function invalidActiveHours() {
        return [
            [[1]],
            [[1 => 22]],
            [[1 => [24]]],
            [[8 => [22]]],
        ];
    }
}

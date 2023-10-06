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
use SuplaBundle\Exception\ApiExceptionWithDetails;
use SuplaBundle\Utils\ArrayUtils;

class ArrayUtilsTest extends TestCase {
    public function testFlattenOnce() {
        $this->assertEquals(['A', 'B', 'C'], ArrayUtils::flattenOnce([['A'], ['B', 'C']]));
        $this->assertEquals(['A', 'B', ['C']], ArrayUtils::flattenOnce([['A'], [], ['B', ['C']]]));
    }

    public function testLeaveKeys() {
        $this->assertEquals(['a' => 1], ArrayUtils::leaveKeys(['a' => 1], ['a']));
        $this->assertEquals(['a' => 1], ArrayUtils::leaveKeys(['a' => 1, 'b' => 1], ['a']));
        $this->assertEquals([], ArrayUtils::leaveKeys(['a' => 1, 'b' => 1], ['c']));
        $this->assertEquals(['a' => 1, 'b' => 1], ArrayUtils::leaveKeys(['a' => 1, 'b' => 1], ['a', 'b']));
    }

    public function testMergeConfigs() {
        $this->assertEquals(['a' => 1], ArrayUtils::mergeConfigs(['a' => 0], ['a' => 1], ['a' => 0]));
        $this->assertEquals([], ArrayUtils::mergeConfigs(['a' => 1], ['a' => 1], ['a' => 0]));
        $this->assertEquals([], ArrayUtils::mergeConfigs(['a' => 0], ['a' => 1], ['a' => 1]));
        $this->assertEquals(['a' => 1], ArrayUtils::mergeConfigs(['a' => 0, 'b' => 1], ['a' => 1, 'b' => 1], ['a' => 0, 'b' => 1]));
        $this->assertEquals(['a' => 1], ArrayUtils::mergeConfigs(['a' => 0, 'b' => 1], ['a' => 1], ['a' => 0, 'b' => 2]));
        $this->expectException(ApiExceptionWithDetails::class);
        ArrayUtils::mergeConfigs(['a' => 0], ['a' => 1], ['a' => 2]);
    }
}

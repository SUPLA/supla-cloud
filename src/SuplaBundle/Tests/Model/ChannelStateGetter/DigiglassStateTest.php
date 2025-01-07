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

namespace SuplaBundle\Tests\Model\ChannelStateGetter;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Model\ChannelStateGetter\DigiglassState;

class DigiglassStateTest extends TestCase {
    public function testCannotSetNonExistingSection() {
        $this->expectException(InvalidArgumentException::class);
        DigiglassState::sections(2)->setTransparent(2);
    }

    /** @dataProvider maskTestCases */
    public function testMaskValues(
        int $expectedMask,
        int $expectedActiveBits,
        array $expectedTransparentSections,
        array $expectedOpaqueSections,
        DigiglassState $state
    ) {
        $this->assertEquals($expectedMask, $state->getMask());
        $this->assertEquals($expectedActiveBits, $state->getActiveBits());
        $this->assertEquals($expectedTransparentSections, $state->getTransparentSections());
        $this->assertEquals($expectedOpaqueSections, $state->getOpaqueSections());
    }

    public static function maskTestCases(): array {
        return [
            [0, 0, [], [], DigiglassState::sections(2)],
            [1, 1, [0], [], DigiglassState::sections(2)->setTransparent(0)],
            [2, 2, [1], [], DigiglassState::sections(2)->setTransparent(1)],
            [2, 3, [1], [0], DigiglassState::sections(2)->setOpaque(0)->setTransparent(1)],
            [2, 3, [1], [0], DigiglassState::sections(2)->setTransparent(1)->setOpaque(0)],
            [3, 3, [0, 1], [], DigiglassState::sections(2)->setTransparent(0)->setTransparent(1)],
            [3, 3, [0, 1], [], DigiglassState::sections(2)->setTransparent([0, 1])],
            [0b0101, 0b1101, [0, 2], [3], DigiglassState::sections(4)->setTransparent(0)->setTransparent(2)->setOpaque(3)],
            [0b10011, 0b11111, [0, 1, 4], [2, 3], DigiglassState::sections(5)->setMask(0b10011)],
        ];
    }
}

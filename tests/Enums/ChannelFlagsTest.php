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

use App\Enums\ChannelFlags;
use PHPUnit\Framework\TestCase;

class ChannelFlagsTest extends TestCase {
    public function testEveryBitIsExclusive() {
        $bitsSum = 0;
        foreach (ChannelFlags::values() as $bit) {
            $newBitsSum = $bitsSum | $bit->getValue();
            $this->assertNotEquals($newBitsSum, $bitsSum, 'Non exclusive detected on ' . $bit->getKey());
            $bitsSum = $newBitsSum;
        }
    }

    public function testProtocol29FlagsAndCombinations(): void {
        $expected = [
            'BUTTON_MODE_SUPPORTED' => 0x100000000,
            'RELAY_MODE_START_SUPPORTED' => 0x200000000,
            'RELAY_MODE_FORCED_SUPPORTED' => 0x400000000,
            'RELAY_MODE_AUTOMATIC_SUPPORTED' => 0x800000000,
            'RELAY_MODE_NOT_SET_SUPPORTED' => 0x1000000000,
            'BUTTON_TEMPORARY_UNLOCK_SUPPORTED' => 0x2000000000,
        ];
        foreach ($expected as $name => $value) {
            $flag = new ChannelFlags($value);
            $this->assertSame($name, $flag->getKey());
            $this->assertTrue($flag->isSupported($value | ChannelFlags::HAS_EXTENDED_CHANNEL_STATE));
            $this->assertFalse($flag->isSupported(ChannelFlags::HAS_EXTENDED_CHANNEL_STATE));
        }
        $flags = 0x2118010000;
        $this->assertNotSame($flags, $flags & 0xFFFFFFFF);
        $this->assertTrue(ChannelFlags::BUTTON_MODE_SUPPORTED()->isOn($flags));
        $this->assertTrue(ChannelFlags::BUTTON_TEMPORARY_UNLOCK_SUPPORTED()->isOn($flags));
        $this->assertTrue(ChannelFlags::HAS_EXTENDED_CHANNEL_STATE()->isOn($flags));
        $this->assertFalse(ChannelFlags::RELAY_MODE_START_SUPPORTED()->isOn($flags));
    }
}

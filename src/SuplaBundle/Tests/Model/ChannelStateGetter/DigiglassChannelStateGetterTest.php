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

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelStateGetter\DigiglassChannelStateGetter;
use SuplaBundle\Supla\SuplaServer;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class DigiglassChannelStateGetterTest extends TestCase {
    use UnitTestHelper;

    /** @dataProvider stateExamples */
    public function testGettingState($suplaServerValue, array $expectedState) {
        $channel = $this->createEntityMock(IODeviceChannel::class, 111);
        $channel->method('getType')->willReturn(ChannelType::DIGIGLASS());
        $suplaServer = $this->createMock(SuplaServer::class);
        $suplaServer->method('getValue')->willReturn($suplaServerValue);
        $stateGetter = new DigiglassChannelStateGetter();
        $stateGetter->setSuplaServer($suplaServer);
        $state = $stateGetter->getState($channel);
        $this->assertEquals($expectedState, $state);
    }

    public static function stateExamples() {
        return [
            [0, ['transparent' => [], 'opaque' => [0, 1, 2, 3, 4, 5, 6], 'mask' => 0]],
            [1, ['transparent' => [0], 'opaque' => [1, 2, 3, 4, 5, 6], 'mask' => 1]],
            [2, ['transparent' => [1], 'opaque' => [0, 2, 3, 4, 5, 6], 'mask' => 2]],
            [3, ['transparent' => [0, 1], 'opaque' => [2, 3, 4, 5, 6], 'mask' => 3]],
            [15, ['transparent' => [0, 1, 2, 3], 'opaque' => [4, 5, 6], 'mask' => 15]],
            [16, ['transparent' => [4], 'opaque' => [0, 1, 2, 3, 5, 6], 'mask' => 16]],
            [16.0, ['transparent' => [4], 'opaque' => [0, 1, 2, 3, 5, 6], 'mask' => 16]],
            [91, ['transparent' => [0, 1, 3, 4, 6], 'opaque' => [2, 5], 'mask' => 91]],
        ];
    }
}

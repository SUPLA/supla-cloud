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

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Tests\Integration\Traits\UnitTestHelper;

class IODeviceChannelGroupTest extends TestCase {
    use UnitTestHelper;

    /** @var User */
    private $user;
    /** @var Location */
    private $location;

    /** @before */
    public function createMocks() {
        $this->location = $this->createMock(Location::class);
        $this->user = $this->createEntityMock(User::class);
    }

    public function testDeterminingGroupFunction() {
        $channel1 = $this->createMock(IODeviceChannel::class);
        $channel2 = $this->createMock(IODeviceChannel::class);
        $channel1->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEGATE());
        $channel2->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEGATE());
        $group = new IODeviceChannelGroup($this->user, $this->location, [$channel1, $channel2]);
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGATE(), $group->getFunction());
    }

    public function testSettingChannels() {
        $channel1 = $this->createMock(IODeviceChannel::class);
        $channel2 = $this->createMock(IODeviceChannel::class);
        $channel1->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEGATE());
        $channel2->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEGATE());
        $group = new IODeviceChannelGroup($this->user, $this->location, [$channel1, $channel2]);
        $this->assertEquals([$channel1, $channel2], $group->getChannels()->toArray());
    }

    public function testCanInstantiateWithNoArgs() {
        new IODeviceChannelGroup();
        $this->assertTrue(true);
    }

    public function testForbidsMixingChannelFunctions() {
        $this->expectException(InvalidArgumentException::class);
        $channel1 = $this->createMock(IODeviceChannel::class);
        $channel2 = $this->createMock(IODeviceChannel::class);
        $channel1->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEGATE());
        $channel2->method('getFunction')->willReturn(ChannelFunction::CONTROLLINGTHEDOORLOCK());
        new IODeviceChannelGroup($this->user, $this->location, [$channel1, $channel2]);
    }

    public function testForbidsSettingEmptyChannels() {
        $this->expectException(InvalidArgumentException::class);
        (new IODeviceChannelGroup())->setChannels([]);
    }

    public function testForbidsSettingNotChannelsAsChannels() {
        $this->expectException(InvalidArgumentException::class);
        new IODeviceChannelGroup($this->user, $this->location, [$this->createMock(Location::class)]);
    }

    public function testBuildingServerActionCommand() {
        $cg = new IODeviceChannelGroup($this->user);
        EntityUtils::setField($cg, 'id', 22);
        $this->assertEquals('ACTION-CG-UNICORNIFY:1,22', $cg->buildServerActionCommand('ACTION-UNICORNIFY', []));
        $this->assertEquals('SET-CG-VALUE:1,22', $cg->buildServerActionCommand('SET-VALUE', []));
        $this->assertEquals('UNICORN:1,22', $cg->buildServerActionCommand('UNICORN', []));
    }
}

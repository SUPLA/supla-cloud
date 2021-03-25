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

namespace SuplaBundle\Tests\Model\ChannelParamsUpdater;

use PHPUnit_Framework_TestCase;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\ChannelParamsUpdater\RelatedChannelsConnector;
use SuplaBundle\Repository\IODeviceChannelRepository;

class RelatedChannelsConnectorTest extends PHPUnit_Framework_TestCase {
    /** @var RelatedChannelsConnector */
    private $connector;

    /** @before */
    public function init() {
        $channelRepository = $this->createMock(IODeviceChannelRepository::class);
        $this->connector = new RelatedChannelsConnector($channelRepository);
    }

    public function testBuildingConnections() {
        $relations = RelatedChannelsConnector::getPossibleRelations();
        $this->assertArrayHasKey(ChannelFunction::OPENINGSENSOR_DOOR, $relations);
        $this->assertArrayHasKey(ChannelFunction::CONTROLLINGTHEDOORLOCK, $relations);
        $this->assertEquals([1, 2], $relations[ChannelFunction::OPENINGSENSOR_DOOR][ChannelFunction::CONTROLLINGTHEDOORLOCK][0]);
        $this->assertEquals([2, 1], $relations[ChannelFunction::CONTROLLINGTHEDOORLOCK][ChannelFunction::OPENINGSENSOR_DOOR][0]);
    }

    public function testSupports() {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getFunction')->willReturn(ChannelFunction::OPENINGSENSOR_DOOR());
        $this->assertTrue($this->connector->supports($channel));
    }
}

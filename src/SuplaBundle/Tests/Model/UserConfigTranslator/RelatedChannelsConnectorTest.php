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

namespace SuplaBundle\Tests\Model\UserConfigTranslator;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction as CF;
use SuplaBundle\Model\UserConfigTranslator\RelatedChannelsConnector;
use SuplaBundle\Repository\IODeviceChannelRepository;

class RelatedChannelsConnectorTest extends TestCase {
    /** @var RelatedChannelsConnector */
    private $connector;

    /** @before */
    public function init() {
        $channelRepository = $this->createMock(IODeviceChannelRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->connector = new RelatedChannelsConnector($channelRepository, $entityManager);
    }

    public function testBuildingConnections() {
        $relations = RelatedChannelsConnector::getPossibleRelations();
        $this->assertArrayHasKey(CF::OPENINGSENSOR_DOOR, $relations);
        $this->assertArrayHasKey(CF::CONTROLLINGTHEDOORLOCK, $relations);
        $this->assertEquals([1, 2], $relations[CF::OPENINGSENSOR_DOOR][CF::CONTROLLINGTHEDOORLOCK]['openingSensorChannelId']);
        $this->assertEquals([2, 1], $relations[CF::CONTROLLINGTHEDOORLOCK][CF::OPENINGSENSOR_DOOR]['openingSensorChannelId']);
    }

    public function testSupports() {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getFunction')->willReturn(CF::OPENINGSENSOR_DOOR());
        $this->assertTrue($this->connector->supports($channel));
    }
}

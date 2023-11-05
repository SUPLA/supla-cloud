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

namespace SuplaBundle\Migrations\Migration;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Migrations\Factory\ChannelDependenciesAware;
use SuplaBundle\Migrations\Factory\EntityManagerAware;
use SuplaBundle\Migrations\NoWayBackMigration;
use SuplaBundle\Model\Dependencies\ChannelDependencies;

/**
 * Ensure the related channels are in the same location.
 */
class Version20231103121340 extends NoWayBackMigration implements EntityManagerAware, ChannelDependenciesAware {
    /** @var ChannelDependencies */
    private $channelDependencies;
    /** @var EntityManagerInterface */
    private $em;

    /** @required */
    public function setChannelDependencies(ChannelDependencies $channelDependencies): void {
        $this->channelDependencies = $channelDependencies;
    }

    public function setEntityManager(EntityManagerInterface $em): void {
        $this->em = $em;
    }

    public function migrate() {
        $channels = $this->fetchAll('SELECT id FROM supla_dev_channel');
        $newLocationIds = [];
        foreach ($channels as $channelData) {
            $channel = $this->em->find(IODeviceChannel::class, $channelData['id']);
            $dependencies = $this->channelDependencies->getDependencies($channel);
            if ($dependencies['channels']) {
                $expectedLocationId = $newLocationIds[$channel->getId()] ?? $channel->getLocation()->getId();
                foreach ($dependencies['channels'] as $channel) {
                    if ($channel->getLocation()->getId() !== $expectedLocationId) {
                        $newLocationIds[$channel->getId()] = $expectedLocationId;
                    }
                }
            }
        }
        foreach ($newLocationIds as $channelId => $newLocationId) {
            $this->addSql('UPDATE supla_dev_channel SET location_id=:locationId WHERE id=:id', [
                'id' => $channelId,
                'locationId' => $newLocationId,
            ]);
        }
    }
}

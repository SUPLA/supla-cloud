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

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannelGroup;

class ChannelGroupsFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function load(ObjectManager $manager) {
        $this->entityManager = $manager;
        $this->createLightGroup();
        $manager->flush();
    }

    private function createLightGroup() {
        /** @var IODevice $sonoff */
        $sonoff = $this->getReference(DevicesFixture::DEVICE_SONOFF);
        /** @var IODevice $full */
        $full = $this->getReference(DevicesFixture::DEVICE_FULL);
        $lightChannelFromSonoff = $sonoff->getChannels()[0];
        $lightChannelFromFull = $full->getChannels()[0];
        $group = new IODeviceChannelGroup($sonoff->getUser(), $sonoff->getLocation(), [$lightChannelFromSonoff, $lightChannelFromFull]);
        $group->setCaption('Światła na parterze');
        $this->entityManager->persist($group);
        $this->entityManager->flush();
    }
}

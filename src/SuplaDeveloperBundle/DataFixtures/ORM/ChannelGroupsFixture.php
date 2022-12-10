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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;

class ChannelGroupsFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var \Faker\Generator */
    private $faker;

    public function load(ObjectManager $manager) {
        $this->faker = Factory::create('pl_PL');
        $this->entityManager = $manager;
        $this->createLightGroup();
        $this->createRandomGroups();
        $manager->flush();
    }

    private function createLightGroup() {
        /** @var \SuplaBundle\Entity\Main\IODevice $sonoff */
        $sonoff = $this->getReference(DevicesFixture::DEVICE_SONOFF);
        /** @var \SuplaBundle\Entity\Main\IODevice $full */
        $full = $this->getReference(DevicesFixture::DEVICE_FULL);
        $lightChannelFromSonoff = $sonoff->getChannels()[0];
        $lightChannelFromFull = $full->getChannels()[0];
        $group = new IODeviceChannelGroup($sonoff->getUser(), $sonoff->getLocation(), [$lightChannelFromSonoff, $lightChannelFromFull]);
        $group->setCaption('Światła na parterze');
        $this->entityManager->persist($group);
    }

    private function createRandomGroups() {
        $randomDevices = [];
        for ($i = 0; $i < DevicesFixture::NUMBER_OF_RANDOM_DEVICES; $i++) {
            $randomDevices[] = $this->getReference(DevicesFixture::RANDOM_DEVICE_PREFIX . $i);
        }
        $locations = [
            $this->getReference(LocationsFixture::LOCATION_GARAGE),
            $this->getReference(LocationsFixture::LOCATION_OUTSIDE),
            $this->getReference(LocationsFixture::LOCATION_BEDROOM),
        ];
        for ($i = 0; $i < 10; $i++) {
            $numberOfChannels = rand(1, DevicesFixture::NUMBER_OF_RANDOM_DEVICES);
            shuffle($randomDevices);
            $channels = [];
            $function = rand(0, 3);
            for ($j = 0; $j < $numberOfChannels; $j++) {
                $channels[] = $randomDevices[$j]->getChannels()[$function];
            }
            $location = $locations[rand(0, count($locations) - 1)];
            $group = new IODeviceChannelGroup($this->getReference(DevicesFixture::DEVICE_SONOFF)->getUser(), $location, $channels);
            $group->setCaption($this->faker->sentence(3));
            $this->entityManager->persist($group);
        }
    }
}

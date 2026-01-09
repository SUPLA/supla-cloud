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

namespace App\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Location;

class ChannelGroupsFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var \Faker\Generator */
    private $faker;

    public function load(ObjectManager $manager): void {
        $this->faker = Factory::create('pl_PL');
        $this->entityManager = $manager;
        $this->createLightGroup();
        $this->createRandomGroups();
        $manager->flush();
    }

    private function createLightGroup() {
        /** @var \SuplaBundle\Entity\Main\IODevice $sonoff */
        $sonoff = $this->getReference(DevicesFixture::DEVICE_SONOFF, IODevice::class);
        /** @var \SuplaBundle\Entity\Main\IODevice $full */
        $full = $this->getReference(DevicesFixture::DEVICE_FULL, IODevice::class);
        $lightChannelFromSonoff = $sonoff->getChannels()[0];
        $lightChannelFromFull = $full->getChannels()[0];
        $group = new IODeviceChannelGroup($sonoff->getUser(), $sonoff->getLocation(), [$lightChannelFromSonoff, $lightChannelFromFull]);
        $group->setCaption('Światła na parterze');
        $this->entityManager->persist($group);
    }

    private function createRandomGroups() {
        $randomDevices = [];
        for ($i = 0; $i < DevicesFixture::NUMBER_OF_RANDOM_DEVICES; $i++) {
            $randomDevices[] = $this->getReference(DevicesFixture::RANDOM_DEVICE_PREFIX . $i, IODevice::class);
        }
        $locations = [
            $this->getReference(LocationsFixture::LOCATION_GARAGE, Location::class),
            $this->getReference(LocationsFixture::LOCATION_OUTSIDE, Location::class),
            $this->getReference(LocationsFixture::LOCATION_BEDROOM, Location::class),
        ];
        for ($i = 0; $i < 10; $i++) {
            $numberOfChannels = random_int(1, DevicesFixture::NUMBER_OF_RANDOM_DEVICES);
            shuffle($randomDevices);
            $channels = [];
            $function = random_int(0, 3);
            for ($j = 0; $j < $numberOfChannels; $j++) {
                $channels[] = $randomDevices[$j]->getChannels()[$function];
            }
            $location = $locations[random_int(0, count($locations) - 1)];
            $device = $this->getReference(DevicesFixture::DEVICE_SONOFF, IODevice::class);
            $group = new IODeviceChannelGroup($device->getUser(), $location, $channels);
            $group->setCaption($this->faker->sentence(3));
            $this->entityManager->persist($group);
        }
    }
}

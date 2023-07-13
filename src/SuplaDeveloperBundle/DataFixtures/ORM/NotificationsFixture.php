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

use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\PushNotification;

class NotificationsFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;

    /** @var Generator */
    private $faker;

    public function load(ObjectManager $manager) {
        $this->faker = Factory::create('pl_PL');
        $this->createChannelNotification($manager, $this->getReference(DevicesFixture::DEVICE_SONOFF)->getChannels()[0]);
        $this->createChannelNotification($manager, $this->getReference(DevicesFixture::DEVICE_SONOFF)->getChannels()[1]);
        $this->createChannelNotification($manager, $this->getReference(DevicesFixture::DEVICE_FULL)->getChannels()[0]);
        $this->createChannelNotification($manager, $this->getReference(DevicesFixture::DEVICE_FULL)->getChannels()[1]);
        $this->createChannelNotification($manager, $this->getReference(DevicesFixture::DEVICE_FULL)->getChannels()[2]);
        $this->createChannelNotification($manager, $this->getReference(DevicesFixture::DEVICE_FULL)->getChannels()[3]);
        $this->createDeviceNotification($manager, $this->getReference(DevicesFixture::DEVICE_SONOFF));
        $this->createDeviceNotification($manager, $this->getReference(DevicesFixture::DEVICE_FULL));
        $this->createDeviceNotification($manager, $this->getReference(DevicesFixture::DEVICE_RGB));
        $manager->flush();
    }

    private function createChannelNotification(ObjectManager $manager, IODeviceChannel $channel) {
        $notification = new PushNotification($channel->getUser());
        EntityUtils::setField($notification, 'managedByDevice', true);
        EntityUtils::setField($notification, 'device', $channel->getIoDevice());
        $notification->setChannel($channel);
        if ($this->faker->boolean) {
            $notification->setTitle($this->faker->sentence);
        }
        if ($this->faker->boolean) {
            $notification->setBody($this->faker->sentence);
        }
        $manager->persist($notification);
    }

    private function createDeviceNotification(ObjectManager $manager, IODevice $device) {
        $notification = new PushNotification($device->getUser());
        EntityUtils::setField($notification, 'managedByDevice', true);
        EntityUtils::setField($notification, 'device', $device);
        if ($this->faker->boolean) {
            $notification->setTitle($this->faker->sentence);
        }
        if ($this->faker->boolean) {
            $notification->setBody($this->faker->sentence);
        }
        $manager->persist($notification);
    }
}

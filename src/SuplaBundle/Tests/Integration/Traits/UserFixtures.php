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

namespace SuplaBundle\Tests\Integration\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;

/**
 * @property ContainerInterface $container
 */
trait UserFixtures {
    protected function createConfirmedUser(string $username = 'supler@supla.org', string $password = 'supla123'): User {
        $userManager = $this->container->get('user_manager');
        $user = new User();
        $user->setEmail($username);
        $userManager->create($user);
        $userManager->setPassword($password, $user, true);
        $userManager->confirm($user->getToken());
        return $user;
    }

    protected function createLocation(User $user): Location {
        $location = $this->container->get('location_manager')->createLocation($user);
        $this->getEntityManager()->persist($location);
        $this->getEntityManager()->flush();
        return $location;
    }

    protected function createDeviceSonoff(Location $location): IODevice {
        return $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ]);
    }

    protected function createDeviceFull(Location $location): IODevice {
        return $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ]);
    }

    protected function createDevice(Location $location, array $channelTypes): IODevice {
        $fieldSetter = function ($field, $type) {
            $this->{$field} = $type;
        };

        $device = new IODevice();
        $fieldSetter->call($device, 'guid', rand(0, 9999999));
        $fieldSetter->call($device, 'regDate', new \DateTime());
        $fieldSetter->call($device, 'lastConnected', new \DateTime());
        $fieldSetter->call($device, 'regIpv4', rand(0, 9999999));
        $fieldSetter->call($device, 'softwareVersion', '2.' . rand(0, 50));
        $fieldSetter->call($device, 'protocolVersion', '2.' . rand(0, 50));
        $fieldSetter->call($device, 'location', $location);
        $fieldSetter->call($device, 'user', $location->getUser());
        $this->getEntityManager()->persist($device);

        foreach ($channelTypes as $channelNumber => $channelData) {
            $channel = new IODeviceChannel();
            $fieldSetter->call($channel, 'iodevice', $device);
            $fieldSetter->call($channel, 'user', $location->getUser());
            $fieldSetter->call($channel, 'type', $channelData[0]);
            $fieldSetter->call($channel, 'function', $channelData[1]);
            $fieldSetter->call($channel, 'channelNumber', $channelNumber++);
            $this->getEntityManager()->persist($channel);
            $this->getEntityManager()->flush();
        }
        $this->getEntityManager()->refresh($device);
        return $device;
    }

    protected function getEntityManager(): EntityManagerInterface {
        return $this->container->get('doctrine')->getManager();
    }
}

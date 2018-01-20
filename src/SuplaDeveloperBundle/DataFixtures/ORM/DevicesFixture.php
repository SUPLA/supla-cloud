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
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Location;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\RelayFunctionBits;
use SuplaBundle\Tests\AnyFieldSetter;

class DevicesFixture extends SuplaFixture {
    const ORDER = LocationsFixture::ORDER + 1;

    const DEVICE_SONOFF = 'deviceSonoff';
    const DEVICE_FULL = 'deviceFull';
    const DEVICE_RGB = 'deviceRgb';

    /** @var EntityManagerInterface */
    private $entityManager;

    public function load(ObjectManager $manager) {
        $this->entityManager = $manager;
        $this->createDeviceSonoff($this->getReference(LocationsFixture::LOCATION_OUTSIDE));
        $this->createDeviceFull($this->getReference(LocationsFixture::LOCATION_GARAGE));
        $this->createDeviceRgb($this->getReference(LocationsFixture::LOCATION_BEDROOM));
        $manager->flush();
    }

    protected function createDeviceSonoff(Location $location): IODevice {
        return $this->createDevice('SONOFF-DS', $location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, RelayFunctionBits::LIGHTSWITCH | RelayFunctionBits::POWERSWITCH],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ], self::DEVICE_SONOFF);
    }

    protected function createDeviceFull(Location $location): IODevice {
        $controllingBits =
            RelayFunctionBits::CONTROLLINGTHEDOORLOCK |
            RelayFunctionBits::CONTROLLINGTHEGARAGEDOOR |
            RelayFunctionBits::CONTROLLINGTHEGATE |
            RelayFunctionBits::CONTROLLINGTHEGATEWAYLOCK;
        return $this->createDevice('UNI-MODULE', $location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, RelayFunctionBits::LIGHTSWITCH | RelayFunctionBits::POWERSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK, $controllingBits],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE, $controllingBits],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER, RelayFunctionBits::CONTROLLINGTHEROLLERSHUTTER],
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATEWAY],
            [ChannelType::SENSORNC, ChannelFunction::OPENINGSENSOR_DOOR],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ], self::DEVICE_FULL);
    }

    protected function createDeviceRgb(Location $location): IODevice {
        return $this->createDevice('RGB-801', $location, [
            [ChannelType::RGBLEDCONTROLLER, ChannelFunction::DIMMERANDRGBLIGHTING],
            [ChannelType::RGBLEDCONTROLLER, ChannelFunction::RGBLIGHTING],
        ], self::DEVICE_RGB);
    }

    private function createDevice(string $name, Location $location, array $channelTypes, string $registerAs): IODevice {
        $device = new IODevice();
        AnyFieldSetter::set($device, [
            'name' => $name,
            'guid' => rand(0, 9999999),
            'regDate' => new \DateTime(),
            'lastConnected' => new \DateTime(),
            'regIpv4' => rand(0, 9999999),
            'softwareVersion' => '2.' . rand(0, 50),
            'protocolVersion' => '2.' . rand(0, 50),
            'location' => $location,
            'user' => $location->getUser(),
        ]);
        $this->entityManager->persist($device);
        foreach ($channelTypes as $channelNumber => $channelData) {
            $channel = new IODeviceChannel();
            AnyFieldSetter::set($channel, [
                'iodevice' => $device,
                'user' => $location->getUser(),
                'type' => $channelData[0],
                'function' => $channelData[1],
                'funcList' => $channelData[2] ?? null,
                'channelNumber' => $channelNumber++,
            ]);
            $this->entityManager->persist($channel);
            $this->entityManager->flush();
        }
        $this->entityManager->refresh($device);
        $this->setReference($registerAs, $device);
        return $device;
    }
}

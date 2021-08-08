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

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Location;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsActionTrigger;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Enums\ChannelFunctionBitsFlist;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\AnyFieldSetter;

class DevicesFixture extends SuplaFixture {
    const ORDER = LocationsFixture::ORDER + 1;
    const NUMBER_OF_RANDOM_DEVICES = 15;

    const DEVICE_SONOFF = 'deviceSonoff';
    const DEVICE_FULL = 'deviceFull';
    const DEVICE_RGB = 'deviceRgb';
    const DEVICE_SUPLER = 'deviceSupler';
    const DEVICE_EVERY_FUNCTION = 'ALL-IN-ONE MEGA DEVICE';
    const RANDOM_DEVICE_PREFIX = 'randomDevice';

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var Generator */
    private $faker;

    public function load(ObjectManager $manager) {
        $this->entityManager = $manager;
        $this->faker = Factory::create('pl_PL');
        $this->createDeviceSonoff($this->getReference(LocationsFixture::LOCATION_OUTSIDE));
        $this->createDeviceFull($this->getReference(LocationsFixture::LOCATION_GARAGE));
        $this->createDeviceRgb($this->getReference(LocationsFixture::LOCATION_BEDROOM));
        $this->createEveryFunctionDevice($this->getReference(LocationsFixture::LOCATION_OUTSIDE), self::DEVICE_EVERY_FUNCTION);
        $device = $this->createEveryFunctionDevice($this->getReference(LocationsFixture::LOCATION_OUTSIDE), 'SECOND MEGA DEVICE');
        foreach ($this->faker->randomElements($device->getChannels(), 3) as $noFunctionChannel) {
            $noFunctionChannel->setFunction(ChannelFunction::NONE());
            $this->entityManager->persist($noFunctionChannel);
        }
        $this->createDeviceManyGates($this->getReference(LocationsFixture::LOCATION_OUTSIDE));
        $nonDeviceLocations = [null, $this->getReference(LocationsFixture::LOCATION_OUTSIDE), $this->getReference(LocationsFixture::LOCATION_BEDROOM)];
        for ($i = 0; $i < self::NUMBER_OF_RANDOM_DEVICES; $i++) {
            $name = strtoupper(implode('-', $this->faker->words($this->faker->numberBetween(1, 3))));
            $device = $this->createDeviceFull($this->getReference(LocationsFixture::LOCATION_GARAGE), $name);
            foreach ($device->getChannels() as $channel) {
                $channel->setLocation($nonDeviceLocations[rand(0, count($nonDeviceLocations) - 1)]);
                $manager->persist($channel);
            }
            $this->setReference(self::RANDOM_DEVICE_PREFIX . $i, $device);
        }
        $suplerDevice = $this->createEveryFunctionDevice($this->getReference(LocationsFixture::LOCATION_SUPLER), 'SUPLER MEGA DEVICE', '');
        $this->setReference(self::DEVICE_SUPLER, $suplerDevice);
        $manager->flush();
    }

    protected function createDeviceSonoff(Location $location): IODevice {
        return $this->createDevice('SONOFF-DS', $location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, ['funcList' => ChannelFunctionBitsFlist::LIGHTSWITCH | ChannelFunctionBitsFlist::POWERSWITCH]],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ], self::DEVICE_SONOFF);
    }

    protected function createDeviceFull(Location $location, $name = 'UNI-MODULE'): IODevice {
        return $this->createDevice($name, $location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH, ['funcList' => ChannelFunctionBitsFlist::LIGHTSWITCH | ChannelFunctionBitsFlist::POWERSWITCH]],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK, ['funcList' => ChannelFunctionBitsFlist::getAllFeaturesFlag()]],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE, ['funcList' => ChannelFunctionBitsFlist::getAllFeaturesFlag()]],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER, ['funcList' => ChannelFunctionBitsFlist::CONTROLLINGTHEROLLERSHUTTER, 'flags' => ChannelFunctionBitsFlags::RECALIBRATE_ACTION_AVAILABLE]],
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATEWAY],
            [ChannelType::SENSORNC, ChannelFunction::OPENINGSENSOR_DOOR],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::ACTION_TRIGGER, ChannelFunction::ACTION_TRIGGER, ['flags' => ChannelFunctionBitsActionTrigger::getAllFeaturesFlag()]],
            [ChannelType::BRIDGE, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER, ['funcList' => ChannelFunctionBitsFlist::getAllFeaturesFlag()]],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_WATERMETER, ['flags' => ChannelFunctionBitsFlags::RESET_COUNTERS_ACTION_AVAILABLE]],
        ], self::DEVICE_FULL);
    }

    protected function createEveryFunctionDevice(
        Location $location,
        $name = 'ALL-IN-ONE MEGA DEVICE'
    ): IODevice {
        $functionableTypes = array_filter(ChannelType::values(), function (ChannelType $type) {
            return count(ChannelType::functions()[$type->getValue()] ?? []);
        });
        $channels = array_values(array_map(function (ChannelType $type) {
            return array_map(function (ChannelFunction $function) use ($type) {
                return [$type->getValue(), $function->getValue()];
            }, ChannelType::functions()[$type->getValue()]);
        }, $functionableTypes));
        $channels = call_user_func_array('array_merge', $channels);
        return $this->createDevice($name, $location, $channels, $name);
    }

    protected function createDeviceRgb(Location $location): IODevice {
        return $this->createDevice('RGB-801', $location, [
            [ChannelType::RGBLEDCONTROLLER, ChannelFunction::DIMMERANDRGBLIGHTING],
            [ChannelType::RGBLEDCONTROLLER, ChannelFunction::RGBLIGHTING],
        ], self::DEVICE_RGB);
    }

    private function createDeviceManyGates(Location $location) {
        $channels = [];
        for ($i = 0; $i < 10; $i++) {
            $channels[] = [
                ChannelType::RELAY,
                ChannelFunction::CONTROLLINGTHEGATE,
                ['funcList' => ChannelFunctionBitsFlist::getAllFeaturesFlag()],
            ];
        }
        return $this->createDevice('OH-MY-GATES. This device also has ridiculously long name!', $location, $channels, 'gatesDevice');
    }

    private function createDevice(string $name, Location $location, array $channelTypes, string $registerAs): IODevice {
        $device = new IODevice();
        AnyFieldSetter::set($device, [
            'name' => $name,
            'guid' => rand(0, 9999999),
            'regDate' => new DateTime(),
            'lastConnected' => new DateTime(),
            'regIpv4' => implode('.', [rand(0, 255), rand(0, 255), rand(0, 255), rand(0, 255)]),
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
                'channelNumber' => $channelNumber,
                'flags' => ChannelFunctionBitsActionTrigger::PRESS | ChannelFunctionBitsActionTrigger::RELEASE,
            ]);
            if (isset($channelData[2])) {
                AnyFieldSetter::set($channel, $channelData[2]);
            }
            if ($this->faker->boolean) {
                $channel->setCaption($this->faker->sentence(3));
            }
            $this->entityManager->persist($channel);
            $this->entityManager->flush();
        }
        $this->entityManager->refresh($device);
        if ($registerAs) {
            $this->setReference($registerAs, $device);
        }
        return $device;
    }
}

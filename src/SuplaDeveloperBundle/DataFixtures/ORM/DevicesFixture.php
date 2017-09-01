<?php

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Location;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaConst;
use SuplaBundle\Tests\AnyFieldSetter;

class DevicesFixture extends SuplaFixture {
    const ORDER = LocationsFixture::ORDER + 1;

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
            [SuplaConst::TYPE_RELAY, ChannelFunction::LIGHTSWITCH],
            [SuplaConst::TYPE_THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ]);
    }

    protected function createDeviceFull(Location $location): IODevice {
        return $this->createDevice('UNI-MODULE', $location, [
            [SuplaConst::TYPE_RELAY, ChannelFunction::LIGHTSWITCH],
            [SuplaConst::TYPE_RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [SuplaConst::TYPE_RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [SuplaConst::TYPE_RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER],
            [SuplaConst::TYPE_THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
        ]);
    }

    protected function createDeviceRgb(Location $location): IODevice {
        return $this->createDevice('RGB-801', $location, [
            [SuplaConst::TYPE_RGBLEDCONTROLLER, ChannelFunction::DIMMERANDRGBLIGHTING],
            [SuplaConst::TYPE_RGBLEDCONTROLLER, ChannelFunction::RGBLIGHTING],
        ]);
    }

    private function createDevice(string $name, Location $location, array $channelTypes): IODevice {
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
                'channelNumber' => $channelNumber++,
            ]);
            $this->entityManager->persist($channel);
            $this->entityManager->flush();
        }
        $this->entityManager->refresh($device);
        return $device;
    }
}

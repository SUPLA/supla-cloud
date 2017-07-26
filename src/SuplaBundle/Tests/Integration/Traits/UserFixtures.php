<?php

namespace SuplaBundle\Tests\Integration\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaConst;

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
            SuplaConst::TYPE_RELAY => ChannelFunction::LIGHTSWITCH,
            SuplaConst::TYPE_THERMOMETERDS18B20 => ChannelFunction::THERMOMETER,
        ]);
    }

    private function createDevice(Location $location, array $channelTypes): IODevice {
        $fieldSetter = function ($field, $type) {
            $this->{$field} = $type;
        };

        $device = new IODevice();
        $fieldSetter->call($device, 'guid', rand(0, 9999999));
        $fieldSetter->call($device, 'regDate', new \DateTime());
        $fieldSetter->call($device, 'regIpv4', rand(0, 9999999));
        $fieldSetter->call($device, 'softwareVersion', '2.' . rand(0, 50));
        $fieldSetter->call($device, 'protocolVersion', '2.' . rand(0, 50));
        $fieldSetter->call($device, 'location', $location);
        $fieldSetter->call($device, 'user', $location->getUser());
        $this->getEntityManager()->persist($device);

        $channelNumber = 0;
        foreach ($channelTypes as $channelType => $channelFunction) {
            $channel = new IODeviceChannel();
            $fieldSetter->call($channel, 'iodevice', $device);
            $fieldSetter->call($channel, 'user', $location->getUser());
            $fieldSetter->call($channel, 'type', $channelType);
            $fieldSetter->call($channel, 'function', $channelFunction);
            $fieldSetter->call($channel, 'channelNumber', $channelNumber++);
            $this->getEntityManager()->persist($channel);
        }
        $this->getEntityManager()->flush();
        return $device;
    }

    protected function getEntityManager(): EntityManagerInterface {
        return $this->container->get('doctrine')->getManager();
    }
}

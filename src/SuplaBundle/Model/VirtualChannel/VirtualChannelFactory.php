<?php

namespace SuplaBundle\Model\VirtualChannel;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Repository\IODeviceRepository;

class VirtualChannelFactory {
    /**
     * @param VirtualChannelConfigurator[] $configurators
     */
    public function __construct(
        private IODeviceRepository $deviceRepository,
        private EntityManagerInterface $em,
        private iterable $configurators,
        private VirtualChannelStateUpdater $stateUpdater,
    ) {
    }

    public function createVirtualChannel(User $user, VirtualChannelType $type, array $config): IODeviceChannel {
        $virtualDevice = $this->getVirtualDevice($user);
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'channelNumber', $virtualDevice->getChannels()->count());
        EntityUtils::setField($channel, 'iodevice', $virtualDevice);
        EntityUtils::setField($channel, 'user', $user);
        EntityUtils::setField($channel, 'location', $user->getLocations()->first());
        EntityUtils::setField($channel, 'isVirtual', true);
        foreach ($this->configurators as $configurator) {
            if ($configurator->supports($type)) {
                $channel = $configurator->configureChannel($channel, $config);
            }
        }
        $this->em->persist($channel);
        $this->em->flush();
        $this->stateUpdater->updateChannels([$channel]);
        return $channel;
    }

    private function getVirtualDevice(User $user): IODevice {
        return $this->em->wrapInTransaction(function (EntityManagerInterface $em) use ($user) {
            $virtualDevice = $this->deviceRepository->findAllForUser($user, function (QueryBuilder $query, string $alias) {
                $query->andWhere("$alias.isVirtual = true");
            })->first();
            if (!$virtualDevice) {
                $virtualDevice = new IODevice();
                EntityUtils::setField($virtualDevice, 'isVirtual', true);
                EntityUtils::setField($virtualDevice, 'user', $user);
                EntityUtils::setField($virtualDevice, 'location', $user->getLocations()->first());
                EntityUtils::setField($virtualDevice, 'name', 'SUPLA-VIRTUAL-DEVICE');
                EntityUtils::setField($virtualDevice, 'guid', random_bytes(16));
                EntityUtils::setField($virtualDevice, 'regDate', new \DateTime());
                EntityUtils::setField($virtualDevice, 'protocolVersion', 26);
                EntityUtils::setField($virtualDevice, 'softwareVersion', '1.0');
                $em->persist($virtualDevice);
            }
            return $virtualDevice;
        });
    }
}

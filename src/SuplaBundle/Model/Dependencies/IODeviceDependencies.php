<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;

/**
 * This class is responsible for detecting and possibly clearing all items that depend on the given IO Device..
 */
class IODeviceDependencies extends ActionableSubjectDependencies {
    /** @var ChannelDependencies */
    private $channelDependencies;
    /** @var ScheduleManager */
    private $scheduleManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectConfigTranslator $channelParamConfigTranslator,
        ChannelDependencies $channelDependencies,
        ScheduleManager $scheduleManager
    ) {
        parent::__construct($entityManager, $channelParamConfigTranslator);
        $this->channelDependencies = $channelDependencies;
        $this->scheduleManager = $scheduleManager;
    }

    public function getItemsThatDependOnEnabled(IODevice $device): array {
        $dependencies = [];
        foreach ($device->getChannels() as $channel) {
            $dependencies = array_merge_recursive($dependencies, $this->channelDependencies->getItemsThatDependOnFunction($channel));
        }
        return array_map(function (array $deps) {
            return EntityUtils::uniqueByIds($deps);
        }, $dependencies);
    }

    public function disableDependencies(IODevice $device): void {
        $dependencies = $this->getItemsThatDependOnEnabled($device);
        foreach ($dependencies['reactions'] as $reaction) {
            $reaction->setEnabled(false);
            $this->entityManager->persist($reaction);
        }
        foreach ($dependencies['schedules'] as $schedule) {
            if ($schedule->getEnabled()) {
                $this->scheduleManager->disable($schedule);
            }
        }
    }

    public function getItemsThatDependOnLocation(IODevice $device): array {
        return [
            'channels' => $this->findDependentChannels($device),
        ];
    }

    private function findDependentChannels(IODevice $device): array {
        $dependentChannels = [];
        foreach ($device->getChannels() as $channel) {
            if ($channel->hasInheritedLocation()) {
                $deps = $this->channelDependencies->getItemsThatDependOnLocation($channel);
                if ($deps['channels']) {
                    $dependentChannels[] = $channel;
                    $dependentChannels = array_merge_recursive($dependentChannels, $deps['channels']);
                }
            }
        }
        return EntityUtils::uniqueByIds($dependentChannels);
    }

    public function updateLocation(IODevice $device, Location $location): void {
        $locationDependencies = $this->getItemsThatDependOnLocation($device);
        foreach ($locationDependencies['channels'] as $channel) {
            if ($channel->hasInheritedLocation() && $channel->getIodevice()->getId() === $device->getId()) {
                continue;
            }
            $channel->setLocation($location);
            $this->entityManager->persist($channel);
        }
    }
}

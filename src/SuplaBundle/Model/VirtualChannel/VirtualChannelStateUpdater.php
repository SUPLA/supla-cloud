<?php

namespace SuplaBundle\Model\VirtualChannel;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\ChannelValue;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Supla\SuplaAutodiscover;

class VirtualChannelStateUpdater {
    public function __construct(private SuplaAutodiscover $ad, private EntityManagerInterface $entityManager) {
    }

    /**
     * @param IODeviceChannel[] $channels
     * @return void
     */
    public function updateChannels($channels): void {
        $tasks = [
            'openWeatherUpdates' => [],
        ];
        $channelUpdates = [];
        foreach ($channels as $channel) {
            $channelUpdates[] = [$channel->getId(), $channel->getUser()->getId()];
            $cfg = $channel->getProperty('virtualChannelConfig', []);
            if (($cfg['type'] ?? null) === VirtualChannelType::OPEN_WEATHER) {
                if (($cityId = $cfg['cityId'] ?? null) && ($field = $cfg['weatherField'] ?? null)) {
                    $tasks['openWeatherUpdates'][$cityId][$field][] = $channel->getId();
                }
            }
        }
        $this->setInitialChannelValues($channelUpdates);
        if ($tasks['openWeatherUpdates']) {
            $this->fetchOpenWeatherUpdates($tasks['openWeatherUpdates']);
        }
    }

    private function setInitialChannelValues(array $channelUpdates) {
        $query = 'INSERT IGNORE INTO supla_dev_channel_value (channel_id, user_id, value) VALUES ';
        $query .= implode(',', array_map(fn(array $ch) => "($ch[0], $ch[1], 0)", $channelUpdates));
        $this->entityManager->getConnection()->executeQuery($query);
    }

    private function fetchOpenWeatherUpdates(array $openWeatherUpdates): void {
        $cityIds = array_keys($openWeatherUpdates);
        $weatherData = $this->ad->fetchOpenWeatherData($cityIds);
        foreach ($weatherData as $cityWeather) {
            ['id' => $cityId, 'fetchedAt' => $fetchedAt, 'weather' => $weather] = $cityWeather;
            foreach ($openWeatherUpdates[$cityId] as $field => $channelIds) {
                $query = sprintf(
                    'UPDATE %s cv SET cv.updateTime=:fetchedAt, cv.validTo=:validTo, cv.value=:value WHERE cv.channel IN (:channelIds)',
                    ChannelValue::class
                );
                $fetchedAtDate = new \DateTime($fetchedAt);
                $validToDate = clone $fetchedAtDate;
                $validToDate->add(new \DateInterval('PT1H'));
                $this->entityManager
                    ->createQuery($query)
                    ->execute([
                        'value' => $weather[$field] ?? 0,
                        'fetchedAt' => $fetchedAtDate,
                        'validTo' => $validToDate,
                        'channelIds' => $channelIds,
                    ]);
            }
        }
    }
}

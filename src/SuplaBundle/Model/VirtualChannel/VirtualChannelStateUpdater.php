<?php

namespace SuplaBundle\Model\VirtualChannel;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\ChannelValue;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\MeasurementLogs\EnergyPriceLogItem;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Supla\SuplaAutodiscover;
use SuplaBundle\Utils\DateUtils;

class VirtualChannelStateUpdater {
    public function __construct(
        private SuplaAutodiscover $ad,
        private EntityManagerInterface $entityManager,
        private EntityManagerInterface $measurementLogsEntityManager,
        private TimeProvider $timeProvider,
    ) {
    }

    /**
     * @param IODeviceChannel[] $channels
     * @return void
     */
    public function updateChannels($channels): void {
        $tasks = [
            'openWeatherUpdates' => [],
            'energyPriceForecastUpdates' => [],
        ];
        $channelUpdates = [];
        foreach ($channels as $channel) {
            $channelUpdates[] = [$channel->getId(), $channel->getUser()->getId()];
            $cfg = $channel->getProperty('virtualChannelConfig', []);
            $virtualType = $cfg['type'] ?? null;
            if ($virtualType === VirtualChannelType::OPEN_WEATHER) {
                if (($cityId = $cfg['cityId'] ?? null) && ($field = $cfg['weatherField'] ?? null)) {
                    $tasks['openWeatherUpdates'][$cityId][$field][] = $channel->getId();
                }
            } elseif ($virtualType === VirtualChannelType::ENERGY_PRICE_FORECAST) {
                if (($field = $cfg['energyField'] ?? null)) {
                    $tasks['energyPriceForecastUpdates'][$field][] = $channel->getId();
                }
            }
        }
        $this->setInitialChannelValues($channelUpdates);
        if ($tasks['openWeatherUpdates']) {
            $this->fetchOpenWeatherUpdates($tasks['openWeatherUpdates']);
        }
        if ($tasks['energyPriceForecastUpdates']) {
            $this->updateEnergyPriceForecastValue($tasks['energyPriceForecastUpdates']);
        }
    }

    private function setInitialChannelValues(array $channelUpdates) {
        $query = 'INSERT IGNORE INTO supla_dev_channel_value (channel_id, user_id, value, update_time, valid_to) VALUES ';
        $zero = ChannelValue::packValue(ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(), 0);
        $now = DateUtils::timestampToMysqlUtc($this->timeProvider->getTimestamp('-1 minute'));
        $query .= implode(',', array_map(fn(array $ch) => "($ch[0], $ch[1], '$zero', '$now', '$now')", $channelUpdates));
        $this->entityManager->getConnection()->executeQuery($query);
    }

    private function fetchOpenWeatherUpdates(array $openWeatherUpdates): void {
        $cityIds = array_keys($openWeatherUpdates);
        $weatherData = $this->ad->fetchOpenWeatherData($cityIds);
        foreach ($weatherData as $cityWeather) {
            ['id' => $cityId, 'fetchedAt' => $fetchedAt, 'weather' => $weather] = $cityWeather;
            foreach ($openWeatherUpdates[$cityId] as $field => $channelIds) {
                $query = sprintf(
                    'UPDATE %s cv SET cv.updateTime=CURRENT_TIMESTAMP(), cv.validTo=:validTo, cv.value=:value ' .
                    'WHERE cv.channel IN (:channelIds)',
                    ChannelValue::class
                );
                $fetchedAtDate = new \DateTime($fetchedAt);
                $validToDate = clone $fetchedAtDate;
                $validToDate->add(new \DateInterval('PT1H'));
                $fnc = OpenWeatherVirtualChannelConfigurator::fieldNameToFunction($field);
                $readField = $field === 'tempHumidity' ? 'temp' : $field;
                $value = ChannelValue::packValue($fnc, $weather[$readField] ?? 0, $weather['humidity'] ?? 0);
                $this->entityManager
                    ->createQuery($query)
                    ->execute([
                        'value' => $value,
                        'validTo' => $validToDate,
                        'channelIds' => $channelIds,
                    ]);
            }
        }
    }

    private function updateEnergyPriceForecastValue(array $energyPriceForecastUpdates): void {
        $repo = $this->measurementLogsEntityManager->getRepository(EnergyPriceLogItem::class);
        $currentEnergyPriceForecast = $repo->createQueryBuilder('e')
            ->where('e.dateFrom <= :now')
            ->setParameter('now', $this->timeProvider->getDateTime())
            ->setMaxResults(1)
            ->orderBy('e.dateFrom', 'DESC')
            ->getQuery()
            ->getResult();
        if ($currentEnergyPriceForecast) {
            /** @var EnergyPriceLogItem $log */
            $log = current($currentEnergyPriceForecast);
            $values = [
                'rce' => $log->getRce(),
                'fixing1' => $log->getFixing1(),
                'fixing2' => $log->getFixing2(),
            ];
            foreach ($energyPriceForecastUpdates as $field => $channelIds) {
                $query = sprintf(
                    'UPDATE %s cv SET cv.updateTime=CURRENT_TIMESTAMP(), cv.validTo=:validTo, cv.value=:value ' .
                    'WHERE cv.channel IN (:channelIds)',
                    ChannelValue::class
                );
                $value = ChannelValue::packValue(ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(), $values[$field]);
                $this->entityManager
                    ->createQuery($query)
                    ->execute([
                        'value' => $value,
                        'validTo' => $log->getDateTo(),
                        'channelIds' => $channelIds,
                    ]);
            }
        }
    }
}

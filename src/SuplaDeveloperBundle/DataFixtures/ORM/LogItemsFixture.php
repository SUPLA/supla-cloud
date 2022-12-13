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

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageLogItem;
use SuplaBundle\Entity\MeasurementLogs\ImpulseCounterLogItem;
use SuplaBundle\Entity\MeasurementLogs\TemperatureLogItem;
use SuplaBundle\Entity\MeasurementLogs\TempHumidityLogItem;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\Traits\MysqlUtcDate;

class LogItemsFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;
    /** @var ObjectManager */
    private $entityManager;
    /** @var Generator */
    private $faker;

    const SINCE = '-40 day';

    public function __construct(Registry $doctrine) {
        $this->faker = Factory::create('pl_PL');
        $this->entityManager = $doctrine->getManager('measurement_logs');
    }

    public function load(ObjectManager $manager) {
        ini_set('memory_limit', '1G');
        $this->createTemperatureLogItems();
        $this->entityManager->flush();
        $this->createHumidityLogItems();
        $this->entityManager->flush();
        $this->createTemperatureAndHumidityLogItems();
        $this->entityManager->flush();
        $this->createImpulseCounterLogItems();
        $this->entityManager->flush();
        $this->createElectricityMeterLogItems();
        $this->entityManager->flush();
        $this->createElectricityMeterVoltageLogItems();
        $this->entityManager->flush();
    }

    private function createTemperatureLogItems() {
        $sonoff = $this->getReference(DevicesFixture::DEVICE_SONOFF);
        /** @var \SuplaBundle\Entity\Main\IODeviceChannel $thermometer */
        $thermometer = $sonoff->getChannels()[1];
        $thermometerId = $thermometer->getId();
        $from = strtotime(self::SINCE);
        $to = time();
        $temperature = 10;
        for ($timestamp = $from; $timestamp < $to; $timestamp += 600) {
            $logItem = new TemperatureLogItem();
            EntityUtils::setField($logItem, 'channel_id', $thermometerId);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . $timestamp));
            $temperature += ($this->faker->boolean() ? -1 : 1) * $this->faker->biasedNumberBetween(0, 100) / 100;
            EntityUtils::setField($logItem, 'temperature', $temperature);
            if ($this->faker->boolean(95)) {
                $this->entityManager->persist($logItem);
            }
        }
    }

    private function createHumidityLogItems() {
        /** @var \SuplaBundle\Entity\Main\IODevice $device */
        $device = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION);
        $humidity = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() === ChannelFunction::HUMIDITY;
        })->first();
        $channelId = $humidity->getId();
        $from = strtotime(self::SINCE);
        $to = time();
        $humidity = 10;
        for ($timestamp = $from; $timestamp < $to; $timestamp += 600) {
            $logItem = new TempHumidityLogItem();
            EntityUtils::setField($logItem, 'channel_id', $channelId);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . $timestamp));
            $humidity += ($this->faker->boolean() ? -1 : 1) * $this->faker->biasedNumberBetween(0, 100) / 100;
            $humidity = max(0, min(100, $humidity));
            EntityUtils::setField($logItem, 'temperature', 0);
            EntityUtils::setField($logItem, 'humidity', $humidity);
            if ($this->faker->boolean(95)) {
                $this->entityManager->persist($logItem);
            }
        }
    }

    private function createTemperatureAndHumidityLogItems() {
        /** @var \SuplaBundle\Entity\Main\IODevice $device */
        $device = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION);
        $tempAndHumidity = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() === ChannelFunction::HUMIDITYANDTEMPERATURE;
        })->first();
        $channelId = $tempAndHumidity->getId();
        $from = strtotime(self::SINCE);
        $to = time();
        $temperature = 10;
        $humidity = 50;
        for ($timestamp = $from; $timestamp < $to; $timestamp += 600) {
            $logItem = new TempHumidityLogItem();
            EntityUtils::setField($logItem, 'channel_id', $channelId);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . $timestamp));
            $temperature += ($this->faker->boolean() ? -1 : 1) * $this->faker->biasedNumberBetween(0, 100) / 100;
            $humidity += ($this->faker->boolean() ? -1 : 1) * $this->faker->biasedNumberBetween(0, 100) / 100;
            $humidity = max(0, min(100, $humidity));
            EntityUtils::setField($logItem, 'temperature', $temperature);
            EntityUtils::setField($logItem, 'humidity', $humidity);
            if ($this->faker->boolean(95)) {
                $this->entityManager->persist($logItem);
            }
        }
    }

    private function createImpulseCounterLogItems() {
        $device = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION);
        $gasMeterIc = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() === ChannelFunction::IC_GASMETER;
        })->first();
        $heatMeterIc = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() === ChannelFunction::IC_HEATMETER;
        })->first();
        $electricityMeterIc = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() === ChannelFunction::IC_ELECTRICITYMETER;
        })->first();
        $waterMeterIc = $this->getReference(DevicesFixture::DEVICE_FULL)->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() === ChannelFunction::IC_WATERMETER;
        })->first();
        foreach ([$gasMeterIc, $heatMeterIc, $electricityMeterIc, $waterMeterIc] as $channel) {
            $channelId = $channel->getId();
            $from = strtotime(self::SINCE);
            $to = time();
            $counter = 0;
            for ($timestamp = $from; $timestamp < $to; $timestamp += 600) {
                $logItem = new ImpulseCounterLogItem();
                EntityUtils::setField($logItem, 'channel_id', $channelId);
                EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . $timestamp));
                $counter += $this->faker->biasedNumberBetween(0, 10);
                EntityUtils::setField($logItem, 'counter', $counter);
                EntityUtils::setField($logItem, 'calculated_value', ($counter / 100) * 1000);
                if ($this->faker->boolean(95)) {
                    $this->entityManager->persist($logItem);
                }
                if ($this->faker->boolean(1)) {
                    $counter = 0;
                }
            }
        }
    }

    private function createElectricityMeterLogItems() {
        $device = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION);
        $ecChannel = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getType()->getId() === ChannelType::ELECTRICITYMETER;
        })->first();
        $channelId = $ecChannel->getId();
        $from = strtotime(self::SINCE);
        $to = time();
        $state = [
            'phase1_fae' => 1,
            'phase1_rae' => 0,
            'phase1_fre' => 1,
            'phase1_rre' => 2,
            'phase2_fae' => 1,
            'phase2_rae' => 0,
            'phase2_fre' => 1,
            'phase2_rre' => 2,
            'phase3_fae' => 1,
            'phase3_rae' => 0,
            'phase3_fre' => 1,
            'phase3_rre' => 2,
            'fae_balanced' => 3,
            'rae_balanced' => 3,
        ];
        for ($timestamp = $from; $timestamp < $to; $timestamp += 600) {
            $logItem = new ElectricityMeterLogItem();
            EntityUtils::setField($logItem, 'channel_id', $channelId);
            EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . $timestamp));
            foreach ($state as $stateName => $value) {
                $state[$stateName] += $this->faker->biasedNumberBetween(0, 10);
                EntityUtils::setField($logItem, $stateName, $state[$stateName]);
            }
            if ($this->faker->boolean(95)) {
                $this->entityManager->persist($logItem);
            }
        }
    }

    public function createElectricityMeterVoltageLogItems(?int $channelId = null, ?string $since = null, int $phaseNo = null) {
        if (!$channelId) {
            $device = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION);
            $ecChannel = $device->getChannels()->filter(function (IODeviceChannel $channel) {
                return $channel->getType()->getId() === ChannelType::ELECTRICITYMETER;
            })->first();
            $channelId = $ecChannel->getId();
        }
        $from = strtotime($since ?: self::SINCE);
        $to = time();
        for ($timestamp = $from; $timestamp < $to; $timestamp += 600) {
            $above = $this->faker->boolean(2);
            $below = $this->faker->boolean(2);
            if ($above || $below) {
                $logItem = new ElectricityMeterVoltageLogItem();
                EntityUtils::setField($logItem, 'channel_id', $channelId);
                EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . $timestamp));
                $state = [
                    'phaseNo' => $phaseNo ?: $this->faker->numberBetween(1, 3),
                    'countAbove' => $above ? $this->faker->numberBetween(1, 10) : 0,
                    'countBelow' => $below ? $this->faker->numberBetween(1, 10) : 0,
                    'secAbove' => $above ? $this->faker->numberBetween(1, 300) : 0,
                    'secBelow' => $below ? $this->faker->numberBetween(1, 300) : 0,
                    'minVoltage' => $this->faker->numberBetween($below ? 22000 : 23000, $below ? 23000 : 24000) / 100,
                    'maxVoltage' => $this->faker->numberBetween($above ? 25000 : 23000, $above ? 27000 : 24000) / 100,
                    'measurementTimeSec' => $this->faker->numberBetween(595, 605),
                ];
                $state['countTotal'] = $state['countAbove'] + $state['countBelow'];
                $state['secTotal'] = $state['secAbove'] + $state['secBelow'];
                $state['maxSecAbove'] = $above
                    ? ($state['countAbove'] === 1 ? $state['secAbove'] : $this->faker->numberBetween(1, $state['secAbove']))
                    : 0;
                $state['maxSecBelow'] = $below
                    ? ($state['countBelow'] === 1 ? $state['secBelow'] : $this->faker->numberBetween(1, $state['secBelow']))
                    : 0;
                $state['avgVoltage'] = $this->faker->numberBetween($state['minVoltage'] * 100, $state['maxVoltage'] * 100) / 100;
                foreach ($state as $stateName => $value) {
                    EntityUtils::setField($logItem, $stateName, $state[$stateName]);
                }
                $this->entityManager->persist($logItem);
            }
        }
    }
}

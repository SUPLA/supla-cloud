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
use Faker\Factory;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\ImpulseCounterLogItem;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\TemperatureLogItem;
use SuplaBundle\Entity\TempHumidityLogItem;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Tests\Integration\Traits\MysqlUtcDate;

class LogItemsFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;
    /** @var ObjectManager */
    private $entityManager;
    /** @var \Faker\Generator */
    private $faker;

    const SINCE = '-40 day';

    public function load(ObjectManager $manager) {
        ini_set('memory_limit', '1G');
        $this->entityManager = $manager;
        $this->faker = Factory::create('pl_PL');
        $this->createTemperatureLogItems();
        $this->entityManager->flush();
        $this->createTemperatureAndHumidityLogItems();
        $this->entityManager->flush();
        $this->createImpulseCounterLogItems();
        $this->entityManager->flush();
    }

    private function createTemperatureLogItems() {
        $sonoff = $this->getReference(DevicesFixture::DEVICE_SONOFF);
        /** @var IODeviceChannel $thermometer */
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

    private function createTemperatureAndHumidityLogItems() {
        /** @var IODevice $device */
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
            return $channel->getFunction()->getId() === ChannelFunction::GASMETER;
        })->first();
        $heatMeterIc = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() === ChannelFunction::HEATMETER;
        })->first();
        foreach ([$gasMeterIc, $heatMeterIc] as $channel) {
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
            }
        }
    }
}

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
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\TemperatureLogItem;

class LogItemsFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;
    /** @var ObjectManager */
    private $entityManager;
    /** @var \Faker\Generator */
    private $faker;

    const SINCE = '-1 day';

    public function load(ObjectManager $manager) {
        $this->entityManager = $manager;
        $this->faker = Factory::create('pl_PL');
        $this->createTemperatureLogItems();
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
            EntityUtils::setField($logItem, 'date', new \DateTime('@' . $timestamp));
            $temperature += ($this->faker->boolean() ? -1 : 1) * $this->faker->biasedNumberBetween(0, 100) / 100;
            EntityUtils::setField($logItem, 'temperature', $temperature);
            if ($this->faker->boolean(99)) {
                $this->entityManager->persist($logItem);
            }
        }
        $this->entityManager->flush();
    }
}

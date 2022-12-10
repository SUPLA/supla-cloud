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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\Schedule\ScheduleManager;

class SchedulesFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var Generator */
    private $faker;
    private $scheduleFactories;
    /** @var ScheduleManager */
    private $scheduleManager;

    public function __construct(ScheduleManager $scheduleManager) {
        $this->scheduleManager = $scheduleManager;
    }

    public function load(ObjectManager $manager) {
        $this->faker = Factory::create('pl_PL');
        $this->entityManager = $manager;
        $this->scheduleFactories = [
            function (IODeviceChannel $channel) {
                $s = new Schedule($channel->getUser());
                $s->setMode(ScheduleMode::MINUTELY());
                $s->setConfig([
                    [
                        'crontab' => '*/' . $this->faker->randomElement([5, 10, 15, 30, 60, 90]) . ' * * * *',
                        'action' => ['id' => $this->faker->randomElement($channel->getPossibleActions())->getId()],
                    ],
                ]);
                return $s;
            },
            function (IODeviceChannel $channel) {
                $s = new Schedule($channel->getUser());
                $s->setMode(ScheduleMode::DAILY());
                $s->setConfig([
                    [
                        'crontab' => 'S' . $this->faker->randomElement(['S', 'R']) . $this->faker->randomElement([-10, 0, 10]) . ' * * * *',
                        'action' => ['id' => $this->faker->randomElement($channel->getPossibleActions())->getId()],
                    ],
                ]);
                return $s;
            },
        ];
        $this->createRandomSchedules();
        $manager->flush();
    }

    private function createRandomSchedules() {
        $randomDevices = [];
        for ($i = 0; $i < DevicesFixture::NUMBER_OF_RANDOM_DEVICES; $i++) {
            $randomDevices[] = $this->getReference(DevicesFixture::RANDOM_DEVICE_PREFIX . $i);
        }
        for ($i = 0; $i < 15; $i++) {
            /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
            do {
                $channel = $this->faker->randomElement($randomDevices)->getChannels()[$this->faker->numberBetween(0, 3)];
            } while (!$channel->getPossibleActions());
            $schedule = $this->faker->randomElement($this->scheduleFactories)($channel);
            $schedule->setCaption($this->faker->sentence($this->faker->numberBetween(2, 4)));
            $schedule->setSubject($channel);
            $schedule->setDateStart($this->faker->dateTimeBetween('now', '+1 week'));
            $this->entityManager->persist($schedule);
            $this->scheduleManager->enable($schedule);
        }
    }
}

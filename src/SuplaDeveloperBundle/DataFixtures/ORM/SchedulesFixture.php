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
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\Schedule\ScheduleManager;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

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
                $s->setTimeExpression('*/' . $this->faker->randomElement([5, 10, 15, 30, 60, 90]) . ' * * * *');
                return $s;
            },
            function (IODeviceChannel $channel) {
                $s = new Schedule($channel->getUser());
                $s->setMode(ScheduleMode::DAILY());
                $s->setTimeExpression('S' . $this->faker->randomElement(['S', 'R']) . $this->faker->randomElement([-10, 0, 10]) . ' * * * *');
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

        for ($i = 0; $i < 20; $i++) {
            /** @var IODeviceChannel $channel */
            $channel = $this->faker->randomElement($randomDevices)->getChannels()[$this->faker->numberBetween(0, 3)];
            $schedule = $this->faker->randomElement($this->scheduleFactories)($channel);
            $schedule->setCaption($this->faker->sentence($this->faker->numberBetween(2, 4)));
            $schedule->setAction($this->faker->randomElement($channel->getFunction()->getPossibleActions()));
            $schedule->setSubject($channel);
            $schedule->setDateStart($this->faker->dateTimeBetween('now', '+1 week'));
            $this->entityManager->persist($schedule);
            $this->scheduleManager->enable($schedule);
        }
    }

    private function createSuplerLink() {
        $channel = $this->getReference(DevicesFixture::DEVICE_SUPLER)->getChannels()[0];
        $directLink = new DirectLink($channel);
        $directLink->generateSlug(new PlaintextPasswordEncoder());
        $directLink->setAllowedActions([ChannelFunctionAction::READ()]);
        $directLink->setCaption('SUPLAER Direct Link');
        $this->entityManager->persist($directLink);
    }
}

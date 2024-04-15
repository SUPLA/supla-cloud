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

use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use SuplaBundle\Entity\Main\ValueBasedTrigger;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Serialization\RequestFiller\ValueBasedTriggerRequestFiller;

class ReactionsFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;

    private Generator $faker;
    private ValueBasedTriggerRequestFiller $requestFiller;

    public function __construct(ValueBasedTriggerRequestFiller $requestFiller) {
        $this->faker = Factory::create('pl_PL');;
        $this->requestFiller = $requestFiller;
    }

    public function load(ObjectManager $manager) {
        $this->createTemperatureReactions($manager);
        $manager->flush();
    }

    private function createTemperatureReactions(ObjectManager $manager) {
        $sonoff = $this->getReference(DevicesFixture::DEVICE_SONOFF);
        $thermometer = $sonoff->getChannels()[1];
        $vbt = new ValueBasedTrigger($thermometer);
        $this->requestFiller->fillFromData([
            'subjectType' => ActionableSubjectType::CHANNEL,
            'subjectId' => $sonoff->getChannels()[0]->getId(),
            'actionId' => ChannelFunctionAction::TOGGLE,
            'trigger' => ['on_change_to' => ['lt' => 20, 'name' => 'temperature', 'resume' => ['ge' => 21]]],
        ], $vbt);
        $manager->persist($vbt);
        $vbt = new ValueBasedTrigger($thermometer);
        $this->requestFiller->fillFromData([
            'subjectType' => ActionableSubjectType::NOTIFICATION,
            'actionId' => ChannelFunctionAction::SEND,
            'actionParam' => ['body' => 'ABC', 'accessIds' => [1]],
            'trigger' => ['on_change_to' => ['lt' => 20, 'name' => 'temperature', 'resume' => ['ge' => 21]]],
        ], $vbt);
        $manager->persist($vbt);
    }
}

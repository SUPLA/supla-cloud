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
use InvalidArgumentException;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Utils\SceneUtils;

class ScenesFixture extends SuplaFixture {
    const ORDER = ChannelGroupsFixture::ORDER + 1;

    /** @var Generator */
    private $faker;

    public function load(ObjectManager $manager) {
        $this->faker = Factory::create('pl_PL');
        $this->createSampleScene($manager);
        $this->createRandomScenes($manager);
        $manager->flush();
    }

    private function createSampleScene(ObjectManager $manager) {
        $scene = new Scene($this->getReference(UsersFixture::USER)->getLocations()[0]);
        /** @var IODevice $deviceFull */
        $deviceFull = $this->getReference(DevicesFixture::DEVICE_EVERY_FUNCTION);
        $rgb = $deviceFull->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::DIMMERANDRGBLIGHTING;
        })->first();
        $gate = $deviceFull->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::CONTROLLINGTHEGATE;
        })->first();
        $op1 = new SceneOperation($this->getReference(DevicesFixture::DEVICE_SONOFF)->getChannels()[0], ChannelFunctionAction::TOGGLE());
        $op2 = new SceneOperation($rgb, ChannelFunctionAction::SET_RGBW_PARAMETERS(), ['brightness' => 55], 2000);
        $op3 = new SceneOperation($gate, ChannelFunctionAction::OPEN_CLOSE());
        $scene->setEnabled(true);
        $scene->setCaption('My scene');
        $scene->setOpeartions([$op1, $op2, $op3]);
        $manager->persist($scene);
    }

    private function createRandomScenes(ObjectManager $manager) {
        $locations = [
            $this->getReference(LocationsFixture::LOCATION_GARAGE),
            $this->getReference(LocationsFixture::LOCATION_OUTSIDE),
            $this->getReference(LocationsFixture::LOCATION_BEDROOM),
        ];
        $user = $this->getReference(UsersFixture::USER);
        $subjectFactories = [
            function (User $user) {
                do {
                    /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
                    $channel = $this->faker->randomElement($user->getChannels());
                } while (!$channel->getFunction()->isOutput());
                return $channel;
            },
            function (User $user) {
                return $this->faker->randomElement($user->getChannelGroups());
            },
            function (User $user) {
                return $this->faker->randomElement($user->getScenes());
            },
        ];
        for ($sceneNo = 0; $sceneNo < 15; $sceneNo++) {
            $scene = new Scene($this->faker->randomElement($locations));
            $numberOfOperations = $this->faker->numberBetween(1, 10);
            $operations = [];
            for ($i = 0; $i < $numberOfOperations; $i++) {
                $action = null;
                do {
                    /** @var ActionableSubject $subject */
                    $subject = $this->faker->randomElement($subjectFactories)($user);
                    if ($subject) {
                        $action = $this->faker->randomElement($subject->getPossibleActions());
                    }
                } while (!$action);
                $sceneOperation = new SceneOperation($subject, $action, [], $this->faker->randomElement([0, 0, 0, 1000, 30000]));
                $operations[] = $sceneOperation;
            }
            $scene->setEnabled($this->faker->boolean(80));
            $scene->setCaption($this->faker->colorName);
            $scene->setOpeartions($operations);
            try {
                SceneUtils::ensureOperationsAreNotCyclic($scene);
                $manager->persist($scene);
                $manager->flush();
                $manager->refresh($user);
            } catch (InvalidArgumentException $e) {
                $sceneNo--;
            }
        }
    }
}

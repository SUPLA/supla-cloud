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
use SuplaBundle\Entity\Main\DirectLink;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunctionAction;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

class DirectLinksFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var Generator */
    private $faker;

    public function load(ObjectManager $manager) {
        $this->faker = Factory::create('pl_PL');
        $this->entityManager = $manager;
        $this->createRandomLinks();
        $this->createSuplerLink();
        $manager->flush();
    }

    private function createRandomLinks() {
        $randomDevices = [];
        for ($i = 0; $i < DevicesFixture::NUMBER_OF_RANDOM_DEVICES; $i++) {
            $randomDevices[] = $this->getReference(DevicesFixture::RANDOM_DEVICE_PREFIX . $i);
        }
        for ($i = 0; $i < 20; $i++) {
            $channels = $this->faker->randomElement($randomDevices)->getChannels();
            /** @var IODeviceChannel $channel */
            $channel = $this->faker->randomElement($channels);
            $directLink = new DirectLink($channel);
            $directLink->generateSlug(new PlaintextPasswordEncoder());
            $action = $this->faker->randomElement(array_merge([ChannelFunctionAction::READ()], $channel->getPossibleActions()));
            $directLink->setAllowedActions([$action]);
            $directLink->setCaption($this->faker->colorName);
            $this->entityManager->persist($directLink);
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

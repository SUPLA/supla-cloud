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
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\SceneOperation;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class ScenesFixture extends SuplaFixture {
    const ORDER = DevicesFixture::ORDER + 1;

    public function load(ObjectManager $manager) {
        $scene = new Scene($this->getReference(UsersFixture::USER)->getLocations()[0]);
        /** @var IODevice $deviceFull */
        $deviceFull = $this->getReference(DevicesFixture::DEVICE_MEGA);
        $rgb = $deviceFull->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::DIMMERANDRGBLIGHTING;
        })->first();
        $gate = $deviceFull->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::OPENINGSENSOR_GATE;
        })->first();
        $op1 = new SceneOperation($this->getReference(DevicesFixture::DEVICE_SONOFF)->getChannels()[0], ChannelFunctionAction::TURN_ON());
        $op2 = new SceneOperation($rgb, ChannelFunctionAction::SET_RGBW_PARAMETERS(), ['brightness' => 55], 2000);
        $op3 = new SceneOperation($gate, ChannelFunctionAction::OPEN_CLOSE());
        $scene->setEnabled(true);
        $scene->setCaption('My scene');
        $scene->setOpeartions([$op1, $op2, $op3]);
        $manager->persist($scene);
        $manager->flush();
    }
}

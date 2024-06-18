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

namespace SuplaBundle\Tests\Entity;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Enums\ChannelType;

class IODeviceChannelTest extends TestCase {
    public function testSettingParams() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $channel->setParam(2, 3);
        $this->assertEquals(3, $channel->getParam2());
        $this->assertEquals(3, $channel->getParam(2));
    }

    public function testSettingInvalidParam() {
        $this->expectException(InvalidArgumentException::class);
        $channel = new IODeviceChannel();
        $channel->setParam(50, 3);
    }

    public function testSettingLocation() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $location = $this->createMock(\SuplaBundle\Entity\Main\Location::class);
        $channel->setLocation($location);
        $this->assertEquals($location, $channel->getLocation());
        $this->assertFalse($channel->hasInheritedLocation());
    }

    public function testGettingLocationFromDevice() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $location = $this->createMock(\SuplaBundle\Entity\Main\Location::class);
        $ioDevice = $this->createMock(IODevice::class);
        $ioDevice->method('getLocation')->willReturn($location);
        EntityUtils::setField($channel, 'iodevice', $ioDevice);
        $this->assertEquals($location, $channel->getLocation());
        $this->assertTrue($channel->hasInheritedLocation());
    }

    public function testSettingTheSameLocationAsDeviceClearsInheritance() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $location = $this->createMock(\SuplaBundle\Entity\Main\Location::class);
        $ioDevice = $this->createMock(IODevice::class);
        $ioDevice->method('getLocation')->willReturn($location);
        EntityUtils::setField($channel, 'iodevice', $ioDevice);
        $channel->setLocation($location);
        $this->assertEquals($location, $channel->getLocation());
        $this->assertFalse($channel->hasInheritedLocation());
    }

    public function testGettingUnknownFunction() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        EntityUtils::setField($channel, 'function', 123);
        $this->assertEquals(ChannelFunction::UNSUPPORTED()->getName(), $channel->getFunction()->getName());
        $this->assertEquals(123, $channel->getFunction()->getId());
    }

    public function testGettingUnknownType() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        EntityUtils::setField($channel, 'type', 123);
        $this->assertEquals(ChannelType::UNSUPPORTED()->getName(), $channel->getType()->getName());
        $this->assertEquals(123, $channel->getType()->getId());
    }

    public function testSettingChannelParams() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $channel->setParam(1, 1);
        $this->assertEquals(1, $channel->getParam(1));
        $channel->setParam(2, 10);
        $this->assertEquals(10, $channel->getParam(2));
        $channel->setParam(3, 33);
        $this->assertEquals(33, $channel->getParam(3));
        $channel->setParam(4, 11);
        $this->assertEquals(11, $channel->getParam(4));
    }

    public function testSettingInvalidChannelParam0() {
        $this->expectException(InvalidArgumentException::class);
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $channel->setParam(0, 111);
    }

    public function testSettingInvalidChannelParam5() {
        $this->expectException(InvalidArgumentException::class);
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $channel->setParam(5, 111);
    }

    public function testGettingPossibleActions() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $channel->setFunction(ChannelFunction::LIGHTSWITCH());
        $functionIds = EntityUtils::mapToIds($channel->getPossibleActions());
        $this->assertEquals([
            ChannelFunctionAction::TURN_ON,
            ChannelFunctionAction::TURN_OFF,
            ChannelFunctionAction::TOGGLE,
            ChannelFunctionAction::COPY,
        ], $functionIds);
    }

    public function testGettingPossibleActionsForRollerShutter() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $channel->setFunction(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER());
        $functionIds = EntityUtils::mapToIds($channel->getPossibleActions());
        $this->assertEquals([
            ChannelFunctionAction::REVEAL,
            ChannelFunctionAction::SHUT,
            ChannelFunctionAction::REVEAL_PARTIALLY,
            ChannelFunctionAction::SHUT_PARTIALLY,
            ChannelFunctionAction::STOP,
            ChannelFunctionAction::COPY,
        ], $functionIds);
    }

    public function testGettingPossibleActionsForRollerShutterWithStartStopActionsSupported() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $channel->setFunction(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER());
        EntityUtils::setField($channel, 'flags', ChannelFunctionBitsFlags::getAllFeaturesFlag());
        $functionIds = EntityUtils::mapToIds($channel->getPossibleActions());
        $this->assertEquals([
            ChannelFunctionAction::REVEAL,
            ChannelFunctionAction::SHUT,
            ChannelFunctionAction::REVEAL_PARTIALLY,
            ChannelFunctionAction::SHUT_PARTIALLY,
            ChannelFunctionAction::STOP,
            ChannelFunctionAction::COPY,
            ChannelFunctionAction::UP_OR_STOP,
            ChannelFunctionAction::DOWN_OR_STOP,
            ChannelFunctionAction::STEP_BY_STEP,
        ], $functionIds);
    }

    public function testAtIsAlwaysHidden() {
        $channel = new IODeviceChannel();
        $channel->setFunction(ChannelFunction::ACTION_TRIGGER());
        $this->assertTrue($channel->getHidden());
    }

    public function testCantChangeAtHidden() {
        $channel = new \SuplaBundle\Entity\Main\IODeviceChannel();
        $channel->setFunction(ChannelFunction::ACTION_TRIGGER());
        $channel->setHidden(true);
        $this->assertTrue($channel->getHidden());
        $channel->setFunction(ChannelFunction::THERMOMETER());
        $this->assertFalse($channel->getHidden());
    }
}

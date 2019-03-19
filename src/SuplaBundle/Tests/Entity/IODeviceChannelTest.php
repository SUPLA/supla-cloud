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
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Location;
use SuplaBundle\Enums\ChannelFunction;

class IODeviceChannelTest extends \PHPUnit_Framework_TestCase {
    public function testSettingParams() {
        $channel = new IODeviceChannel();
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
        $channel = new IODeviceChannel();
        $location = $this->createMock(Location::class);
        $channel->setLocation($location);
        $this->assertEquals($location, $channel->getLocation());
        $this->assertFalse($channel->hasInheritedLocation());
    }

    public function testGettingLocationFromDevice() {
        $channel = new IODeviceChannel();
        $location = $this->createMock(Location::class);
        $ioDevice = $this->createMock(IODevice::class);
        $ioDevice->method('getLocation')->willReturn($location);
        EntityUtils::setField($channel, 'iodevice', $ioDevice);
        $this->assertEquals($location, $channel->getLocation());
        $this->assertTrue($channel->hasInheritedLocation());
    }

    public function testSettingTheSameLocationAsDeviceClearsInheritance() {
        $channel = new IODeviceChannel();
        $location = $this->createMock(Location::class);
        $ioDevice = $this->createMock(IODevice::class);
        $ioDevice->method('getLocation')->willReturn($location);
        EntityUtils::setField($channel, 'iodevice', $ioDevice);
        $channel->setLocation($location);
        $this->assertEquals($location, $channel->getLocation());
        $this->assertFalse($channel->hasInheritedLocation());
    }

    public function testGettingUnknownFunction() {
        $channel = new IODeviceChannel();
        EntityUtils::setField($channel, 'function', 123);
        $this->assertEquals(ChannelFunction::UNSUPPORTED()->getName(), $channel->getFunction()->getName());
        $this->assertEquals(123, $channel->getFunction()->getId());
    }
}

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

namespace SuplaApiBundle\Tests\Model;

use SuplaApiBundle\ParamConverter\IODeviceChannelParamConverter;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class IODeviceChannelParamConverterTest extends \PHPUnit_Framework_TestCase {
    /** @var IODeviceChannelParamConverter */
    private $converter;

    /** @before */
    public function init() {
        $this->converter = new IODeviceChannelParamConverter();
    }

    public function testFromEmptyArray() {
        $channel = $this->converter->convert([]);
        $this->assertInstanceOf(IODeviceChannel::class, $channel);
    }

    public function testFunctionFromInt() {
        $channel = $this->converter->convert(['function' => ChannelFunction::CONTROLLINGTHEGATEWAYLOCK]);
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), $channel->getFunction());
    }

    public function testFunctionFromArrayWithId() {
        $channel = $this->converter->convert(['function' => ['id' => ChannelFunction::CONTROLLINGTHEGATE()]]);
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGATE(), $channel->getFunction());
    }

    public function testInvalidFunction() {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->convert(['function' => ['id' => -1]]);
    }
}

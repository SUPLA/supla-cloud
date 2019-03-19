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

namespace SuplaBundle\Tests\Model\ParamConverter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\ParamConverter\IODeviceChannelParamConverter;
use SuplaBundle\Repository\LocationRepository;
use SuplaBundle\Repository\UserIconRepository;

class IODeviceChannelParamConverterTest extends \PHPUnit_Framework_TestCase {
    /** @var IODeviceChannelParamConverter */
    private $converter;

    /** @before */
    public function init() {
        $this->converter = new IODeviceChannelParamConverter(
            $this->createMock(LocationRepository::class),
            $this->createMock(UserIconRepository::class)
        );
    }

    public function testFromEmptyArray() {
        $channel = $this->converter->convert([]);
        $this->assertInstanceOf(IODeviceChannel::class, $channel);
    }

    public function testFunctionFromInt() {
        $channel = $this->converter->convert(['functionId' => ChannelFunction::CONTROLLINGTHEGATEWAYLOCK]);
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), $channel->getFunction());
    }

    public function testInvalidFunction() {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->convert(['functionId' => 123]);
    }

    public function testAltIcon() {
        $channel = $this->converter->convert(['functionId' => ChannelFunction::POWERSWITCH,
            'altIcon' => 1]);
        $this->assertEquals(1, $channel->getAltIcon());
    }

    public function testInvalidAltIcon() {
        $this->expectException(\InvalidArgumentException::class);
        $this->converter->convert(['functionId' => ChannelFunction::POWERSWITCH,
            'altIcon' => 50]);
    }

    public function testOtherParamsInit() {
        $channel = $this->converter->convert(['param1' => 1,
            'param2' => 2,
            'param3' => 3,
            'textParam1' => 'text1',
            'textParam2' => 'text2',
            'textParam3' => 'text3',
            'caption' => 'caption',
            'hidden' => true,
            ]);

        $this->assertEquals(1, $channel->getParam1());
        $this->assertEquals(2, $channel->getParam2());
        $this->assertEquals(3, $channel->getParam3());

        $this->assertEquals('text1', $channel->getTextParam1());
        $this->assertEquals('text2', $channel->getTextParam2());
        $this->assertEquals('text3', $channel->getTextParam3());

        $this->assertEquals('caption', $channel->getCaption());
        $this->assertEquals(true, $channel->getHidden());
    }
}

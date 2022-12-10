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

namespace SuplaBundle\Tests\Integration\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class OnOffChannelStateGetterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var IODevice */
    private $device;
    /** @var ChannelStateGetter */
    private $channelStateGetter;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::POWERSWITCH],
            [ChannelType::RELAY, ChannelFunction::DIMMER],
            [ChannelType::RELAY, ChannelFunction::RGBLIGHTING],
            [ChannelType::RELAY, ChannelFunction::DIMMERANDRGBLIGHTING],
            [ChannelType::RELAY, ChannelFunction::STAIRCASETIMER],
        ]);
        $this->channelStateGetter = self::$container->get(ChannelStateGetter::class);
    }

    public function testGettingOnFromPowerSwitch() {
        SuplaServerMock::mockResponse('GET-RELAY-VALUE', "VALUE:1,0\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $this->assertArrayHasKey('on', $state);
        $this->assertTrue($state['on']);
    }

    public function testGettingOnFromStaircaseTimer() {
        SuplaServerMock::mockResponse('GET-RELAY-VALUE', "VALUE:1,1\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[4]);
        $this->assertArrayHasKey('on', $state);
        $this->assertTrue($state['on']);
    }

    public function testGettingOffFromPowerSwitch() {
        SuplaServerMock::mockResponse('GET-RELAY-VALUE', "VALUE:0,0\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $this->assertArrayHasKey('on', $state);
        $this->assertFalse($state['on']);
    }

    public function testGettingOnFromDimmer() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,0,10\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[1]);
        $this->assertArrayHasKey('on', $state);
        $this->assertTrue($state['on']);
    }

    public function testGettingOffFromDimmer() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,0,0\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[1]);
        $this->assertArrayHasKey('on', $state);
        $this->assertFalse($state['on']);
    }

    public function testGettingOnFromRgb() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,10,0\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[2]);
        $this->assertArrayHasKey('on', $state);
        $this->assertTrue($state['on']);
    }

    public function testGettingOnFromDimmerRgbWhenBothOn() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,10,10\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[3]);
        $this->assertArrayHasKey('on', $state);
        $this->assertTrue($state['on']);
    }

    public function testGettingOnFromDimmerRgbWhenColorOn() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,10,0\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[3]);
        $this->assertArrayHasKey('on', $state);
        $this->assertTrue($state['on']);
    }

    public function testGettingOnFromDimmerRgbWhenDimOn() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,0,10\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[3]);
        $this->assertArrayHasKey('on', $state);
        $this->assertTrue($state['on']);
    }

    public function testGettingOffFromDimmerRgbWhen() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:0,0,0\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[3]);
        $this->assertArrayHasKey('on', $state);
        $this->assertFalse($state['on']);
    }

    public function testGettingCurrentOverloadFalseFromPowerSwitch() {
        SuplaServerMock::mockResponse('GET-RELAY-VALUE', "VALUE:1,0\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $this->assertArrayHasKey('currentOverload', $state);
        $this->assertFalse($state['currentOverload']);
    }

    public function testGettingCurrentOverloadTrueFromPowerSwitch() {
        SuplaServerMock::mockResponse('GET-RELAY-VALUE', "VALUE:1,1\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $this->assertArrayHasKey('currentOverload', $state);
        $this->assertTrue($state['currentOverload']);
    }
}

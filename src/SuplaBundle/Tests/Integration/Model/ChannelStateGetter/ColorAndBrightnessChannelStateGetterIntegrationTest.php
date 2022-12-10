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

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ColorAndBrightnessChannelStateGetterIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var ChannelStateGetter */
    private $channelStateGetter;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::DIMMERANDRGBLIGHTING],
        ]);
        $this->channelStateGetter = self::$container->get(ChannelStateGetter::class);
    }

    public function testGettingRgbValue() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:16711680,80,90\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $this->assertArrayHasKey('color', $state);
        $this->assertArrayHasKey('hue', $state);
        $this->assertArrayHasKey('hsv', $state);
        $this->assertArrayHasKey('rgb', $state);
        $this->assertArrayHasKey('color_brightness', $state);
        $this->assertArrayHasKey('brightness', $state);
        $this->assertEquals('0xFF0000', $state['color']);
        $this->assertEquals(0, $state['hue']);
        $this->assertEquals(['hue' => 0, 'saturation' => 100, 'value' => 80], $state['hsv']);
        $this->assertEquals(['red' => 204, 'green' => 0, 'blue' => 0], $state['rgb']);
        $this->assertEquals(80, $state['color_brightness']);
        $this->assertEquals(90, $state['brightness']);
    }

    public function testGettingRgbValueForAnotherColor() {
        SuplaServerMock::mockResponse('GET-RGBW-VALUE', "VALUE:5635840,80,90\n");
        $state = $this->channelStateGetter->getState($this->device->getChannels()[0]);
        $this->assertEquals('0x55FF00', $state['color']);
        $this->assertEquals(80, $state['color_brightness']);
        $this->assertEquals(100, $state['hue']);
        $this->assertEquals(['hue' => 100, 'saturation' => 100, 'value' => 80], $state['hsv']);
        $this->assertEquals(['red' => 68, 'green' => 204, 'blue' => 0], $state['rgb']);
    }
}

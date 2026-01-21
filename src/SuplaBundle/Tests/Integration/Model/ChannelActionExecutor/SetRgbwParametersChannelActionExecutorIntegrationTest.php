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

namespace SuplaBundle\Tests\Integration\Model\ChannelActionExecutor;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\SuplaAssertions;

/** @small */
class SetRgbwParametersChannelActionExecutorIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use SuplaAssertions;

    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    public function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $this->user = $user;
        $location = $this->createLocation($user);
        $this->device = $this->createDevice($location, [
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::RGBLIGHTING],
        ]);
        $this->channelActionExecutor = self::$container->get(ChannelActionExecutor::class);
        $this->simulateAuthentication($this->user);
    }

    /** @dataProvider colorParamsExamples */
    public function testUpdatingColors(array $params, string $expectedCommand) {
        $this->channelActionExecutor->executeAction($this->device->getChannels()[0], ChannelFunctionAction::SET_RGBW_PARAMETERS(), $params);
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $this->assertSuplaCommandExecuted($expectedCommand);
    }

    public static function colorParamsExamples(): array {
        return [
            [['color' => '0xFF0000', 'color_brightness' => 55], 'SET-RGBW-VALUE:1,1,1,16711680,55,-1,-1'],
            [['color' => '16711680', 'color_brightness' => 55], 'SET-RGBW-VALUE:1,1,1,16711680,55,-1,-1'],
            [['color' => 'random', 'color_brightness' => 55], 'SET-RAND-RGBW-VALUE:1,1,1,55,-1'],
            [['hue' => 0, 'color_brightness' => 55], 'SET-RGBW-VALUE:1,1,1,16711680,55,-1,-1'],
            [['hue' => 'random', 'color_brightness' => 55], 'SET-RAND-RGBW-VALUE:1,1,1,55,-1'],
            [['hsv' => ['hue' => 0, 'saturation' => 100, 'value' => 55]], 'SET-RGBW-VALUE:1,1,1,9175040,55,-1,-1'],
            [['rgb' => ['red' => 140, 'green' => 0, 'blue' => 0]], 'SET-RGBW-VALUE:1,1,1,9175040,55,-1,-1'],
            [['color' => '0xFF0000', 'turnOnOff' => true], 'SET-RGBW-VALUE:1,1,1,16711680,-1,-1,2'],
            [
                ['color' => '0xFF0000', 'turnOnOff' => true, 'alexaCorrelationToken' => 'unicorn'],
                'SET-RGBW-VALUE:1,1,1,16711680,-1,-1,2,ALEXA-CORRELATION-TOKEN=dW5pY29ybg==',
            ],
            [
                ['color' => '0xFF0000', 'turnOnOff' => true, 'googleRequestId' => 'unicorn'],
                'SET-RGBW-VALUE:1,1,1,16711680,-1,-1,2,GOOGLE-REQUEST-ID=dW5pY29ybg==',
            ],
        ];
    }
}

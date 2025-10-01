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

namespace SuplaBundle\Tests\Enums;

use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Utils\ArrayUtils;

class ChannelFunctionTest extends TestCase {
    public function testEveryFunctionHasCaption() {
        $diff = array_map(function (ChannelFunction $type) {
            return $type->getKey();
        }, array_diff(ChannelFunction::values(), array_keys(ChannelFunction::captions())));
        $this->assertEmpty($diff, 'Have you forgotten to add a caption for the new ChannelFunction value? Missing: '
            . implode(', ', $diff));
    }

    public function testEveryFunctionHasStates() {
        $diff = array_map(function (ChannelFunction $type) {
            return $type->getKey();
        }, array_diff(ChannelFunction::values(), array_keys(ChannelFunction::possibleVisualStates())));
        $this->assertEmpty($diff, 'Have you forgotten to add visual states for the new ChannelFunction value? Missing: '
            . implode(', ', $diff));
    }

    public function testFromString() {
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::fromString('CONTROLLINGTHEGATE'));
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::fromString('controllingTheGate'));
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::fromString(ChannelFunction::CONTROLLINGTHEGATE));
    }

    public function testFromStrings() {
        $this->assertEquals(
            [ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::WINDSENSOR()],
            ChannelFunction::fromStrings(['CONTROLLINGTHEGATE', 'windSensor'])
        );
    }

    public function testFromStringDeprecatedNames() {
        $this->assertEquals(ChannelFunction::IC_GASMETER(), ChannelFunction::fromString('GASMETER'));
        $this->assertEquals(ChannelFunction::IC_GASMETER(), ChannelFunction::fromString('IC_GASMETER'));
        $this->assertEquals(ChannelFunction::IC_WATERMETER(), ChannelFunction::fromString('WATERMETER'));
        $this->assertEquals(ChannelFunction::IC_WATERMETER(), ChannelFunction::fromString('IC_WATERMETER'));
        $this->assertEquals(ChannelFunction::ELECTRICITYMETER(), ChannelFunction::fromString('ELECTRICITYMETER'));
        $this->assertEquals(ChannelFunction::IC_ELECTRICITYMETER(), ChannelFunction::fromString('IC_ELECTRICITYMETER'));
    }

    public function testInvalidFromString() {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('UNICORN');
        ChannelFunction::fromString('unicorn');
    }

    public function testInvalidFromValue() {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('123');
        ChannelFunction::fromString(123);
    }

    public function testSupportedFunctionsForNormalChannel() {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getType')->willReturn(ChannelType::THERMOMETER());
        $supportedFunctions = EntityUtils::mapToIds(ChannelFunction::forChannel($channel));
        $this->assertEquals([ChannelFunction::THERMOMETER], $supportedFunctions);
    }

    public function testSupportedFunctionsForRelay() {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getType')->willReturn(ChannelType::RELAY());
        $channel->method('getFuncList')->willReturn(17);
        $supportedFunctions = EntityUtils::mapToIds(ChannelFunction::forChannel($channel));
        $expectedSupportedFunctions = [ChannelFunction::CONTROLLINGTHEGATEWAYLOCK, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER];
        $this->assertEquals($expectedSupportedFunctions, $supportedFunctions);
    }

    public function testSupportedFunctionsForBridge() {
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('getType')->willReturn(ChannelType::BRIDGE());
        $channel->method('getFuncList')->willReturn(17);
        $supportedFunctions = EntityUtils::mapToIds(ChannelFunction::forChannel($channel));
        $expectedSupportedFunctions = [ChannelFunction::CONTROLLINGTHEGATEWAYLOCK, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER];
        $this->assertEquals($expectedSupportedFunctions, $supportedFunctions);
    }

    /**
     * This is dummy test to ensure that frontend filters take every backend function into consideration.
     * @see https://github.com/SUPLA/supla-cloud/issues/480
     */
    public function testEveryFunctionIsMatchedByFrontendFilter() {
        $frontendCode = file_get_contents(__DIR__ . '/../../../frontend/src/channels/channel-filters.vue');
        preg_match_all("#value: '(.+?)'#", $frontendCode, $matches, PREG_SET_ORDER);
        $functionIdsUsedInFrontend = array_column($matches, 1);
        $functionIdsUsedInFrontend = array_map(function ($ids) {
            return explode(',', $ids);
        }, $functionIdsUsedInFrontend);
        $functionIdsUsedInFrontend = ArrayUtils::flattenOnce($functionIdsUsedInFrontend);
        $functionIdsUsedInFrontend = array_map('intval', $functionIdsUsedInFrontend);
        $skip = [ChannelFunction::SCENE, ChannelFunction::SCHEDULE, ChannelFunction::NOTIFICATION];
        foreach (ChannelFunction::values() as $functionName => $channelFunction) {
            $functionId = $channelFunction->getValue();
            if (in_array($functionId, $skip)) {
                continue;
            }
            $this->assertContains($functionId, $functionIdsUsedInFrontend, "$functionName ($functionId) is not used in frontend.");
        }
    }

    public function testNoneAndUnsupportedFunctionsAreNeitherInputNorOutput() {
        $this->assertNotContains(ChannelFunction::NONE, ChannelFunction::inputFunctions());
        $this->assertNotContains(ChannelFunction::NONE, ChannelFunction::outputFunctions());
        $this->assertNotContains(ChannelFunction::UNSUPPORTED, ChannelFunction::inputFunctions());
        $this->assertNotContains(ChannelFunction::UNSUPPORTED, ChannelFunction::outputFunctions());
    }
}

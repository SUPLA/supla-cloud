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
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class ChannelFunctionActionTest extends TestCase {
    public function testEveryValueHasCaption() {
        $diff = array_map(function (ChannelFunctionAction $type) {
            return $type->getKey();
        }, array_diff(ChannelFunctionAction::values(), array_keys(ChannelFunctionAction::captions())));
        $this->assertEmpty($diff, 'Have you forgotten to add a caption for the new ChannelFunction value? Missing: '
            . implode(', ', $diff));
    }

    public function testCreatingFromString() {
        $this->assertEquals(ChannelFunctionAction::REVEAL(), ChannelFunctionAction::fromString('reveal'));
        $this->assertEquals(ChannelFunctionAction::OPEN_CLOSE(), ChannelFunctionAction::fromString('OPEN_CLOSE'));
        $this->assertEquals(ChannelFunctionAction::OPEN_CLOSE(), ChannelFunctionAction::fromString('open_close'));
        $this->assertEquals(ChannelFunctionAction::OPEN_CLOSE(), ChannelFunctionAction::fromString('open-close'));
    }

    public function testCanSetCustomCaption() {
        $this->assertEquals('Expand', ChannelFunctionAction::SHUT()->withFunctionCaption(ChannelFunction::PROJECTOR_SCREEN())->getCaption());
    }

    public function testUsesDefaultCaptionIfNoCustomFunctionMapping() {
        $this->assertEquals('Shut', ChannelFunctionAction::SHUT()->withFunctionCaption(ChannelFunction::HUMIDITY())->getCaption());
    }

    public function testGeneratingChannelFunctionActionMarkdownTableForDocs() {
        // https://github.com/SUPLA/supla-cloud/wiki/Channel-Actions
        $this->markTestSkipped('Only for docs');
        $lines = [
            '| Action name | Additional parameters | Example parameters',
            '| ------------- |-------------|---|',
        ];
        /// @codingStandardsIgnoreStart
        $docs = [
            ChannelFunctionAction::SHUT => ['`percentage` an integer from 0 to 100 meaning how much to shut; optional, default 100'],
            ChannelFunctionAction::REVEAL => ['`percentage` an integer from 0 to 100 meaning how much to shut; optional, default 100'],
            ChannelFunctionAction::SET_RGBW_PARAMETERS => [
                'new color for the device in one of the [described formats](https://github.com/SUPLA/supla-cloud/wiki/RGB-Channels-color-formats)',
                '`brightness` an integer from 0 to 100 meaning the brightness of the white controller (or dimmer); optional, default 0',
            ],
            ChannelFunctionAction::COPY => ['`sourceChannelId` - the ID of the channel which state should be copied from'],
        ];
        $examples = [
            ChannelFunctionAction::SHUT => [['percentage' => 40]],
            ChannelFunctionAction::REVEAL => [['percentage' => 60]],
            ChannelFunctionAction::SET_RGBW_PARAMETERS => [
                ['color' => '0x00FF00', 'brightness' => 70],
                ['color' => 362537, 'color_brightness' => 20, 'brightness' => 70],
                ['brightness' => 50],
                ['color' => 'random', 'color_brightness' => 100],
            ],
            ChannelFunctionAction::COPY => [['sourceChannelId' => 123]],
        ];
        // @codingStandardsIgnoreEnd
        $docs[ChannelFunctionAction::SHUT_PARTIALLY] = $docs[ChannelFunctionAction::SHUT];
        $docs[ChannelFunctionAction::REVEAL_PARTIALLY] = $docs[ChannelFunctionAction::REVEAL];
        $examples[ChannelFunctionAction::SHUT_PARTIALLY] = $examples[ChannelFunctionAction::SHUT];
        $examples[ChannelFunctionAction::REVEAL_PARTIALLY] = $examples[ChannelFunctionAction::REVEAL];
        foreach (ChannelFunctionAction::toArray() as $actionName => $actionId) {
            if (in_array($actionId, [ChannelFunctionAction::AT_FORWARD_OUTSIDE, ChannelFunctionAction::AT_DISABLE_LOCAL_FUNCTION])) {
                continue;
            }
            $docsForItem = implode('', array_map(function ($item) {
                return '<li>' . $item;
            }, $docs[$actionId] ?? []));
            $examplesForItem = $examples[$actionId] ?? null
                ? '`' . implode('`<br> `', array_map(function ($item) {
                    return json_encode($item);
                }, $examples[$actionId] ?? [])) . '`'
                : '';
            $lines[] = sprintf('| `%s` (`%d`) | %s | %s |', $actionName, $actionId, $docsForItem, $examplesForItem);
        }

        $lines[] = '';
        $lines[] = '| Channel Function        | Possible actions |';
        $lines[] = '| ------------- |-------------|';
        foreach (ChannelFunction::toArray() as $functionName => $functionId) {
            if ($functionId === ChannelFunction::UNSUPPORTED) {
                continue;
            }
            $actions = ChannelFunction::actions()[$functionId] ?? [];
            $actions = implode(', ', array_map(function (ChannelFunctionAction $action) {
                return sprintf('`%s` (`%d`)', $action->getName(), $action->getId());
            }, $actions));
            $lines[] = sprintf('| `%s` (`%d`) | %s |', $functionName, $functionId, $actions ?: '-');
        }
        echo implode(PHP_EOL, $lines);
    }
}

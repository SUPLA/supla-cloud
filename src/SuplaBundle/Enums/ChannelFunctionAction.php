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

namespace SuplaBundle\Enums;

use Assert\Assertion;
use Cocur\Slugify\Slugify;
use MyCLabs\Enum\Enum;
use OpenApi\Annotations as OA;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @OA\Schema(schema="ChannelFunctionActionEnumNames", type="string", example="OPEN")
 * @OA\Schema(schema="ChannelFunctionActionIds", type="integer", example=10)
 * @OA\Schema(
 *   schema="ChannelFunctionAction", type="object",
 *   @OA\Property(property="id", ref="#/components/schemas/ChannelFunctionActionIds"),
 *   @OA\Property(property="name", ref="#/components/schemas/ChannelFunctionActionEnumNames"),
 *   @OA\Property(property="caption", type="string", example="Open"),
 * )
 *
 * @method static ChannelFunctionAction VOID()
 * @method static ChannelFunctionAction READ()
 * @method static ChannelFunctionAction EXECUTE()
 * @method static ChannelFunctionAction INTERRUPT()
 * @method static ChannelFunctionAction INTERRUPT_AND_EXECUTE()
 * @method static ChannelFunctionAction SET()
 * @method static ChannelFunctionAction OPEN()
 * @method static ChannelFunctionAction CLOSE()
 * @method static ChannelFunctionAction SHUT()
 * @method static ChannelFunctionAction REVEAL()
 * @method static ChannelFunctionAction REVEAL_PARTIALLY()
 * @method static ChannelFunctionAction SHUT_PARTIALLY()
 * @method static ChannelFunctionAction TURN_ON()
 * @method static ChannelFunctionAction TURN_ON_WITH_DURATION()
 * @method static ChannelFunctionAction TURN_OFF()
 * @method static ChannelFunctionAction TURN_OFF_WITH_DURATION()
 * @method static ChannelFunctionAction SET_RGBW_PARAMETERS()
 * @method static ChannelFunctionAction HVAC_SET_PARAMETERS()
 * @method static ChannelFunctionAction HVAC_SWITCH_TO_PROGRAM_MODE()
 * @method static ChannelFunctionAction HVAC_SWITCH_TO_MANUAL_MODE()
 * @method static ChannelFunctionAction HVAC_SET_TEMPERATURES()
 * @method static ChannelFunctionAction HVAC_SET_TEMPERATURE()
 * @method static ChannelFunctionAction OPEN_CLOSE()
 * @method static ChannelFunctionAction STOP()
 * @method static ChannelFunctionAction TOGGLE()
 * @method static ChannelFunctionAction OPEN_PARTIALLY()
 * @method static ChannelFunctionAction CLOSE_PARTIALLY()
 * @method static ChannelFunctionAction UP_OR_STOP(string $customCaption = '')
 * @method static ChannelFunctionAction DOWN_OR_STOP(string $customCaption = '')
 * @method static ChannelFunctionAction STEP_BY_STEP()
 * @method static ChannelFunctionAction COPY()
 * @method static ChannelFunctionAction ENABLE()
 * @method static ChannelFunctionAction DISABLE()
 * @method static ChannelFunctionAction SEND()
 */
final class ChannelFunctionAction extends Enum {
    const READ = 1000;
    const SET = 2000;
    const EXECUTE = 3000;
    const INTERRUPT = 3001;
    const INTERRUPT_AND_EXECUTE = 3002;

    const VOID = -1;

    const OPEN = 10;
    const CLOSE = 20;
    const SHUT = 30;
    const REVEAL = 40;
    const REVEAL_PARTIALLY = 50;
    const SHUT_PARTIALLY = 51;
    const TURN_ON = 60;
    const TURN_ON_WITH_DURATION = 61;
    const TURN_OFF = 70;
    const TURN_OFF_WITH_DURATION = 71;
    const SET_RGBW_PARAMETERS = 80;
    const OPEN_CLOSE = 90;
    const STOP = 100;
    const TOGGLE = 110;
    const OPEN_PARTIALLY = 120;
    const CLOSE_PARTIALLY = 130;
    const UP_OR_STOP = 140;
    const DOWN_OR_STOP = 150;
    const STEP_BY_STEP = 160;

    const HVAC_SET_PARAMETERS = 230;
    const HVAC_SWITCH_TO_PROGRAM_MODE = 231;
    const HVAC_SWITCH_TO_MANUAL_MODE = 232;
    const HVAC_SET_TEMPERATURES = 233;
    const HVAC_SET_TEMPERATURE = 234;

    const COPY = 10100;

    const ENABLE = 200;
    const DISABLE = 210;
    const SEND = 220;

    const AT_FORWARD_OUTSIDE = 10000;
    const AT_DISABLE_LOCAL_FUNCTION = 10200;

    private string $customCaption = '';

    /** @Groups({"basic"}) */
    public function getId(): int {
        return $this->value;
    }

    /** @Groups({"basic"}) */
    public function getName(): string {
        return $this->getKey();
    }

    /** @Groups({"basic"}) */
    public function getNameSlug(): string {
        return (new Slugify())->slugify($this->getKey());
    }

    /** @param $functionId ChannelFunction|int */
    public function withFunctionCaption($functionId): self {
        $customCaptions = [
            self::SHUT => [
                ChannelFunction::PROJECTOR_SCREEN => 'Expand', // i18n
                ChannelFunction::TERRACE_AWNING => 'Expand', // i18n
                ChannelFunction::ROLLER_GARAGE_DOOR => 'Close', // i18n
            ],
            self::REVEAL => [
                ChannelFunction::PROJECTOR_SCREEN => 'Collapse', // i18n
                ChannelFunction::TERRACE_AWNING => 'Collapse', // i18n
                ChannelFunction::ROLLER_GARAGE_DOOR => 'Open', // i18n
            ],
            self::REVEAL_PARTIALLY => [
                ChannelFunction::PROJECTOR_SCREEN => 'Collapse partially', // i18n
                ChannelFunction::TERRACE_AWNING => 'Collapse partially', // i18n
                ChannelFunction::ROLLER_GARAGE_DOOR => 'Open partially', // i18n
            ],
            self::SHUT_PARTIALLY => [
                ChannelFunction::PROJECTOR_SCREEN => 'Expand partially', // i18n
                ChannelFunction::TERRACE_AWNING => 'Expand partially', // i18n
                ChannelFunction::ROLLER_GARAGE_DOOR => 'Close partially', // i18n
            ],
            self::UP_OR_STOP => [
                ChannelFunction::PROJECTOR_SCREEN => 'Collapse or stop', // i18n
                ChannelFunction::TERRACE_AWNING => 'Collapse or stop', // i18n
                ChannelFunction::VERTICAL_BLIND => 'Reveal or stop', // i18n
                ChannelFunction::CONTROLLINGTHEFACADEBLIND => 'Reveal or stop', // i18n
                ChannelFunction::CURTAIN => 'Reveal or stop', // i18n
            ],
            self::DOWN_OR_STOP => [
                ChannelFunction::PROJECTOR_SCREEN => 'Expand or stop', // i18n
                ChannelFunction::TERRACE_AWNING => 'Expand or stop', // i18n
                ChannelFunction::VERTICAL_BLIND => 'Shut or stop', // i18n
                ChannelFunction::CONTROLLINGTHEFACADEBLIND => 'Shut or stop', // i18n
                ChannelFunction::CURTAIN => 'Shut or stop', // i18n
            ],
        ];
        if ($functionId instanceof ChannelFunction) {
            $functionId = $functionId->getId();
        }
        if (isset($customCaptions[$this->value][$functionId])) {
            $this->customCaption = $customCaptions[$this->value][$functionId];
        }
        return $this;
    }

    /** @Groups({"basic"}) */
    public function getCaption(): string {
        return $this->customCaption ?: self::captions()[$this->getValue()];
    }

    public static function captions(): array {
        return [
            self::VOID => 'Noop', // i18n
            self::READ => 'Read', // i18n
            self::SET => 'Adjust parameters', // i18n
            self::OPEN => 'Open', // i18n
            self::CLOSE => 'Close', // i18n
            self::SHUT => 'Shut', // i18n
            self::REVEAL => 'Reveal', // i18n
            self::REVEAL_PARTIALLY => 'Reveal partially', // i18n
            self::SHUT_PARTIALLY => 'Shut partially', // i18n
            self::TURN_ON => 'On', // i18n
            self::TURN_ON_WITH_DURATION => 'On with duration', // i18n
            self::TURN_OFF => 'Off', // i18n
            self::TURN_OFF_WITH_DURATION => 'Off with duration', // i18n
            self::SET_RGBW_PARAMETERS => 'Adjust parameters', // i18n
            self::OPEN_CLOSE => 'Open / close', // i18n
            self::STOP => 'Stop', // i18n
            self::TOGGLE => 'Toggle', // i18n
            self::EXECUTE => 'Execute', // i18n
            self::INTERRUPT => 'Interrupt', // i18n
            self::INTERRUPT_AND_EXECUTE => 'Interrupt and execute', // i18n
            self::OPEN_PARTIALLY => 'Open partially', // i18n
            self::CLOSE_PARTIALLY => 'Close partially', // i18n
            self::UP_OR_STOP => 'Move up or stop', // i18n
            self::DOWN_OR_STOP => 'Move down or stop', // i18n
            self::STEP_BY_STEP => 'Step by step', // i18n
            self::COPY => 'Copy state from other channel', // i18n
            self::ENABLE => 'Enable', // i18n
            self::DISABLE => 'Disable', // i18n
            self::SEND => 'Send notification', // i18n
            self::AT_DISABLE_LOCAL_FUNCTION => 'Disable local function', // i18n
            self::AT_FORWARD_OUTSIDE => 'Publish to integrations', // i18n
            self::HVAC_SET_PARAMETERS => 'Set HVAC parameters', // i18n
            self::HVAC_SWITCH_TO_PROGRAM_MODE => 'Switch to program mode', // i18n
            self::HVAC_SWITCH_TO_MANUAL_MODE => 'Switch to manual mode', // i18n
            self::HVAC_SET_TEMPERATURES => 'Adjust temperatures', // i18n
            self::HVAC_SET_TEMPERATURE => 'Adjust temperature', // i18n
        ];
    }

    public static function fromString(string $action): ChannelFunctionAction {
        if (is_numeric($action)) {
            Assertion::true(self::isValid(intval($action)), 'Invalid action: ' . $action);
            return new self(intval($action));
        } else {
            $action = str_replace('-', '_', strtoupper($action));
            Assertion::true(self::isValidKey($action), 'Invalid action: ' . $action);
            return self::$action();
        }
    }
}

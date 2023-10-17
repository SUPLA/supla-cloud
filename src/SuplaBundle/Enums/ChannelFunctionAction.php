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
 * @OA\Schema(schema="ChannelFunctionActionEnumNames", type="string", example="OPEN", enum={"READ","SET","EXECUTE","INTERRUPT","INTERRUPT_AND_EXECUTE","VOID","OPEN","CLOSE","SHUT","REVEAL","REVEAL_PARTIALLY","SHUT_PARTIALLY","TURN_ON","TURN_OFF","SET_RGBW_PARAMETERS","OPEN_CLOSE","STOP","TOGGLE","OPEN_PARTIALLY","CLOSE_PARTIALLY","UP_OR_STOP","DOWN_OR_STOP","STEP_BY_STEP","HVAC_SET_WEEKLY_SCHEDULE","TURN_OFF_TIMER","HVAC_SWITCH_TO_MANUAL","HVAC_SET_TEMPERATURES","COPY","ENABLE","DISABLE","SEND","AT_FORWARD_OUTSIDE","AT_DISABLE_LOCAL_FUNCTION"})
 * @OA\Schema(schema="ChannelFunctionActionIds", type="integer", example=10, enum={1000,2000,3000,3001,3002,-1,10,20,30,40,50,51,60,70,80,90,100,110,120,130,140,150,160,231,232,233,234,10100,200,210,220,10000,10200})
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
 * @method static ChannelFunctionAction TURN_OFF()
 * @method static ChannelFunctionAction SET_RGBW_PARAMETERS()
 * @method static ChannelFunctionAction HVAC_SET_WEEKLY_SCHEDULE()
 * @method static ChannelFunctionAction TURN_OFF_TIMER()
 * @method static ChannelFunctionAction HVAC_SWITCH_TO_MANUAL()
 * @method static ChannelFunctionAction HVAC_SET_TEMPERATURES()
 * @method static ChannelFunctionAction OPEN_CLOSE()
 * @method static ChannelFunctionAction STOP()
 * @method static ChannelFunctionAction TOGGLE()
 * @method static ChannelFunctionAction OPEN_PARTIALLY()
 * @method static ChannelFunctionAction CLOSE_PARTIALLY()
 * @method static ChannelFunctionAction UP_OR_STOP()
 * @method static ChannelFunctionAction DOWN_OR_STOP()
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
    const TURN_OFF = 70;
    const SET_RGBW_PARAMETERS = 80;
    const OPEN_CLOSE = 90;
    const STOP = 100;
    const TOGGLE = 110;
    const OPEN_PARTIALLY = 120;
    const CLOSE_PARTIALLY = 130;
    const UP_OR_STOP = 140;
    const DOWN_OR_STOP = 150;
    const STEP_BY_STEP = 160;

    const HVAC_SET_WEEKLY_SCHEDULE = 231;
    const TURN_OFF_TIMER = 232;
    const HVAC_SWITCH_TO_MANUAL = 233;
    const HVAC_SET_TEMPERATURES = 234;

    const COPY = 10100;

    const ENABLE = 200;
    const DISABLE = 210;
    const SEND = 220;

    const AT_FORWARD_OUTSIDE = 10000;
    const AT_DISABLE_LOCAL_FUNCTION = 10200;

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

    /** @Groups({"basic"}) */
    public function getCaption(): string {
        return self::captions()[$this->getValue()];
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
            self::TURN_OFF => 'Off', // i18n
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
            self::HVAC_SET_WEEKLY_SCHEDULE => 'Switch to weekly schedule', // i18n
            self::HVAC_SWITCH_TO_MANUAL => 'Switch to manual mode', // i18n
            self::TURN_OFF_TIMER => 'Off with a timer', // i18n
            self::HVAC_SET_TEMPERATURES => 'Adjust temperature', // i18n
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

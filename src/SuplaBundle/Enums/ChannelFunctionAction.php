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
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @method static ChannelFunctionAction READ()
 * @method static ChannelFunctionAction OPEN()
 * @method static ChannelFunctionAction CLOSE()
 * @method static ChannelFunctionAction SHUT()
 * @method static ChannelFunctionAction REVEAL()
 * @method static ChannelFunctionAction REVEAL_PARTIALLY()
 * @method static ChannelFunctionAction TURN_ON()
 * @method static ChannelFunctionAction TURN_OFF()
 * @method static ChannelFunctionAction SET_RGBW_PARAMETERS()
 * @method static ChannelFunctionAction OPEN_CLOSE()
 * @method static ChannelFunctionAction STOP()
 * @method static ChannelFunctionAction TOGGLE()
 */
final class ChannelFunctionAction extends Enum {
    const READ = 1000;
    const OPEN = 10;
    const CLOSE = 20;
    const SHUT = 30;
    const REVEAL = 40;
    const REVEAL_PARTIALLY = 50;
    const TURN_ON = 60;
    const TURN_OFF = 70;
    const SET_RGBW_PARAMETERS = 80;
    const OPEN_CLOSE = 90;
    const STOP = 100;
    const TOGGLE = 110;

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
            self::READ => 'Read', // i18n
            self::OPEN => 'Open', // i18n
            self::CLOSE => 'Close', // i18n
            self::SHUT => 'Shut', // i18n
            self::REVEAL => 'Reveal', // i18n
            self::REVEAL_PARTIALLY => 'Reveal partially', // i18n
            self::TURN_ON => 'On', // i18n
            self::TURN_OFF => 'Off', // i18n
            self::SET_RGBW_PARAMETERS => 'Adjust parameters', // i18n
            self::OPEN_CLOSE => 'Open / close', // i18n
            self::STOP => 'Stop', // i18n
            self::TOGGLE => 'Toggle', // i18n
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

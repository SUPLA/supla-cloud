<?php

namespace SuplaBundle\Enums;

use Cocur\Slugify\Slugify;
use ValueError;

/**
 * @see https://github.com/SUPLA/supla-core/issues/280#issuecomment-1761103658
 */
enum RgbwCommand: int {
    case NOT_SET = 0;
    case TURN_ON_DIMMER = 1;
    case TURN_OFF_DIMMER = 2;
    case TOGGLE_DIMMER = 3;
    case TURN_ON_RGB = 4;
    case TURN_OFF_RGB = 5;
    case TOGGLE_RGB = 6;
    case TURN_ON_ALL = 7;
    case TURN_OFF_ALL = 8;
    case TOGGLE_ALL = 9;
    case SET_BRIGHTNESS_WITHOUT_TURN_ON = 10;
    case SET_COLOR_BRIGHTNESS_WITHOUT_TURN_ON = 11;
    case SET_RGB_WITHOUT_TURN_ON = 12;
    case BRIGHTNESS_ADJUSTMENT_DIMMER_START = 13;
    case BRIGHTNESS_ADJUSTMENT_COLOR_START = 14;
    case BRIGHTNESS_ADJUSTMENT_ALL_START = 15;
    case BRIGHTNESS_ADJUSTMENT_DIMMER_STOP = 16;
    case BRIGHTNESS_ADJUSTMENT_COLOR_STOP = 17;
    case BRIGHTNESS_ADJUSTMENT_ALL_STOP = 18;
    case SET_DIMMER_CCT_WITHOUT_TURN_ON = 19;

    public static function tryFromName(string $name): ?self {
        $slugify = new Slugify(['separator' => '_', 'regexp' => "/([^A-Z0-9])+/i"]);
        $commandName = strtoupper($slugify->slugify($name));
        return array_column(self::cases(), null, 'name')[$commandName] ?? null;
    }

    public static function fromName(string $name): self {
        return self::tryFromName($name) ?? throw new ValueError(sprintf('%s is not a valid case for enum %s', $name, self::class));
    }

    public static function forFunction(int $functionId): array {
        return match ($functionId) {
            ChannelFunction::RGBLIGHTING => [
                self::NOT_SET,
                self::TURN_ON_RGB,
                self::TURN_OFF_RGB,
                self::TOGGLE_RGB,
                self::SET_COLOR_BRIGHTNESS_WITHOUT_TURN_ON,
                self::SET_RGB_WITHOUT_TURN_ON,
                self::BRIGHTNESS_ADJUSTMENT_COLOR_START,
                self::BRIGHTNESS_ADJUSTMENT_COLOR_STOP,
            ],
            ChannelFunction::DIMMER => [
                self::NOT_SET,
                self::TURN_ON_DIMMER,
                self::TURN_OFF_DIMMER,
                self::TOGGLE_DIMMER,
                self::SET_BRIGHTNESS_WITHOUT_TURN_ON,
                self::BRIGHTNESS_ADJUSTMENT_DIMMER_START,
                self::BRIGHTNESS_ADJUSTMENT_DIMMER_STOP,
            ],
            ChannelFunction::DIMMERANDRGBLIGHTING => [
                self::NOT_SET,
                self::TURN_ON_DIMMER,
                self::TURN_OFF_DIMMER,
                self::TOGGLE_DIMMER,
                self::SET_BRIGHTNESS_WITHOUT_TURN_ON,
                self::BRIGHTNESS_ADJUSTMENT_DIMMER_START,
                self::BRIGHTNESS_ADJUSTMENT_DIMMER_STOP,
                self::TURN_ON_RGB,
                self::TURN_OFF_RGB,
                self::TOGGLE_RGB,
                self::SET_COLOR_BRIGHTNESS_WITHOUT_TURN_ON,
                self::SET_RGB_WITHOUT_TURN_ON,
                self::BRIGHTNESS_ADJUSTMENT_COLOR_START,
                self::BRIGHTNESS_ADJUSTMENT_COLOR_STOP,
                self::TURN_ON_ALL,
                self::TURN_OFF_ALL,
                self::TOGGLE_ALL,
                self::BRIGHTNESS_ADJUSTMENT_ALL_START,
                self::BRIGHTNESS_ADJUSTMENT_ALL_STOP,
            ],
            ChannelFunction::DIMMER_CCT => [
                self::NOT_SET,
                self::TURN_ON_DIMMER,
                self::TURN_OFF_DIMMER,
                self::TOGGLE_DIMMER,
                self::SET_BRIGHTNESS_WITHOUT_TURN_ON,
                self::BRIGHTNESS_ADJUSTMENT_DIMMER_START,
                self::BRIGHTNESS_ADJUSTMENT_DIMMER_STOP,
                self::SET_DIMMER_CCT_WITHOUT_TURN_ON,
            ],
            ChannelFunction::DIMMER_CCT_AND_RGB => [
                self::NOT_SET,
                self::TURN_ON_DIMMER,
                self::TURN_OFF_DIMMER,
                self::TOGGLE_DIMMER,
                self::SET_BRIGHTNESS_WITHOUT_TURN_ON,
                self::BRIGHTNESS_ADJUSTMENT_DIMMER_START,
                self::BRIGHTNESS_ADJUSTMENT_DIMMER_STOP,
                self::SET_DIMMER_CCT_WITHOUT_TURN_ON,
                self::TURN_ON_RGB,
                self::TURN_OFF_RGB,
                self::TOGGLE_RGB,
                self::SET_COLOR_BRIGHTNESS_WITHOUT_TURN_ON,
                self::SET_RGB_WITHOUT_TURN_ON,
                self::BRIGHTNESS_ADJUSTMENT_COLOR_START,
                self::BRIGHTNESS_ADJUSTMENT_COLOR_STOP,
                self::TURN_ON_ALL,
                self::TURN_OFF_ALL,
                self::TOGGLE_ALL,
                self::BRIGHTNESS_ADJUSTMENT_ALL_START,
                self::BRIGHTNESS_ADJUSTMENT_ALL_STOP,
            ],
            default => [],
        };
    }
}

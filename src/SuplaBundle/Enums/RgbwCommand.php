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
    case START_ITERATE_DIMMER = 13;
    case START_ITERATE_RGB = 14;
    case START_ITERATE_ALL = 15;
    case STOP_ITERATE_DIMMER = 16;
    case STOP_ITERATE_RGB = 17;
    case STOP_ITERATE_ALL = 18;
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
                self::START_ITERATE_RGB,
                self::STOP_ITERATE_RGB,
            ],
            ChannelFunction::DIMMER => [
                self::NOT_SET,
                self::TURN_ON_DIMMER,
                self::TURN_OFF_DIMMER,
                self::TOGGLE_DIMMER,
                self::SET_BRIGHTNESS_WITHOUT_TURN_ON,
                self::START_ITERATE_DIMMER,
                self::STOP_ITERATE_DIMMER,
            ],
            ChannelFunction::DIMMERANDRGBLIGHTING => [
                self::NOT_SET,
                self::TURN_ON_DIMMER,
                self::TURN_OFF_DIMMER,
                self::TOGGLE_DIMMER,
                self::SET_BRIGHTNESS_WITHOUT_TURN_ON,
                self::START_ITERATE_DIMMER,
                self::STOP_ITERATE_DIMMER,
                self::TURN_ON_RGB,
                self::TURN_OFF_RGB,
                self::TOGGLE_RGB,
                self::SET_COLOR_BRIGHTNESS_WITHOUT_TURN_ON,
                self::SET_RGB_WITHOUT_TURN_ON,
                self::START_ITERATE_RGB,
                self::STOP_ITERATE_RGB,
                self::TURN_ON_ALL,
                self::TURN_OFF_ALL,
                self::TOGGLE_ALL,
                self::START_ITERATE_ALL,
                self::STOP_ITERATE_ALL,
            ],
            ChannelFunction::DIMMER_CCT => [
                self::NOT_SET,
                self::TURN_ON_DIMMER,
                self::TURN_OFF_DIMMER,
                self::TOGGLE_DIMMER,
                self::SET_BRIGHTNESS_WITHOUT_TURN_ON,
                self::START_ITERATE_DIMMER,
                self::STOP_ITERATE_DIMMER,
                self::SET_DIMMER_CCT_WITHOUT_TURN_ON,
            ],
            ChannelFunction::DIMMER_CCT_AND_RGB => [
                self::NOT_SET,
                self::TURN_ON_DIMMER,
                self::TURN_OFF_DIMMER,
                self::TOGGLE_DIMMER,
                self::SET_BRIGHTNESS_WITHOUT_TURN_ON,
                self::START_ITERATE_DIMMER,
                self::STOP_ITERATE_DIMMER,
                self::SET_DIMMER_CCT_WITHOUT_TURN_ON,
                self::TURN_ON_RGB,
                self::TURN_OFF_RGB,
                self::TOGGLE_RGB,
                self::SET_COLOR_BRIGHTNESS_WITHOUT_TURN_ON,
                self::SET_RGB_WITHOUT_TURN_ON,
                self::START_ITERATE_RGB,
                self::STOP_ITERATE_RGB,
                self::TURN_ON_ALL,
                self::TURN_OFF_ALL,
                self::TOGGLE_ALL,
                self::START_ITERATE_ALL,
                self::STOP_ITERATE_ALL,
            ],
            default => [],
        };
    }
}

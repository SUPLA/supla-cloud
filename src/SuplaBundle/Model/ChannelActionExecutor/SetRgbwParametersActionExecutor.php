<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\RgbwCommand;
use SuplaBundle\Utils\ColorUtils;

/**
 * @OA\Schema(schema="ChannelActionParamsDimmer",
 *   description="Action params for `SET_RGBW_PARAMETERS` action on `DIMMER`.",
 *   @OA\Property(property="brightness", type="integer", minimum=0, maximum=100),
 * )
 * @OA\Schema(schema="ChannelActionParamsRgbw",
 *   description="Action params for `SET_RGBW_PARAMETERS` action.",
 *   oneOf={
 *     @OA\Schema(
 *       @OA\Property(property="color", oneOf={
 *         @OA\Schema(type="integer"),
 *         @OA\Schema(type="string", pattern="^0x[0-9A-F]{6}$", description="Hex color value"),
 *         @OA\Schema(type="string", enum={"white", "random"}),
 *       }),
 *       @OA\Property(property="color_brightness", type="integer", minimum=0, maximum=100, deprecated=true),
 *     ),
 *     @OA\Schema(@OA\Property(property="hue", type="integer", minimum=0, maximum=359), @OA\Property(property="color_brightness", type="integer", minimum=0, maximum=100, deprecated=true)),
 *     @OA\Schema(@OA\Property(property="hsv", @OA\Property(property="hue", type="integer", minimum=0, maximum=359), @OA\Property(property="saturation", type="integer", minimum=0, maximum=100), @OA\Property(property="value", type="integer", minimum=0, maximum=100))),
 *     @OA\Schema(@OA\Property(property="rgb", @OA\Property(property="red", type="integer", minimum=0, maximum=255), @OA\Property(property="green", type="integer", minimum=0, maximum=255), @OA\Property(property="blue", type="integer", minimum=0, maximum=255))),
 *   }
 * )
 */
class SetRgbwParametersActionExecutor extends SingleChannelActionExecutor {
    const POSSIBLE_ACTION_KEYS = [
        'rgbw_command',
        'hue',
        'color_brightness',
        'brightness',
        'white_temperature',
        'color',
        'hsv',
        'rgb',
        'turnOnOff',
    ];

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::DIMMER(),
            ChannelFunction::RGBLIGHTING(),
            ChannelFunction::DIMMERANDRGBLIGHTING(),
            ChannelFunction::DIMMER_CCT(),
            ChannelFunction::DIMMER_CCT_AND_RGB(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SET_RGBW_PARAMETERS();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::between(count($actionParams), 1, 6, 'You need to specify at least brightness or color for this action.');
        Assertion::count(
            array_intersect_key(
                $actionParams,
                array_flip(self::POSSIBLE_ACTION_KEYS)
            ),
            count($actionParams),
            'Invalid action parameters'
        );
        $possibleKeyCombinations = [
            ['color', 'color_brightness'],
            ['hue', 'color_brightness'],
            ['color'],
            ['hsv'],
            ['rgb'],
        ];
        $possibleColorSettings = array_unique(call_user_func_array('array_merge', $possibleKeyCombinations));
        foreach ($possibleKeyCombinations as $possibleKeyCombination) {
            if (isset($actionParams[$possibleKeyCombination[0]])) {
                foreach ($possibleColorSettings as $possibleColorSetting) {
                    if (isset($actionParams[$possibleColorSetting])) {
                        Assertion::inArray(
                            $possibleColorSetting,
                            $possibleKeyCombination,
                            sprintf(
                                'You cant set %s when you use new color format with %s parameter.',
                                $possibleColorSetting,
                                $possibleKeyCombination[0]
                            )
                        );
                        break;
                    }
                }
            }
        }
        $params = [];
        if (isset($actionParams['hue']) || isset($actionParams['color'])) {
            if (isset($actionParams['color'])) {
                $color = $actionParams['color'];
                if (is_string($color) && preg_match('/^(0x|#)([0-9A-F]+)$/i', $color, $colorMatch)) {
                    $color = hexdec($colorMatch[2]);
                }
                if (is_numeric($color)) {
                    Assertion::between($color, 1, 0xFFFFFF);
                    $params['color'] = ColorUtils::decToHex(intval($color), '#');
                } else {
                    Assertion::inArray($color, ['random'], 'Invalid color definition "%s".');
                    $params['color'] = 'random';
                }
            } else {
                Assertion::true(is_numeric($actionParams['hue']) || in_array($actionParams['hue'], ['random', 'white']));
                if (is_numeric($actionParams['hue'])) {
                    Assertion::between($actionParams['hue'], 0, 359);
                    $actionParams['hue'] = intval($actionParams['hue']);
                    $color = ColorUtils::hueToDec($actionParams['hue']);
                    $params['color'] = ColorUtils::decToHex($color, '#');
                } elseif ($actionParams['hue'] === 'white') {
                    $params['color'] = '#FFFFFF';
                } else {
                    $params['color'] = 'random';
                }
            }
        }
        if (isset($actionParams['color_brightness'])) {
            Assert::that($actionParams['color_brightness'])->numeric()->between(0, 100);
            $params['color_brightness'] = intval($actionParams['color_brightness']);
        }
        if (isset($actionParams['brightness'])) {
            Assert::that($actionParams['brightness'])->numeric()->between(0, 100);
            $params['brightness'] = intval($actionParams['brightness']);
        }
        if (isset($actionParams['white_temperature'])) {
            Assert::that($actionParams['white_temperature'])->numeric()->between(0, 100);
            $params['white_temperature'] = intval($actionParams['white_temperature']);
        }
        if (isset($actionParams['hsv'])) {
            $hsv = $actionParams['hsv'];
            Assert::that($hsv)->isArray()->keyIsset('hue')->keyIsset('saturation')->keyIsset('value');
            Assertion::between($hsv['hue'], 0, 359);
            Assertion::between($hsv['saturation'], 0, 100);
            Assertion::between($hsv['value'], 0, 100);
            $color = ColorUtils::hsvToDec([$hsv['hue'], $hsv['saturation'], $hsv['value']]);
            $params['color'] = ColorUtils::decToHex($color, '#');
            $params['color_brightness'] = $hsv['value'];
        }
        if (isset($actionParams['rgb'])) {
            $rgb = $actionParams['rgb'];
            Assert::that($rgb)->isArray()->keyIsset('red')->keyIsset('green')->keyIsset('blue')->all()->between(0, 255);
            $color = ColorUtils::rgbToDec([$rgb['red'], $rgb['green'], $rgb['blue']]);
            $params['color'] = ColorUtils::decToHex($color, '#');
            $params['color_brightness'] = ColorUtils::decToHsv($color)[2] ?? 0;
        }
        if (isset($actionParams['turnOnOff'])) {
            $params['turnOnOff'] = $this->chooseTurnOnOffBit($subject, $actionParams['turnOnOff']);
        }
        if (isset($actionParams['rgbw_command'])) {
            $command = RgbwCommand::tryFromName($actionParams['rgbw_command']);
            $possibleCommands = RgbwCommand::forFunction($subject->getFunction()->getId());
            Assert::that($command)
                ->notNull('Invalid rgbw_command "%s".', $actionParams['rgbw_command'])
                ->inArray($possibleCommands, 'Unsupported rgbw_command "%s".', $actionParams['rgbw_command']);
            $params['rgbw_command'] = $command->value;
        }
        return $params;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $color = -1;
        if (isset($actionParams['color'])) {
            $color = $actionParams['color'] === 'random' ? 'random' : ColorUtils::hexToDec($actionParams['color']);
        }
        $colorBrightness = $actionParams['color_brightness'] ?? -1;
        $brightness = $actionParams['brightness'] ?? -1;
        $turnOnOff = $actionParams['turnOnOff'] ?? -1;
        $rgbwCommand = RgbwCommand::from($actionParams['rgbw_command'] ?? RgbwCommand::NOT_SET->value);
        $whiteTemperature = $actionParams['white_temperature'] ?? -1;
        $cmdParams = [$color, $colorBrightness, $brightness, $turnOnOff, $rgbwCommand->value, $whiteTemperature];
        $command = $subject->buildServerActionCommand('SET-RGBW-VALUE', $cmdParams);
        if ($color == 'random') {
            array_shift($cmdParams);
            $command = $subject->buildServerActionCommand('SET-RAND-RGBW-VALUE', $cmdParams);
        }
        $this->suplaServer->executeCommand($command);
    }

    private function chooseTurnOnOffBit(ActionableSubject $subject, $turnOnOff): int {
        if (!$turnOnOff) {
            return 0;
        }
        switch ($subject->getFunction()->getId()) {
            case ChannelFunction::RGBLIGHTING:
                return 0x2;
            case ChannelFunction::DIMMER:
                return 0x1;
            case ChannelFunction::DIMMERANDRGBLIGHTING:
                if ($turnOnOff === true) {
                    $turnOnOff = 0x1 | 0x2;
                }
                return ($turnOnOff & 0x1) | ($turnOnOff & 0x2);
            default:
                return 0;
        }
    }

    public function transformActionParamsForApi(ActionableSubject $subject, array $actionParams): array {
        if (isset($actionParams['hue'])) {
            $color = ColorUtils::hueToDec($actionParams['hue']);
            $actionParams['color'] = ColorUtils::decToHex($color, '#');
            unset($actionParams['hue']);
        }
        if (isset($actionParams['rgbw_command'])) {
            $actionParams['rgbw_command'] = RgbwCommand::from($actionParams['rgbw_command'])->name;
        }
        return $actionParams;
    }
}

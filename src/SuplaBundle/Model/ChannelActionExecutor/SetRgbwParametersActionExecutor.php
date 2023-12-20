<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelStateGetter\ColorAndBrightnessChannelStateGetter;
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
        'hue',
        'color_brightness',
        'brightness',
        'color',
        'hsv',
        'rgb',
        'turnOnOff',
    ];

    /** @var ColorAndBrightnessChannelStateGetter */
    private $channelStateGetter;

    public function __construct(ColorAndBrightnessChannelStateGetter $channelStateGetter) {
        $this->channelStateGetter = $channelStateGetter;
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::DIMMER(),
            ChannelFunction::RGBLIGHTING(),
            ChannelFunction::DIMMERANDRGBLIGHTING(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SET_RGBW_PARAMETERS();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::between(count($actionParams), 1, 4, 'You need to specify at least brightness or color for this action.');
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
        if (isset($actionParams['hue']) || isset($actionParams['color'])) {
            if (isset($actionParams['hue'])) { // hue is supported in schedules only
                Assertion::true(is_numeric($actionParams['hue']) || in_array($actionParams['hue'], ['random', 'white']));
                if (is_numeric($actionParams['hue'])) {
                    Assertion::between($actionParams['hue'], 0, 359);
                    $actionParams['hue'] = intval($actionParams['hue']);
                }
            } else {
                $color = $actionParams['color'] ?? 1;
                if (is_string($color) && preg_match('#^0x[0-9A-F]+$#i', $color)) {
                    $color = hexdec($color);
                }
                if (is_numeric($color)) {
                    Assertion::between($color, 1, 0xFFFFFF);
                    $actionParams['color'] = intval($color);
                    if (!isset($actionParams['color_brightness'])) {
                        [, , $v] = ColorUtils::decToHsv($color);
                        $actionParams['color_brightness'] = $v;
                    }
                } else {
                    Assertion::inArray($color, ['random'], 'Invalid color definition "%s".');
                }
            }
            $colorBrightness = $actionParams['color_brightness'] ?? 100;
            Assert::that($colorBrightness)->numeric()->between(0, 100);
            $actionParams['color_brightness'] = intval($colorBrightness);
        }
        if (isset($actionParams['brightness'])) {
            Assert::that($actionParams['brightness'])->numeric()->between(0, 100);
            $actionParams['brightness'] = intval($actionParams['brightness']);
        }
        if (isset($actionParams['hsv'])) {
            $hsv = $actionParams['hsv'];
            Assert::that($hsv)->isArray()->keyIsset('hue')->keyIsset('saturation')->keyIsset('value');
            $actionParams['hsv'] = [
                'hue' => intval($hsv['hue']),
                'saturation' => intval($hsv['saturation']),
                'value' => intval($hsv['value']),
            ];
            Assertion::between($actionParams['hsv']['hue'], 0, 359);
            Assertion::between($actionParams['hsv']['saturation'], 0, 100);
            Assertion::between($actionParams['hsv']['value'], 0, 100);
        }
        if (isset($actionParams['rgb'])) {
            $rgb = $actionParams['rgb'];
            Assert::that($rgb)->isArray()->keyIsset('red')->keyIsset('green')->keyIsset('blue');
            $actionParams['rgb'] = [
                'red' => intval($rgb['red']),
                'green' => intval($rgb['green']),
                'blue' => intval($rgb['blue']),
            ];
            Assertion::between($actionParams['rgb']['red'], 0, 255);
            Assertion::between($actionParams['rgb']['green'], 0, 255);
            Assertion::between($actionParams['rgb']['blue'], 0, 255);
        }
        return $actionParams;
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        if (isset($actionParams['color'])) {
            $color = $actionParams['color'];
            if (stripos($color, '0x') === 0) {
                $color = ColorUtils::hexToDec($color);
            }
        } elseif (isset($actionParams['hue'])) {
            $color = $actionParams['hue'];
            if ($color !== 'random') {
                if ($color === 'white') {
                    $color = 0xFFFFFF;
                } else {
                    $color = ColorUtils::hueToDec($actionParams['hue']);
                }
            }
        } elseif (isset($actionParams['rgb'])) {
            $rgb = $actionParams['rgb'];
            $color = ColorUtils::rgbToDec([$rgb['red'], $rgb['green'], $rgb['blue']]);
            [$h, $s, $v] = ColorUtils::decToHsv($color);
            $actionParams['hsv'] = ['hue' => $h, 'saturation' => $s, 'value' => $v];
        }
        if (isset($actionParams['hsv'])) {
            $hsv = $actionParams['hsv'];
            $color = ColorUtils::hsvToDec([$hsv['hue'], $hsv['saturation'], 100]);
            $actionParams['color_brightness'] = $hsv['value'];
        }
        $channel = $subject instanceof IODeviceChannel ? $subject : $subject->getChannels()[0];
        $currentState = $this->channelStateGetter->getState($channel);
        $actionParams = array_merge($currentState, $actionParams);
        if (!isset($color)) {
            $color = ColorUtils::hexToDec($currentState['color'] ?? '0x1');
        }
        $colorBrightness = $actionParams['color_brightness'] ?? 0;
        $brightness = $actionParams['brightness'] ?? 0;
        $turnOnOff = $this->chooseTurnOnOffBit($subject, $actionParams['turnOnOff'] ?? false);
        $command = $subject->buildServerActionCommand('SET-RGBW-VALUE', [$color, $colorBrightness, $brightness, $turnOnOff]);
        if ($color == 'random') {
            $command = $subject->buildServerActionCommand('SET-RAND-RGBW-VALUE', [$colorBrightness, $brightness]);
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
}

<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelStateGetter\ColorAndBrightnessChannelStateGetter;
use SuplaBundle\Utils\ColorUtils;

class SetRgbwParametersActionExecutor extends SingleChannelActionExecutor {
    const POSSIBLE_ACTION_KEYS = [
        'hue',
        'color_brightness',
        'brightness',
        'color',
        'hsv',
        'rgb',
        'alexaCorrelationToken',
        'googleRequestId',
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

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        Assertion::between(count($actionParams), 1, 4, 'Invalid number of action parameters');
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
                Assertion::true(is_numeric($color) || in_array($color, ['random']) || strpos($color, '0x') === 0);
                if (strpos($color, '0x') === 0) {
                    $color = hexdec($color);
                }
                if (is_numeric($color)) {
                    Assertion::between($color, 1, 0xFFFFFF);
                    $actionParams['color'] = intval($color);
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

    public function execute(HasFunction $subject, array $actionParams = []) {
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
            list($h, $s, $v) = ColorUtils::decToHsv($color);
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
        $turnOnOff = ($actionParams['turnOnOff']) ?? false ? 1 : 0;
        $command = $subject->buildServerSetCommand(
            'RGBW',
            $this->assignCommonParams([$color, $colorBrightness, $brightness, $turnOnOff], $actionParams)
        );
        if ($color == 'random') {
            $command = $subject->buildServerSetCommand('RAND-RGBW', [$colorBrightness, $brightness]);
        }
        $this->suplaServer->executeSetCommand($command);
    }
}

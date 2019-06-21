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
            array_intersect_key($actionParams, array_flip(['hue', 'color_brightness', 'brightness', 'color', 'hsv',
                'alexaCorrelationToken', 'googleRequestId'])),
            count($actionParams),
            'Invalid action parameters'
        );
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
            Assert::that($hsv)->isArray()->notEmptyKey('hue')->notEmptyKey('saturation')->notEmptyKey('value');
            $actionParams['hsv'] = [
                'hue' => intval($hsv['hue']),
                'saturation' => intval($hsv['saturation']),
                'value' => intval($hsv['value']),
            ];
            Assertion::between($actionParams['hsv']['hue'], 0, 359);
            Assertion::between($actionParams['hsv']['saturation'], 0, 100);
            Assertion::between($actionParams['hsv']['value'], 0, 100);
        }
        return $actionParams;
    }

    public function execute(HasFunction $subject, array $actionParams = []) {
        if (isset($actionParams['color'])) {
            $color = $actionParams['color'];
            if (strpos($color, '0x') === 0) {
                $color = ColorUtils::hexToDec($color);
            }
            if (!isset($actionParams['color_brightness'])) {
                $actionParams['color_brightness'] = ColorUtils::decToHsv($color)[2];
            }
        } elseif (isset($actionParams['hue'])) {
            $color = ColorUtils::hueToDec($actionParams['hue']);
        } elseif (isset($actionParams['hsv'])) {
            $hsv = $actionParams['hsv'];
            $color = ColorUtils::hsvToDec([$hsv['hue'], $hsv['saturation'], 100]);
            if (!isset($actionParams['color_brightness'])) {
                $actionParams['color_brightness'] = $hsv['value'];
            }
        }
        $channel = $subject instanceof IODeviceChannel ? $subject : $subject->getChannels()[0];
        $currentState = $this->channelStateGetter->getState($channel);
        $actionParams = array_merge($currentState, $actionParams);
        if (!isset($color)) {
            $color = $currentState['color'] ?? 1;
        }
        $colorBrightness = $actionParams['color_brightness'] ?? 0;
        $brightness = $actionParams['brightness'] ?? 0;
        $command = $subject->buildServerSetCommand(
            'RGBW',
            $this->assignCommonParams([$color, $colorBrightness, $brightness], $actionParams)
        );
        if ($color == 'random') {
            $command = $subject->buildServerSetCommand('RAND-RGBW', [$colorBrightness, $brightness]);
        }
        $this->suplaServer->executeSetCommand($command);
    }
}

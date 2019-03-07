<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class SetRgbwParametersActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::DIMMER(),
            ChannelFunction::VLDIMMER(),
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
            array_intersect_key($actionParams, array_flip(['hue', 'color_brightness', 'brightness', 'color',
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
        return $actionParams;
    }

    public function execute(HasFunction $subject, array $actionParams = []) {
        $color = $actionParams['color'] ?? 1;
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

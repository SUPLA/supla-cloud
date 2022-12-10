<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\ColorUtils;

/**
 * @OA\Schema(schema="ChannelStateBrightness",
 *     description="State of `DIMMER`",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="brightness", type="integer", minimum=0, maximum=100, description="current dimmer brightness value in percent"),
 *     @OA\Property(property="on", type="boolean"),
 * )
 * @OA\Schema(schema="ChannelStateColor",
 *     description="State of `RGBLIGHTING`",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="color", type="string", description="integer (hex) value of a current color, ranging from `0x000001` to `0xFFFFFF`"),
 *     @OA\Property(property="color_brightness", type="integer", minimum=0, maximum=100, description="color brightness in percent"),
 *     @OA\Property(property="on", type="boolean"),
 * )
 * @OA\Schema(schema="ChannelStateColorAndBrightness",
 *     description="State of `DIMMERANDRGBLIGHTING`",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="color", type="string", description="integer (hex) value of a current color, ranging from `0x000001` to `0xFFFFFF`"),
 *     @OA\Property(property="color_brightness", type="integer", minimum=0, maximum=100, description="color brightness in percent"),
 *     @OA\Property(property="brightness", type="integer", minimum=0, maximum=100, description="current dimmer brightness value in percent"),
 *     @OA\Property(property="on", type="boolean"),
 * )
 */
class ColorAndBrightnessChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getRgbwValue($channel);
        $result = [];
        if ($value !== false) {
            if (in_array($channel->getFunction(), [ChannelFunction::RGBLIGHTING(), ChannelFunction::DIMMERANDRGBLIGHTING()])) {
                $result['color'] = ColorUtils::decToHex($value['color']);
                $result['color_brightness'] = $value['color_brightness'];
                $result['hue'] = ColorUtils::decToHue($value['color']);
                [$h, $s,] = ColorUtils::decToHsv($value['color']);
                [$r, $g, $b] = ColorUtils::hsvToRgb([$h, $s, $value['color_brightness']]);
                $result['hsv'] = ['hue' => $h, 'saturation' => $s, 'value' => $value['color_brightness']];
                $result['rgb'] = ['red' => $r, 'green' => $g, 'blue' => $b];
                $result['on'] = $value['color_brightness'] > 0;
            }
            if (in_array($channel->getFunction(), [ChannelFunction::DIMMER(), ChannelFunction::DIMMERANDRGBLIGHTING()])) {
                $result['brightness'] = $value['brightness'];
                $result['on'] = ($result['on'] ?? false) || $value['brightness'] > 0;
            }
        }
        return $result;
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::DIMMER(),
            ChannelFunction::RGBLIGHTING(),
            ChannelFunction::DIMMERANDRGBLIGHTING(),
        ];
    }
}

<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class ColorAndBrightnessChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getRgbwValue($channel);
        $result = [];
        if ($value !== false) {
            if (in_array($channel->getFunction(), [ChannelFunction::RGBLIGHTING(), ChannelFunction::DIMMERANDRGBLIGHTING()])) {
                $result['color'] = $value['color'];
                $result['color_brightness'] = $value['color_brightness'];
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

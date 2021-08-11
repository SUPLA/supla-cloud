<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class PercentageChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getIntValue($channel);
        if ($channel->getFunction() == ChannelFunction::VALVEPERCENTAGE()) {
            return ['closed' => min(100, max(0, $value))];
        }
return [
    // TODO should be determined from channel flags returned by the server, when it supports it; it will be breaking for Alexa/GA/MQTT
    'is_calibrating' => $value == -1,
    'not_calibrated' => $value == -1,
    'shut' => min(100, max(0, $value)),
];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
        ];
    }
}

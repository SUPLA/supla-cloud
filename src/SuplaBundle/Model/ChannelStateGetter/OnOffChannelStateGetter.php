<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class OnOffChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getCharValue($channel);
        return ['on' => $value == '1'];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::STAIRCASETIMER(),
            ChannelFunction::THERMOSTAT(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS(),
        ];
    }
}

<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class DoubleChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getDoubleValue($channel);
        if ($value !== false) {
            return ['value' => $value];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::WINDSENSOR(),
            ChannelFunction::PRESSURESENSOR(),
            ChannelFunction::RAINSENSOR(),
            ChannelFunction::WEIGHTSENSOR(),
        ];
    }
}

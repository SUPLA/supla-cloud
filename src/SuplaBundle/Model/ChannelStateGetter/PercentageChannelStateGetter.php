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
        return ['is_calibrating' => $value == -1, 'shut' => min(100, max(0, $value))];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
        ];
    }
}

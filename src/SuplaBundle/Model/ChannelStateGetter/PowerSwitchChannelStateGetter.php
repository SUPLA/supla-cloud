<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\RelayStateBits;
use SuplaBundle\Enums\ValveStateBits;
use SuplaBundle\Supla\SuplaServerAware;

class PowerSwitchChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        [$onOff, $flags] = $this->suplaServer->getRelayValue($channel);
        if ($onOff !== null) {
            return [
                'on' => $onOff == '1',
                'currentOverload' => RelayStateBits::OVERCURRENT_RELAY_OFF()->isOn(intval($flags)),
            ];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::POWERSWITCH(),
            ChannelFunction::LIGHTSWITCH(),
        ];
    }
}

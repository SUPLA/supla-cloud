<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ValveStateBits;
use SuplaBundle\Supla\SuplaServerAware;

class ValveChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        [$closed, $flags] = $this->suplaServer->getValveValue($channel);
        if ($closed !== null && $flags !== null) {
            return [
                'closed' => $channel->getFunction()->getId() === ChannelFunction::VALVEOPENCLOSE ? boolval($closed) : $closed,
                'manuallyClosed' => ValveStateBits::MANUALLY_CLOSED()->isSupported($flags),
                'flooding' => ValveStateBits::FLOODING()->isSupported($flags),
            ];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::VALVEOPENCLOSE(),
            ChannelFunction::VALVEPERCENTAGE(),
        ];
    }
}

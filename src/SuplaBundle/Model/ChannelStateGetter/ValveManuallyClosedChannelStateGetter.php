<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class ValveManuallyClosedChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $manuallyClosed = $this->suplaServer->getValue('VALVE-MANUALLY-CLOSED', $channel);
        return ['manuallyClosed' => $manuallyClosed == 1];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::VALVEOPENCLOSE(),
        ];
    }
}

<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Supla\SuplaServerAware;

class DigiglassChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $mask = $this->suplaServer->getValue('DIGIGLASS', $channel);
        $state = DigiglassState::channel($channel)->setMask($mask);
        return [
            'opaque' => $state->getOpaqueSections(),
            'transparent' => $state->getTransparentSections(),
            'mask' => $state->getMask(),
        ];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::DIGIGLASS_HORIZONTAL(),
            ChannelFunction::DIGIGLASS_VERTICAL(),
        ];
    }
}

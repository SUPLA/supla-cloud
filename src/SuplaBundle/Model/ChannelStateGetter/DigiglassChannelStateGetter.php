<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

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

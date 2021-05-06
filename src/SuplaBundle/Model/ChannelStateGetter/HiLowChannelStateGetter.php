<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class HiLowChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getCharValue($channel);
        return ['hi' => $value == '1'];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER(),
            ChannelFunction::OPENINGSENSOR_ROOFWINDOW(),
            ChannelFunction::OPENINGSENSOR_DOOR(),
            ChannelFunction::OPENINGSENSOR_GARAGEDOOR(),
            ChannelFunction::OPENINGSENSOR_GATEWAY(),
            ChannelFunction::OPENINGSENSOR_GATE(),
            ChannelFunction::OPENINGSENSOR_WINDOW(),
            ChannelFunction::MAILSENSOR(),
            ChannelFunction::NOLIQUIDSENSOR(),
        ];
    }
}

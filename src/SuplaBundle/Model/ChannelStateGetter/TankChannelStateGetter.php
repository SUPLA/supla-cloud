<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class TankChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getRawValue('CONTAINER', $channel);
        $value = rtrim($value);
        $value = substr($value, strlen('VALUE:'));
        $value = is_numeric($value) ? intval($value) : null;
        $fillLevelKnown = $value > 0;
        return ['fillLevel' => $fillLevelKnown ? $value - 1 : null];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::CONTAINER(),
            ChannelFunction::SEPTIC_TANK(),
            ChannelFunction::WATER_TANK(),
        ];
    }
}

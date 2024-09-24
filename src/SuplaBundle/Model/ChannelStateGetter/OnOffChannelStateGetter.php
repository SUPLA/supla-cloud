<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateOnOff",
 *     description="State of `THERMOSTAT`, `THERMOSTATHEATPOLHOMEPLUS`.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="on", type="boolean"),
 * )
 */
class OnOffChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getCharValue($channel);
        return ['on' => $value == '1'];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::THERMOSTAT(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS(),
            ChannelFunction::PUMPSWITCH(),
            ChannelFunction::HEATORCOLDSOURCESWITCH(),
        ];
    }
}

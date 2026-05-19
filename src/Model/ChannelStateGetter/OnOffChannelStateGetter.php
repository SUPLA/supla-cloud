<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Supla\SuplaServerAware;
use OpenApi\Annotations as OA;

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

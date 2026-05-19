<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Enums\RelayStateBits;
use App\Supla\SuplaServerAware;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ChannelStateRelay",
 *     description="State of `POWERSWITCH`, `LIGHTSWITCH`, `STAIRCASETIMER`.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="on", type="boolean"),
 *     @OA\Property(property="currentOverload", type="boolean"),
 * )
 */
class RelayChannelStateGetter implements SingleChannelStateGetter {
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
            ChannelFunction::STAIRCASETIMER(),
        ];
    }
}

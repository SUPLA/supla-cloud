<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateConnected",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="connectedCode", type="string"),
 * )
 */
class ConnectedChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $status = $this->suplaServer->getChannelConnectionStatus($channel);
        return [
            'connected' => $status->isConnected(),
            'connectedCode' => $status->getKey(),
        ];
    }

    public function supportedFunctions(): array {
        return ChannelFunction::values();
    }

    public static function getDefaultPriority(): int {
        return 1000;
    }
}

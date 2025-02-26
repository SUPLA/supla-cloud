<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

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
}

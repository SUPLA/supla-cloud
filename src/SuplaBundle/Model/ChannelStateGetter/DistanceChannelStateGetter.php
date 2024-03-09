<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateDepth",
 *     description="State of `DEPTHSENSOR`",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="depth", type="integer", minimum=0),
 * )
 * @OA\Schema(schema="ChannelStateDistance",
 *     description="State of `DISTANCESENSOR`",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="distance", type="integer", minimum=0),
 * )
 */
class DistanceChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getDoubleValue($channel);
        if ($value !== false) {
            $key = $channel->getFunction() == ChannelFunction::DISTANCESENSOR() ? 'distance' : 'depth';
            return [$key => $value === -1 ? null : $value];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::DISTANCESENSOR(),
            ChannelFunction::DEPTHSENSOR(),
        ];
    }
}

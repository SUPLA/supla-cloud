<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateDouble",
 *     description="State of `WINDSENSOR`, `PRESSURESENSOR`, `RAINSENSOR`, `WEIGHTSENSOR`",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="value", type="number"),
 * )
 */
class DoubleChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getDoubleValue($channel);
        if ($value !== false) {
            return ['value' => $value];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::WINDSENSOR(),
            ChannelFunction::PRESSURESENSOR(),
            ChannelFunction::RAINSENSOR(),
            ChannelFunction::WEIGHTSENSOR(),
        ];
    }
}

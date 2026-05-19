<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Supla\SuplaServerAware;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="ChannelStateDouble",
 *     description="State of `WINDSENSOR`, `PRESSURESENSOR`, `RAINSENSOR`, `WEIGHTSENSOR`, `GENERAL_PURPOSE_METER`, `GENERAL_PURPOSE_MEASUREMENT`.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="value", type="number"),
 * )
 */
class DoubleChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getDoubleValue($channel);
        if ($value !== false) {
            return ['value' => $value == -1 ? null : $value];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::WINDSENSOR(),
            ChannelFunction::PRESSURESENSOR(),
            ChannelFunction::WEIGHTSENSOR(),
        ];
    }
}

<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateImpulseCounter",
 *     description="State of `IC_ELECTRICITYMETER`, `IC_GASMETER`, `IC_WATERMETER`, `IC_HEATMETER`.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="totalCost", type="number"),
 *     @OA\Property(property="impulsesPerUnit", type="integer"),
 *     @OA\Property(property="counter", type="integer"),
 *     @OA\Property(property="calculatedValue", type="number"),
 *     @OA\Property(property="currency", type="string"),
 *     @OA\Property(property="unit", type="string"),
 * )
 */
class ImpulseCounterChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        return $this->suplaServer->getImpulseCounterValue($channel);
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::IC_ELECTRICITYMETER(),
            ChannelFunction::IC_GASMETER(),
            ChannelFunction::IC_WATERMETER(),
            ChannelFunction::IC_HEATMETER(),
        ];
    }
}

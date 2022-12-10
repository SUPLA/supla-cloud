<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateHumidity",
 *     description="State of `HUMIDITY`.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="humidity", type="number", minimum=0, maximum=100, description="value provided by the sensor, including possibly configured delta adjustment"),
 * )
 */
class HumidityChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getHumidityValue($channel);
        if ($value !== false) {
            return ['humidity' => $value];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::HUMIDITY(),
            ChannelFunction::HUMIDITYANDTEMPERATURE(),
        ];
    }
}

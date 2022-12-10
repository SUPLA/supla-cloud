<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateTemperature",
 *     description="State of `THERMOMETER`.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="temperature", type="number", description="value provided by the sensor, including possibly configured delta adjustment"),
 * )
 * @OA\Schema(schema="ChannelStateHumidityAndTemperature",
 *     description="State of `HUMIDITYANDTEMPERATURE`.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="temperature", type="number", description="value provided by the sensor, including possibly configured delta adjustment"),
 *     @OA\Property(property="humidity", type="number", minimum=0, maximum=100, description="value provided by the sensor, including possibly configured delta adjustment"),
 * )
 */
class TemperatureChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getTemperatureValue($channel);
        if ($value !== false) {
            return ['temperature' => $value];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::THERMOMETER(),
            ChannelFunction::HUMIDITYANDTEMPERATURE(),
        ];
    }
}

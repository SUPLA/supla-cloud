<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateGeneralPurposeMeasurement",
 *     description="State of `HVAC` thermostat channels.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="calculatedValue", type="number"),
 * )
 */
class GeneralPurposeMeasurementChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getRawValue('GPM', $channel);
        $value = rtrim($value);
        $value = floatval(substr($value, strlen('VALUE:')));
        return ['calculatedValue' => $value];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::GENERAL_PURPOSE_METER(),
            ChannelFunction::GENERAL_PURPOSE_MEASUREMENT(),
        ];
    }
}

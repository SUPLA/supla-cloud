<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateRollerShutter",
 *     description="State of `CONTROLLINGTHEROLLERSHUTTER`",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="isCalibrating", type="boolean"),
 *     @OA\Property(property="notCalibrated", type="boolean"),
 *     @OA\Property(property="shut", type="integer", maximum=100, minimum=0),
 * )
 */
class PercentageChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getIntValue($channel);
        return [
            // TODO should be determined from channel flags returned by server, when it supports it; it will be breaking for Alexa/GA/MQTT
            'is_calibrating' => $value == -1,
            'isCalibrating' => $value == -1,
            'not_calibrated' => $value == -1,
            'notCalibrated' => $value == -1,
            'shut' => min(100, max(0, $value)),
        ];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
            ChannelFunction::TERRACE_AWNING(),
            ChannelFunction::PROJECTOR_SCREEN(),
            ChannelFunction::CURTAIN(),
            ChannelFunction::ROLLER_GARAGE_DOOR(),
        ];
    }
}

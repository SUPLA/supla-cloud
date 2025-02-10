<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

/**
 * @OA\Schema(schema="ChannelStateSensor",
 *     description="State of sensors, e.g. `OPENINGSENSOR_DOOR`, `MAILSENSOR`, `NOLIQUIDSENSOR`.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="hi", type="boolean"),
 * )
 */
class HiLowChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getCharValue($channel);
        return ['hi' => $value == '1'];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER(),
            ChannelFunction::OPENINGSENSOR_ROOFWINDOW(),
            ChannelFunction::OPENINGSENSOR_DOOR(),
            ChannelFunction::OPENINGSENSOR_GARAGEDOOR(),
            ChannelFunction::OPENINGSENSOR_GATEWAY(),
            ChannelFunction::OPENINGSENSOR_GATE(),
            ChannelFunction::OPENINGSENSOR_WINDOW(),
            ChannelFunction::MAILSENSOR(),
            ChannelFunction::HOTELCARDSENSOR(),
            ChannelFunction::ALARM_ARMAMENT_SENSOR(),
            ChannelFunction::NOLIQUIDSENSOR(),
            ChannelFunction::CONTAINER_LEVEL_SENSOR(),
            ChannelFunction::FLOOD_SENSOR(),
        ];
    }
}

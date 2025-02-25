<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\TankStateBits;
use SuplaBundle\Supla\SuplaServerAware;

class TankChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $result = $this->suplaServer->getRawValue('CONTAINER', $channel);
        if ($result !== false) {
            [$fillLevel, $flags] = sscanf($result, "VALUE:%d,%d\n");
        }
        $fillLevelKnown = $fillLevel > 0;
        return [
            'fillLevel' => $fillLevelKnown ? $fillLevel - 1 : null,
            'warningLevel' => TankStateBits::WARNING_LEVEL()->isOn($flags),
            'alarmLevel' => TankStateBits::ALARM_LEVEL()->isOn($flags),
            'invalidSensorState' => TankStateBits::INVALID_SENSOR_STATE()->isOn($flags),
            'soundAlarmOn' => TankStateBits::SOUND_ALARM_ON()->isOn($flags),
        ];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::CONTAINER(),
            ChannelFunction::SEPTIC_TANK(),
            ChannelFunction::WATER_TANK(),
        ];
    }
}

<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\HvacIpcActionMode;
use SuplaBundle\Enums\HvacIpcValueFlags;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Supla\SuplaServerIsDownException;
use SuplaBundle\Utils\NumberUtils;

class HvacChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        // VALUE:%isOn,%mode,%setpointTemperatureHeat,%setpointTemperatureCool,%flags
        $value = $this->suplaServer->getRawValue('HVAC', $channel);
        $values = explode(',', substr($value, strlen('VALUE:')));
        if (count($values) !== 5) {
            throw new SuplaServerIsDownException();
        }
        [, $modeId, $tempHeat, $tempCool, $flags] = $values;
        return [
            'heating' => HvacIpcValueFlags::HEATING()->isOn($flags),
            'cooling' => HvacIpcValueFlags::COOLING()->isOn($flags),
            'manual' => !HvacIpcValueFlags::WEEKLY_SCHEDULE()->isOn($flags),
            'countdownTimer' => HvacIpcValueFlags::COUNTDOWN_TIMER()->isOn($flags),
            'thermometerError' => HvacIpcValueFlags::THERMOMETER_ERROR()->isOn($flags),
            'clockError' => HvacIpcValueFlags::CLOCK_ERROR()->isOn($flags),
            'forcedOffBySensor' => HvacIpcValueFlags::FORCED_OFF_BY_SENSOR()->isOn($flags),
            'weeklyScheduleTemporalOverride' => HvacIpcValueFlags::WEEKLY_SCHEDULE_TEMPORAL_OVERRIDE()->isOn($flags),
            'mode' => (new HvacIpcActionMode(intval($modeId)))->getKey(),
            'temperatureHeat' => HvacIpcValueFlags::TEMPERATURE_HEAT_SET()->isOn($flags)
                ? NumberUtils::maximumDecimalPrecision($tempHeat / 100)
                : null,
            'temperatureCool' => HvacIpcValueFlags::TEMPERATURE_COOL_SET()->isOn($flags)
                ? NumberUtils::maximumDecimalPrecision($tempCool / 100)
                : null,
        ];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT_AUTO(),
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
        ];
    }
}

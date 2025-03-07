<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\HvacIpcActionMode;
use SuplaBundle\Enums\HvacIpcValueFlags;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Supla\SuplaServerIsDownException;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelStateHvac",
 *     description="State of `HVAC` thermostat channels.",
 *     @OA\Property(property="connected", type="boolean"),
 *     @OA\Property(property="heating", type="boolean"),
 *     @OA\Property(property="cooling", type="boolean"),
 *     @OA\Property(property="manual", type="boolean"),
 *     @OA\Property(property="countdownTimer", type="boolean"),
 *     @OA\Property(property="thermometerError", type="boolean"),
 *     @OA\Property(property="clockError", type="boolean"),
 *     @OA\Property(property="forcedOffBySensor", type="boolean"),
 *     @OA\Property(property="weeklyScheduleTemporalOverride", type="boolean"),
 *     @OA\Property(property="batteryCoverOpen", type="boolean"),
 *     @OA\Property(property="calibrationError", type="boolean"),
 *     @OA\Property(property="mode", type="string"),
 *     @OA\Property(property="temperatureHeat", type="number"),
 *     @OA\Property(property="temperatureCool", type="number"),
 *     @OA\Property(property="temperatureMain", type="number"),
 *     @OA\Property(property="humidityMain", type="number"),
 * )
 */
class HvacChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        // VALUE:%isOn,%mode,%setpointTemperatureHeat,%setpointTemperatureCool,%flags\n
        $value = $this->suplaServer->getRawValue('HVAC', $channel);
        $value = rtrim($value);
        $values = explode(',', substr($value, strlen('VALUE:')));
        if (count($values) !== 7) {
            throw new SuplaServerIsDownException();
        }
        [$isOn, $modeId, $tempHeat, $tempCool, $flags, $tempMain, $humidityMain] = $values;
        return [
            'heating' => HvacIpcValueFlags::HEATING()->isOn($flags),
            'cooling' => HvacIpcValueFlags::COOLING()->isOn($flags),
            'partially' => $isOn >= 3 ? $isOn - 2 : 0,
            'manual' => !HvacIpcValueFlags::WEEKLY_SCHEDULE()->isOn($flags),
            'countdownTimer' => HvacIpcValueFlags::COUNTDOWN_TIMER()->isOn($flags),
            'thermometerError' => HvacIpcValueFlags::THERMOMETER_ERROR()->isOn($flags),
            'clockError' => HvacIpcValueFlags::CLOCK_ERROR()->isOn($flags),
            'forcedOffBySensor' => HvacIpcValueFlags::FORCED_OFF_BY_SENSOR()->isOn($flags),
            'weeklyScheduleTemporalOverride' => HvacIpcValueFlags::WEEKLY_SCHEDULE_TEMPORAL_OVERRIDE()->isOn($flags),
            'batteryCoverOpen' => HvacIpcValueFlags::BATTERY_COVER_OPEN()->isOn($flags),
            'calibrationError' => HvacIpcValueFlags::CALIBRATION_ERROR()->isOn($flags),
            'mode' => (new HvacIpcActionMode(intval($modeId)))->getKey(),
            'temperatureHeat' => HvacIpcValueFlags::TEMPERATURE_HEAT_SET()->isOn($flags)
                ? NumberUtils::maximumDecimalPrecision($tempHeat / 100)
                : null,
            'temperatureCool' => HvacIpcValueFlags::TEMPERATURE_COOL_SET()->isOn($flags)
                ? NumberUtils::maximumDecimalPrecision($tempCool / 100)
                : null,
            'temperatureMain' => $tempMain > -27300
                ? NumberUtils::maximumDecimalPrecision($tempMain / 100)
                : null,
            'humidityMain' => $humidityMain > -1
                ? NumberUtils::maximumDecimalPrecision($humidityMain / 100)
                : null,
        ];
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(),
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS(),
        ];
    }
}

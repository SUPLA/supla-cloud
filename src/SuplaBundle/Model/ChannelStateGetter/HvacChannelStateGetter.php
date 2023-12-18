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
 *     @OA\Property(property="forcedOffBySensor", type="boolean"),
 *     @OA\Property(property="weeklyScheduleTemporalOverride", type="boolean"),
 *     @OA\Property(property="mode", type="string"),
 *     @OA\Property(property="temperatureHeat", type="number"),
 *     @OA\Property(property="temperatureCool", type="number"),
 *     @OA\Property(property="temperatureMain", type="number"),
 *     @OA\Property(property="humidityMain", type="number"),
 * )
 */
class HvacChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    /** @var TemperatureChannelStateGetter */
    private $temperatureChannelStateGetter;

    public function __construct(TemperatureChannelStateGetter $temperatureChannelStateGetter) {
        $this->temperatureChannelStateGetter = $temperatureChannelStateGetter;
    }

    public function getState(IODeviceChannel $channel): array {
        // VALUE:%isOn,%mode,%setpointTemperatureHeat,%setpointTemperatureCool,%flags\n
        $value = $this->suplaServer->getRawValue('HVAC', $channel);
        $value = rtrim($value);
        $values = explode(',', substr($value, strlen('VALUE:')));
        if (count($values) !== 5) {
            throw new SuplaServerIsDownException();
        }
        [, $modeId, $tempHeat, $tempCool, $flags] = $values;
        $mainThermometerState = $this->getMainThermometerState($channel);
        return array_merge([
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
        ], $mainThermometerState);
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT_AUTO(),
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS(),
        ];
    }

    private function getMainThermometerState(IODeviceChannel $channel): array {
        $thermometerNo = $channel->getUserConfigValue('mainThermometerChannelNo');
        $thermometerChannel = $channel->getIoDevice()->getChannels()->filter(function (IODeviceChannel $c) use ($thermometerNo) {
            return $c->getChannelNumber() === $thermometerNo;
        })->first();
        $thermometerState = [
            'temperatureMain' => null,
            'humidityMain' => null,
        ];
        $thermometerFunctions = [ChannelFunction::THERMOMETER, ChannelFunction::HUMIDITYANDTEMPERATURE];
        if ($thermometerChannel && in_array($thermometerChannel->getFunction()->getId(), $thermometerFunctions)) {
            $state = $this->temperatureChannelStateGetter->getState($thermometerChannel);
            $thermometerState['temperatureMain'] = $state['temperature'];
            $thermometerState['humidityMain'] = $state['humidity'] ?? null;
        }
        return $thermometerState;
    }
}

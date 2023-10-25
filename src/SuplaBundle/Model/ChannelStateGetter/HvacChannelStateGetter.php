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

    /** @var TemperatureChannelStateGetter */
    private $temperatureChannelStateGetter;

    public function __construct(TemperatureChannelStateGetter $temperatureChannelStateGetter) {
        $this->temperatureChannelStateGetter = $temperatureChannelStateGetter;
    }

    public function getState(IODeviceChannel $channel): array {
        // VALUE:%isOn,%mode,%setpointTemperatureHeat,%setpointTemperatureCool,%flags
        $value = $this->suplaServer->getRawValue('HVAC', $channel);
        $values = explode(',', substr($value, strlen('VALUE:')));
        if (count($values) !== 5) {
            throw new SuplaServerIsDownException();
        }
        [, $modeId, $tempHeat, $tempCool, $flags] = $values;
        $temperatureMain = $this->getMainThermometerTemperature($channel);
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
            'temperatureMain' => $temperatureMain,
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

    public function getMainThermometerTemperature(IODeviceChannel $channel) {
        $thermometerNo = $channel->getUserConfigValue('mainThermometerChannelNo');
        $thermometerChannel = $channel->getIoDevice()->getChannels()->filter(function (IODeviceChannel $c) use ($thermometerNo) {
            return $c->getChannelNumber() === $thermometerNo;
        })->first();
        $temperatureMain = null;
        $thermometerFunctions = [ChannelFunction::THERMOMETER, ChannelFunction::HUMIDITYANDTEMPERATURE];
        if ($thermometerChannel && in_array($thermometerChannel->getFunction()->getId(), $thermometerFunctions)) {
            $config = $this->temperatureChannelStateGetter->getState($thermometerChannel);
            $temperatureMain = $config['temperature'];
        }
        return $temperatureMain;
    }
}

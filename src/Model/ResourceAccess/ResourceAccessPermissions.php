<?php
namespace App\Model\ResourceAccess;

use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType as CT;

/** Manual M5 resource representability; keep this contract aligned with Core. */
class ResourceAccessPermissions {
    // Protocol-v21 impulse functions not yet named in the general Cloud enum.
    private const IC_EVENTS = 350;
    private const IC_SECONDS = 360;
    public static function supported(int $type, int $function, int $permissions, array $properties): bool {
        if ($permissions < 1 || $permissions > 7 || !$function) {
            return false;
        }
        if ($type === CT::ACTION_TRIGGER || $function === CF::ACTION_TRIGGER) {
            return $type === CT::ACTION_TRIGGER && $function === CF::ACTION_TRIGGER
                && $permissions === 4 && !empty($properties['actionTriggerCapabilities']);
        }
        if ($permissions & 4) {
            return false;
        }
        $control = [
            CT::RELAY => [CF::POWERSWITCH, CF::LIGHTSWITCH, CF::STAIRCASETIMER, CF::PUMPSWITCH,
                CF::HEATORCOLDSOURCESWITCH, CF::CONTROLLINGTHEGATEWAYLOCK, CF::CONTROLLINGTHEGATE,
                CF::CONTROLLINGTHEGARAGEDOOR, CF::CONTROLLINGTHEDOORLOCK, CF::CONTROLLINGTHEROLLERSHUTTER,
                CF::CONTROLLINGTHEROOFWINDOW, CF::CONTROLLINGTHEFACADEBLIND, CF::TERRACE_AWNING,
                CF::PROJECTOR_SCREEN, CF::CURTAIN, CF::VERTICAL_BLIND, CF::ROLLER_GARAGE_DOOR],
            CT::HVAC => [CF::HVAC_THERMOSTAT, CF::HVAC_THERMOSTAT_HEAT_COOL, CF::HVAC_THERMOSTAT_DIFFERENTIAL, CF::HVAC_DOMESTIC_HOT_WATER],
            CT::VALVEOPENCLOSE => [CF::VALVEOPENCLOSE], CT::VALVEPERCENTAGE => [CF::VALVEPERCENTAGE],
            CT::DIMMER => [CF::DIMMER, CF::DIMMER_CCT], CT::RGBLEDCONTROLLER => [CF::RGBLIGHTING],
            CT::DIMMERANDRGBLED => [CF::DIMMERANDRGBLIGHTING, CF::DIMMER_CCT_AND_RGB],
        ];
        if (in_array($function, $control[$type] ?? [], true)) {
            return true;
        }
        if ($permissions & 2) {
            return false;
        }
        $read = [
            CT::SENSORNO => array_map(fn(CF $fn) => $fn->getId(), CT::functions()[CT::SENSORNO]),
            CT::THERMOMETER => [CF::THERMOMETER], CT::HUMIDITYSENSOR => [CF::HUMIDITY],
            CT::HUMIDITYANDTEMPSENSOR => [CF::HUMIDITYANDTEMPERATURE],
            CT::GENERAL_PURPOSE_MEASUREMENT => [CF::GENERAL_PURPOSE_MEASUREMENT],
            CT::GENERAL_PURPOSE_METER => [CF::GENERAL_PURPOSE_METER], CT::ELECTRICITYMETER => [CF::ELECTRICITYMETER],
            CT::IMPULSECOUNTER => [CF::IC_ELECTRICITYMETER, CF::IC_GASMETER, CF::IC_WATERMETER, CF::IC_HEATMETER, self::IC_EVENTS, self::IC_SECONDS],
            CT::CONTAINER => [CF::CONTAINER, CF::SEPTIC_TANK, CF::WATER_TANK],
        ];
        return in_array($function, $read[$type] ?? [], true);
    }
}

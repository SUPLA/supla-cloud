<?php

namespace SuplaBundle\Model;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

/**
 * @OA\Schema(schema="ReactionTrigger", description="Reaction trigger.",
 *     oneOf={
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerLt"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerLe"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerGt"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerGe"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerEq"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerNe"),
 *       @OA\Schema(ref="#/components/schemas/ReactionTriggerChange"),
 *     }
 * )
 * @OA\Schema(schema="ReactionTriggerFieldNames", type="string", example="temperature", enum={
 *    "temperature",
 *    "humidity",
 *    "brightness",
 *    "color_brightness",
 *    "color",
 *    "voltage_avg",
 *    "voltage1",
 *    "voltage2",
 *    "voltage3",
 *    "current_sum",
 *    "current1",
 *    "current2",
 *    "current3",
 *    "power_active_sum",
 *    "power_active1",
 *    "power_active2",
 *    "power_active3",
 *    "power_reactive_sum",
 *    "power_reactive1",
 *    "power_reactive2",
 *    "power_reactive3",
 *    "power_apparent_sum",
 *    "power_apparent1",
 *    "power_apparent2",
 *    "power_apparent3",
 *    "fae1",
 *    "fae2",
 *    "fae3",
 *    "fae_sum",
 *    "fae_balanced",
 *    "rae1",
 *    "rae2",
 *    "rae3",
 *    "rae_sum",
 *    "rae_balanced",
 *    "manually_closed",
 *    "flooding",
 *    "counter",
 *    "calculated_value",
 * })
 */
class ValueBasedTriggerValidator {
    private const FIELD_NAMES = [
        'temperature' => [ChannelFunction::THERMOMETER, ChannelFunction::HUMIDITYANDTEMPERATURE],
        'humidity' => [ChannelFunction::HUMIDITY, ChannelFunction::HUMIDITYANDTEMPERATURE],
        'brightness' => [ChannelFunction::DIMMER, ChannelFunction::DIMMERANDRGBLIGHTING],
        'color_brightness' => [ChannelFunction::RGBLIGHTING, ChannelFunction::DIMMERANDRGBLIGHTING],
        'color' => [ChannelFunction::RGBLIGHTING, ChannelFunction::DIMMERANDRGBLIGHTING],
        'voltage_avg' => [ChannelFunction::ELECTRICITYMETER],
        'voltage1' => [ChannelFunction::ELECTRICITYMETER],
        'voltage2' => [ChannelFunction::ELECTRICITYMETER],
        'voltage3' => [ChannelFunction::ELECTRICITYMETER],
        'current_sum' => [ChannelFunction::ELECTRICITYMETER],
        'current1' => [ChannelFunction::ELECTRICITYMETER],
        'current2' => [ChannelFunction::ELECTRICITYMETER],
        'current3' => [ChannelFunction::ELECTRICITYMETER],
        'power_active_sum' => [ChannelFunction::ELECTRICITYMETER],
        'power_active1' => [ChannelFunction::ELECTRICITYMETER],
        'power_active2' => [ChannelFunction::ELECTRICITYMETER],
        'power_active3' => [ChannelFunction::ELECTRICITYMETER],
        'power_reactive_sum' => [ChannelFunction::ELECTRICITYMETER],
        'power_reactive1' => [ChannelFunction::ELECTRICITYMETER],
        'power_reactive2' => [ChannelFunction::ELECTRICITYMETER],
        'power_reactive3' => [ChannelFunction::ELECTRICITYMETER],
        'power_apparent_sum' => [ChannelFunction::ELECTRICITYMETER],
        'power_apparent1' => [ChannelFunction::ELECTRICITYMETER],
        'power_apparent2' => [ChannelFunction::ELECTRICITYMETER],
        'power_apparent3' => [ChannelFunction::ELECTRICITYMETER],
        'fae1' => [ChannelFunction::ELECTRICITYMETER],
        'fae2' => [ChannelFunction::ELECTRICITYMETER],
        'fae3' => [ChannelFunction::ELECTRICITYMETER],
        'fae_sum' => [ChannelFunction::ELECTRICITYMETER],
        'fae_balanced' => [ChannelFunction::ELECTRICITYMETER],
        'rae1' => [ChannelFunction::ELECTRICITYMETER],
        'rae2' => [ChannelFunction::ELECTRICITYMETER],
        'rae3' => [ChannelFunction::ELECTRICITYMETER],
        'rae_sum' => [ChannelFunction::ELECTRICITYMETER],
        'rae_balanced' => [ChannelFunction::ELECTRICITYMETER],
        'manually_closed' => [ChannelFunction::VALVEOPENCLOSE],
        'flooding' => [ChannelFunction::VALVEOPENCLOSE],
        'is_on' => [
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
        ],
        'heating' => [ChannelFunction::HVAC_THERMOSTAT, ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL],
        'cooling' => [ChannelFunction::HVAC_THERMOSTAT, ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL],
        'heating_or_cooling' => [ChannelFunction::HVAC_THERMOSTAT, ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL],
        'clock_error' => [
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
        ],
        'thermometer_error' => [
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
        ],
        'is_battery_cover_open' => [
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
        ],
        'battery_powered' => [
            ChannelFunction::THERMOMETER,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
        ],
        'battery_level' => [
            ChannelFunction::THERMOMETER,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
        ],
        'counter' => [
            ChannelFunction::IC_HEATMETER,
            ChannelFunction::IC_WATERMETER,
            ChannelFunction::IC_GASMETER,
            ChannelFunction::IC_ELECTRICITYMETER,
        ],
        'calculated_value' => [
            ChannelFunction::IC_HEATMETER,
            ChannelFunction::IC_WATERMETER,
            ChannelFunction::IC_GASMETER,
            ChannelFunction::IC_ELECTRICITYMETER,
        ],
        'calibration_failed' => [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEROOFWINDOW,
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::TERRACE_AWNING,
            ChannelFunction::PROJECTOR_SCREEN,
            ChannelFunction::CURTAIN,
            ChannelFunction::ROLLER_GARAGE_DOOR,
            ChannelFunction::VERTICAL_BLIND,
        ],
        'calibration_in_progress' => [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEROOFWINDOW,
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::TERRACE_AWNING,
            ChannelFunction::PROJECTOR_SCREEN,
            ChannelFunction::CURTAIN,
            ChannelFunction::ROLLER_GARAGE_DOOR,
            ChannelFunction::VERTICAL_BLIND,
        ],
        'calibration_lost' => [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEROOFWINDOW,
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::TERRACE_AWNING,
            ChannelFunction::PROJECTOR_SCREEN,
            ChannelFunction::CURTAIN,
            ChannelFunction::ROLLER_GARAGE_DOOR,
            ChannelFunction::VERTICAL_BLIND,
        ],
        'motor_problem' => [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEROOFWINDOW,
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::TERRACE_AWNING,
            ChannelFunction::PROJECTOR_SCREEN,
            ChannelFunction::CURTAIN,
            ChannelFunction::ROLLER_GARAGE_DOOR,
            ChannelFunction::VERTICAL_BLIND,
        ],
        'invalid_value' => [
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
            ChannelFunction::WATER_TANK,
        ],
        'alarm' => [
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
            ChannelFunction::WATER_TANK,
        ],
        'warning' => [
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
            ChannelFunction::WATER_TANK,
        ],
        'invalid_sensor_state' => [
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
            ChannelFunction::WATER_TANK,
        ],
        'sound_alarm_on' => [
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
            ChannelFunction::WATER_TANK,
        ],
        'default' => [
            ChannelFunction::VALVEOPENCLOSE,
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::CONTROLLINGTHEROOFWINDOW,
            ChannelFunction::CONTROLLINGTHEFACADEBLIND,
            ChannelFunction::TERRACE_AWNING,
            ChannelFunction::PROJECTOR_SCREEN,
            ChannelFunction::CURTAIN,
            ChannelFunction::ROLLER_GARAGE_DOOR,
            ChannelFunction::VERTICAL_BLIND,
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
            ChannelFunction::WATER_TANK,
        ],
    ];

    private const THRESHOLD_SUPPORT = [
        ChannelFunction::THERMOMETER,
        ChannelFunction::HUMIDITYANDTEMPERATURE,
        ChannelFunction::HUMIDITY,
        ChannelFunction::DEPTHSENSOR,
        ChannelFunction::DISTANCESENSOR,
        ChannelFunction::ELECTRICITYMETER,
        ChannelFunction::CONTROLLINGTHEROOFWINDOW,
        ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
        ChannelFunction::CONTROLLINGTHEFACADEBLIND,
        ChannelFunction::TERRACE_AWNING,
        ChannelFunction::PROJECTOR_SCREEN,
        ChannelFunction::CURTAIN,
        ChannelFunction::ROLLER_GARAGE_DOOR,
        ChannelFunction::VERTICAL_BLIND,
        ChannelFunction::WINDSENSOR,
        ChannelFunction::WEIGHTSENSOR,
        ChannelFunction::RAINSENSOR,
        ChannelFunction::PRESSURESENSOR,
        ChannelFunction::RGBLIGHTING,
        ChannelFunction::DIMMERANDRGBLIGHTING,
        ChannelFunction::DIMMER,
        ChannelFunction::IC_ELECTRICITYMETER,
        ChannelFunction::IC_GASMETER,
        ChannelFunction::IC_WATERMETER,
        ChannelFunction::IC_HEATMETER,
        ChannelFunction::GENERAL_PURPOSE_METER,
        ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
        ChannelFunction::HVAC_THERMOSTAT,
        ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
        ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
        ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
        ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
        ChannelFunction::CONTAINER,
        ChannelFunction::SEPTIC_TANK,
        ChannelFunction::WATER_TANK,
    ];

    const BOOLEAN_FIELD_NAMES = [
        'calibration_failed',
        'calibration_in_progress',
        'calibration_lost',
        'motor_problem',
        'clock_error',
        'thermometer_error',
        'is_battery_cover_open',
        'battery_powered',
        'heating',
        'cooling',
        'heating_or_cooling',
        'is_on',
        'flooding',
        'manually_closed',
        'invalid_value',
        'alarm',
        'warning',
        'invalid_sensor_state',
        'sound_alarm_on',
    ];

    /**
     * @OA\Schema(schema="ReactionTriggerChange", description="Reaction trigger based any state (on every change).",
     *   @OA\Property(property="on_change", type="object",
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *   )
     * )
     */
    public function validate(IODeviceChannel $channel, array $trigger): void {
        Assertion::count($trigger, 1, 'Invalid trigger. Only one on_change or on_change_to section can be defined.');
        if (isset($trigger['on_change'])) {
            Assertion::isArray($trigger['on_change'], 'on_change_to must be an object');
            if ($trigger['on_change']) {
                Assertion::count($trigger['on_change'], 1, 'Only name can be defined inside on_change trigger.');
                Assertion::keyExists($trigger['on_change'], 'name', 'Only name can be defined inside on_change trigger.');
            }
            $this->validateFieldName($channel, $trigger['on_change']);
        } elseif (isset($trigger['on_change_to'])) {
            Assertion::isArray($trigger['on_change_to'], 'on_change_to must be an object');
            $onChangeTo = $trigger['on_change_to'];
            $this->validateFieldName($channel, $onChangeTo);
            if (array_intersect_key($onChangeTo, array_flip(['lt', 'le', 'gt', 'ge']))) {
                $this->validateThresholdTrigger($channel, $onChangeTo);
            } elseif (array_intersect_key($onChangeTo, array_flip(['eq', 'ne']))) {
                $this->validateEqualityTrigger($channel, $onChangeTo);
            } else {
                throw new \InvalidArgumentException('Unrecognized trigger format.');
            }
        } else {
            throw new \InvalidArgumentException('Missing on_change_to and on_change definition.');
        }
    }

    private function validateFieldName(IODeviceChannel $channel, array $onChangeTo): void {
        $possibleFieldNames = array_filter(self::FIELD_NAMES, function ($functionIds) use ($channel) {
            return in_array($channel->getFunction()->getId(), $functionIds);
        });
        if ($possibleFieldNames) {
            $possibleFieldNames = array_keys($possibleFieldNames);
            if (isset($onChangeTo['name'])) {
                Assertion::inArray($onChangeTo['name'], $possibleFieldNames, 'Unsupported field name %s. Supported: %s.');
            } else {
                Assertion::inArray('default', $possibleFieldNames, 'Missing trigger field definition.');
            }
        } else {
            Assertion::keyNotExists($onChangeTo, 'name', 'Field name is not required for this channel. Remove it.');
        }
    }

    /**
     * @OA\Schema(schema="ReactionTriggerLt", description="Reaction trigger based on numeric state (less than).",
     *   @OA\Property(property="on_change_to", type="object", required={"lt"},
     *     @OA\Property(property="lt", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="ge", type="number"))
     *   )
     * )
     * @OA\Schema(schema="ReactionTriggerLe", description="Reaction trigger based on numeric state (less than or equal).",
     *   @OA\Property(property="on_change_to", type="object",  required={"le"},
     *     @OA\Property(property="le", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="gt", type="number"))
     *   )
     * )
     * @OA\Schema(schema="ReactionTriggerGt", description="Reaction trigger based on numeric state (greater than).",
     *   @OA\Property(property="on_change_to", type="object",  required={"gt"},
     *     @OA\Property(property="gt", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="le", type="number"))
     *   )
     * )
     * @OA\Schema(schema="ReactionTriggerGe", description="Reaction trigger based on numeric state (greater than or equal).",
     *   @OA\Property(property="on_change_to", type="object",  required={"ge"},
     *     @OA\Property(property="ge", type="number"),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *     @OA\Property(property="resume", @OA\Property(property="lt", type="number"))
     *   )
     * )
     */
    private function validateThresholdTrigger(IODeviceChannel $channel, array $onChangeTo): void {
        Assertion::inArray($channel->getFunction()->getId(), self::THRESHOLD_SUPPORT, 'Threshold trigger unsupported for this function.');
        $mainCondition = array_intersect_key($onChangeTo, array_flip(['lt', 'le', 'gt', 'ge']));
        Assertion::count($mainCondition, 1, 'You must define only one condition for the threshold (lt, le, gt or ge).');
        $operator = key($mainCondition);
        Assertion::numeric($onChangeTo[$operator], 'Threshold must be numeric.');
        if (isset($onChangeTo['resume'])) {
            $resume = $onChangeTo['resume'];
            $resumeOperator = ['lt' => 'ge', 'le' => 'gt', 'ge' => 'lt', 'gt' => 'le'][$operator];
            Assertion::isArray($resume, 'Invalid resume');
            Assertion::count($resume, 1, 'Invalid resume');
            Assertion::keyExists($resume, $resumeOperator, "Resume for this trigger must have a $resumeOperator operator.");
            Assertion::numeric($resume[$resumeOperator], 'Threshold must be numeric.');
            if (in_array($operator, ['lt', 'le'])) {
                Assertion::lessOrEqualThan(
                    $onChangeTo[$operator],
                    $resume[$resumeOperator],
                    'Resume value must be greater than monitored value.'
                );
            } else {
                Assertion::greaterOrEqualThan(
                    $onChangeTo[$operator],
                    $resume[$resumeOperator],
                    'Resume value must be less than monitored value.'
                );
            }
        }
    }

    /**
     * @OA\Schema(schema="ReactionTriggerEq", description="Reaction trigger based on numeric or binary state (equal).",
     *   @OA\Property(property="on_change_to", type="object",  required={"eq"},
     *     @OA\Property(property="eq", oneOf={
     *       @OA\Schema(type="number"),
     *       @OA\Schema(type="string", enum={"hi", "closed", "on", "lo", "low", "open", "off"}),
     *     }),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *   )
     * )
     * @OA\Schema(schema="ReactionTriggerNe", description="Reaction trigger based on numeric or binary state (not equal).",
     *   @OA\Property(property="on_change_to", type="object",  required={"ne"},
     *     @OA\Property(property="ne", oneOf={
     *       @OA\Schema(type="number"),
     *       @OA\Schema(type="string", enum={"hi", "closed", "on", "lo", "low", "open", "off"}),
     *     }),
     *     @OA\Property(property="name", ref="#/components/schemas/ReactionTriggerFieldNames"),
     *   )
     * )
     */
    private function validateEqualityTrigger(IODeviceChannel $channel, array $onChangeTo): void {
        $mainCondition = array_intersect_key($onChangeTo, array_flip(['eq', 'ne']));
        Assertion::count($mainCondition, 1, 'You must define only one condition for the threshold (eq, ne).');
        $operator = key($mainCondition);
        $fieldName = $onChangeTo['name'] ?? 'default';
        if (in_array($channel->getFunction()->getId(), self::THRESHOLD_SUPPORT) && !in_array($fieldName, self::BOOLEAN_FIELD_NAMES)) {
            Assertion::numeric($onChangeTo[$operator], 'Threshold must be numeric.');
        } else {
            Assertion::inArray(
                $onChangeTo[$operator],
                ['hi', 'closed', 'on', 'lo', 'low', 'open', 'off'],
                'Invalid comparison value: ' . var_export($onChangeTo[$operator], true)
            );
        }
    }
}

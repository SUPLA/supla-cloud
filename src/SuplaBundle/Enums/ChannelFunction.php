<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Enums;

use MyCLabs\Enum\Enum;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Exception\ApiException;
use Symfony\Component\Serializer\Annotation\Groups;
use UnexpectedValueException;

/**
 * @OA\Schema(schema="ChannelFunctionEnumNames", type="string", example="OPENINGSENSOR_GATE")
 * @OA\Schema(
 *   schema="ChannelFunction", type="object",
 *   @OA\Property(property="id", type="integer", example=60),
 *   @OA\Property(property="name", ref="#/components/schemas/ChannelFunctionEnumNames"),
 *   @OA\Property(property="caption", type="string", example="Gate opening sensor"),
 *   @OA\Property(property="maxAlternativeIconIndex", type="integer"),
 *   @OA\Property(property="possibleVisualStates", type="array", description="Possible visual states of channel with this function. Ordered.", @OA\Items(type="string", example="opened")),
 *   @OA\Property(property="output", type="boolean", description="Whether the function is output type (i.e. can execute action) or input (i.e. provides data)", example=false),
 * )
 *
 * @method static ChannelFunction UNSUPPORTED()
 * @method static ChannelFunction NONE()
 * @method static ChannelFunction SCENE()
 * @method static ChannelFunction SCHEDULE()
 * @method static ChannelFunction NOTIFICATION()
 * @method static ChannelFunction CONTROLLINGTHEGATEWAYLOCK()
 * @method static ChannelFunction CONTROLLINGTHEGATE()
 * @method static ChannelFunction CONTROLLINGTHEGARAGEDOOR()
 * @method static ChannelFunction THERMOMETER()
 * @method static ChannelFunction HUMIDITY()
 * @method static ChannelFunction HUMIDITYANDTEMPERATURE()
 * @method static ChannelFunction OPENINGSENSOR_GATEWAY()
 * @method static ChannelFunction OPENINGSENSOR_GATE()
 * @method static ChannelFunction OPENINGSENSOR_GARAGEDOOR()
 * @method static ChannelFunction NOLIQUIDSENSOR()
 * @method static ChannelFunction CONTROLLINGTHEDOORLOCK()
 * @method static ChannelFunction OPENINGSENSOR_DOOR()
 * @method static ChannelFunction CONTROLLINGTHEROLLERSHUTTER()
 * @method static ChannelFunction CONTROLLINGTHEROOFWINDOW()
 * @method static ChannelFunction OPENINGSENSOR_ROLLERSHUTTER()
 * @method static ChannelFunction OPENINGSENSOR_ROOFWINDOW()
 * @method static ChannelFunction POWERSWITCH()
 * @method static ChannelFunction LIGHTSWITCH()
 * @method static ChannelFunction DIMMER()
 * @method static ChannelFunction RGBLIGHTING()
 * @method static ChannelFunction DIMMERANDRGBLIGHTING()
 * @method static ChannelFunction DEPTHSENSOR()
 * @method static ChannelFunction DISTANCESENSOR()
 * @method static ChannelFunction OPENINGSENSOR_WINDOW()
 * @method static ChannelFunction HOTELCARDSENSOR()
 * @method static ChannelFunction ALARM_ARMAMENT_SENSOR()
 * @method static ChannelFunction MAILSENSOR()
 * @method static ChannelFunction WINDSENSOR()
 * @method static ChannelFunction PRESSURESENSOR()
 * @method static ChannelFunction RAINSENSOR()
 * @method static ChannelFunction WEIGHTSENSOR()
 * @method static ChannelFunction WEATHER_STATION()
 * @method static ChannelFunction STAIRCASETIMER()
 * @method static ChannelFunction ELECTRICITYMETER()
 * @method static ChannelFunction IC_ELECTRICITYMETER()
 * @method static ChannelFunction IC_GASMETER()
 * @method static ChannelFunction IC_HEATMETER()
 * @method static ChannelFunction IC_WATERMETER()
 * @method static ChannelFunction THERMOSTAT()
 * @method static ChannelFunction THERMOSTATHEATPOLHOMEPLUS()
 * @method static ChannelFunction HVAC_THERMOSTAT()
 * @method static ChannelFunction HVAC_THERMOSTAT_HEAT_COOL()
 * @method static ChannelFunction HVAC_DRYER()
 * @method static ChannelFunction HVAC_FAN()
 * @method static ChannelFunction HVAC_THERMOSTAT_DIFFERENTIAL()
 * @method static ChannelFunction HVAC_DOMESTIC_HOT_WATER()
 * @method static ChannelFunction VALVEOPENCLOSE()
 * @method static ChannelFunction VALVEPERCENTAGE()
 * @method static ChannelFunction GENERAL_PURPOSE_MEASUREMENT()
 * @method static ChannelFunction GENERAL_PURPOSE_METER()
 * @method static ChannelFunction ACTION_TRIGGER()
 * @method static ChannelFunction DIGIGLASS_VERTICAL()
 * @method static ChannelFunction DIGIGLASS_HORIZONTAL()
 * @method static ChannelFunction CONTROLLINGTHEFACADEBLIND()
 * @method static ChannelFunction TERRACE_AWNING()
 * @method static ChannelFunction PROJECTOR_SCREEN()
 * @method static ChannelFunction CURTAIN()
 * @method static ChannelFunction VERTICAL_BLIND()
 * @method static ChannelFunction ROLLER_GARAGE_DOOR()
 * @method static ChannelFunction PUMPSWITCH()
 * @method static ChannelFunction HEATORCOLDSOURCESWITCH()
 * @method static ChannelFunction CONTAINER()
 * @method static ChannelFunction SEPTIC_TANK()
 * @method static ChannelFunction WATER_TANK()
 * @method static ChannelFunction CONTAINER_LEVEL_SENSOR()
 */
final class ChannelFunction extends Enum {
    const UNSUPPORTED = -1;
    const NONE = 0;
    const SCENE = 2000;
    const SCHEDULE = 2010;
    const NOTIFICATION = 2020;
    const CONTROLLINGTHEGATEWAYLOCK = 10;
    const CONTROLLINGTHEGATE = 20;
    const CONTROLLINGTHEGARAGEDOOR = 30;
    const THERMOMETER = 40;
    const HUMIDITY = 42;
    const HUMIDITYANDTEMPERATURE = 45;
    const OPENINGSENSOR_GATEWAY = 50;
    const OPENINGSENSOR_GATE = 60;
    const OPENINGSENSOR_GARAGEDOOR = 70;
    const NOLIQUIDSENSOR = 80;
    const CONTROLLINGTHEDOORLOCK = 90;
    const OPENINGSENSOR_DOOR = 100;
    const CONTROLLINGTHEROLLERSHUTTER = 110;
    const CONTROLLINGTHEROOFWINDOW = 115;
    const OPENINGSENSOR_ROLLERSHUTTER = 120;
    const OPENINGSENSOR_ROOFWINDOW = 125;
    const POWERSWITCH = 130;
    const LIGHTSWITCH = 140;
    const DIMMER = 180;
    const RGBLIGHTING = 190;
    const DIMMERANDRGBLIGHTING = 200;
    const DEPTHSENSOR = 210;
    const DISTANCESENSOR = 220;
    const OPENINGSENSOR_WINDOW = 230;
    const HOTELCARDSENSOR = 235;
    const ALARM_ARMAMENT_SENSOR = 236;
    const MAILSENSOR = 240;
    const WINDSENSOR = 250;
    const PRESSURESENSOR = 260;
    const RAINSENSOR = 270;
    const WEIGHTSENSOR = 280;
    const WEATHER_STATION = 290;
    const STAIRCASETIMER = 300;
    const ELECTRICITYMETER = 310;
    const IC_ELECTRICITYMETER = 315;
    const IC_GASMETER = 320;
    const IC_WATERMETER = 330;
    const IC_HEATMETER = 340;
    const THERMOSTAT = 400;
    const THERMOSTATHEATPOLHOMEPLUS = 410;
    const HVAC_THERMOSTAT = 420;
    const HVAC_THERMOSTAT_HEAT_COOL = 422;
    const HVAC_DRYER = 423;
    const HVAC_FAN = 424;
    const HVAC_THERMOSTAT_DIFFERENTIAL = 425;
    const HVAC_DOMESTIC_HOT_WATER = 426;
    const VALVEOPENCLOSE = 500;
    const VALVEPERCENTAGE = 510;
    const GENERAL_PURPOSE_MEASUREMENT = 520;
    const GENERAL_PURPOSE_METER = 530;
    const ACTION_TRIGGER = 700;
    const DIGIGLASS_HORIZONTAL = 800;
    const DIGIGLASS_VERTICAL = 810;
    const CONTROLLINGTHEFACADEBLIND = 900;
    const TERRACE_AWNING = 910;
    const PROJECTOR_SCREEN = 920;
    const CURTAIN = 930;
    const VERTICAL_BLIND = 940;
    const ROLLER_GARAGE_DOOR = 950;
    const PUMPSWITCH = 960;
    const HEATORCOLDSOURCESWITCH = 970;
    const CONTAINER = 980;
    const SEPTIC_TANK = 981;
    const WATER_TANK = 982;
    const CONTAINER_LEVEL_SENSOR = 990;

    private $unsupportedFunctionId;

    private static $deprecatedNames = [
        'GASMETER' => 'IC_GASMETER',
        'WATERMETER' => 'IC_WATERMETER',
        'HEATMETER' => 'IC_HEATMETER',
        'HVAC_THERMOSTAT_AUTO' => 'HVAC_THERMOSTAT_HEAT_COOL',
    ];

    /** @Groups({"basic"}) */
    public function getId(): int {
        return $this->value == self::UNSUPPORTED ? ($this->unsupportedFunctionId ?: -1) : $this->value;
    }

    /** @Groups({"basic"}) */
    public function getName(): string {
        return $this->getKey();
    }

    /** @Groups({"basic"}) */
    public function getCaption(): string {
        return self::captions()[$this->value] ?? 'Unknown'; // i18n
    }

    /**
     * @return ChannelFunctionAction[]
     */
    public function getDefaultPossibleActions(): array {
        return self::actions()[$this->getValue()] ?? [];
    }

    /**
     * @Groups({"basic"})
     * @return string[]
     */
    public function getPossibleVisualStates(): array {
        return self::possibleVisualStates()[$this->getValue()] ?? [];
    }

    /**
     * @param IODeviceChannel $channel
     * @return ChannelFunction[]
     */
    public static function forChannel(IODeviceChannel $channel): array {
        $type = $channel->getType();
        if (in_array($type->getId(), [ChannelType::RELAY, ChannelType::BRIDGE, ChannelType::HVAC])) {
            return ChannelFunctionBitsFlist::getSupportedFunctions($channel->getFuncList());
        } else {
            return ChannelType::functions()[$type->getValue()];
        }
    }

    /** @Groups({"basic"}) */
    public function getMaxAlternativeIconIndex(): int {
        return self::maxAlternativeIconIndexes()[$this->getValue()] ?? 0;
    }

    public static function actions(): array {
        $actions = [
            self::CONTROLLINGTHEGATEWAYLOCK => [ChannelFunctionAction::OPEN()],
            self::CONTROLLINGTHEDOORLOCK => [ChannelFunctionAction::OPEN()],
            self::CONTROLLINGTHEGATE => [
                ChannelFunctionAction::OPEN(),
                ChannelFunctionAction::CLOSE(),
                ChannelFunctionAction::OPEN_CLOSE(),
                ChannelFunctionAction::COPY(),
            ],
            self::CONTROLLINGTHEGARAGEDOOR => [
                ChannelFunctionAction::OPEN(),
                ChannelFunctionAction::CLOSE(),
                ChannelFunctionAction::OPEN_CLOSE(),
                ChannelFunctionAction::COPY(),
            ],
            self::CONTROLLINGTHEROLLERSHUTTER => [
                ChannelFunctionAction::REVEAL(),
                ChannelFunctionAction::SHUT(),
                ChannelFunctionAction::REVEAL_PARTIALLY(),
                ChannelFunctionAction::SHUT_PARTIALLY(),
                ChannelFunctionAction::STOP(),
                ChannelFunctionAction::COPY(),
            ],
            self::CONTROLLINGTHEFACADEBLIND => [
                ChannelFunctionAction::REVEAL(),
                ChannelFunctionAction::SHUT(),
                ChannelFunctionAction::REVEAL_PARTIALLY(),
                ChannelFunctionAction::SHUT_PARTIALLY(),
                ChannelFunctionAction::STOP(),
                ChannelFunctionAction::COPY(),
            ],
            self::PROJECTOR_SCREEN => [
                ChannelFunctionAction::REVEAL()->withFunctionCaption(self::PROJECTOR_SCREEN),
                ChannelFunctionAction::SHUT()->withFunctionCaption(self::PROJECTOR_SCREEN),
                ChannelFunctionAction::REVEAL_PARTIALLY()->withFunctionCaption(self::PROJECTOR_SCREEN),
                ChannelFunctionAction::SHUT_PARTIALLY()->withFunctionCaption(self::PROJECTOR_SCREEN),
                ChannelFunctionAction::STOP(),
                ChannelFunctionAction::COPY(),
            ],
            self::ROLLER_GARAGE_DOOR => [
                ChannelFunctionAction::REVEAL()->withFunctionCaption(self::ROLLER_GARAGE_DOOR),
                ChannelFunctionAction::SHUT()->withFunctionCaption(self::ROLLER_GARAGE_DOOR),
                ChannelFunctionAction::REVEAL_PARTIALLY()->withFunctionCaption(self::ROLLER_GARAGE_DOOR),
                ChannelFunctionAction::SHUT_PARTIALLY()->withFunctionCaption(self::ROLLER_GARAGE_DOOR),
                ChannelFunctionAction::STOP(),
                ChannelFunctionAction::COPY(),
            ],
            self::CONTROLLINGTHEROOFWINDOW => [
                ChannelFunctionAction::REVEAL(),
                ChannelFunctionAction::SHUT(),
                ChannelFunctionAction::REVEAL_PARTIALLY(),
                ChannelFunctionAction::SHUT_PARTIALLY(),
                ChannelFunctionAction::STOP(),
                ChannelFunctionAction::COPY(),
            ],
            self::POWERSWITCH => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::TOGGLE(),
                ChannelFunctionAction::COPY(),
            ],
            self::LIGHTSWITCH => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::TOGGLE(),
                ChannelFunctionAction::COPY(),
            ],
            self::STAIRCASETIMER => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::TOGGLE(),
                ChannelFunctionAction::COPY(),
            ],
            self::DIMMER => [
                ChannelFunctionAction::SET_RGBW_PARAMETERS(),
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::TOGGLE(),
                ChannelFunctionAction::COPY(),
            ],
            self::RGBLIGHTING => [
                ChannelFunctionAction::SET_RGBW_PARAMETERS(),
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::TOGGLE(),
                ChannelFunctionAction::COPY(),
            ],
            self::DIMMERANDRGBLIGHTING => [
                ChannelFunctionAction::SET_RGBW_PARAMETERS(),
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::TOGGLE(),
                ChannelFunctionAction::COPY(),
            ],
            self::THERMOSTAT => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::TOGGLE(),
                ChannelFunctionAction::HVAC_SET_TEMPERATURE(),
            ],
            self::THERMOSTATHEATPOLHOMEPLUS => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::TOGGLE(),
                ChannelFunctionAction::HVAC_SWITCH_TO_PROGRAM_MODE(),
                ChannelFunctionAction::HVAC_SWITCH_TO_MANUAL_MODE(),
                ChannelFunctionAction::HVAC_SET_TEMPERATURE(),
            ],
            self::HVAC_THERMOSTAT => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::HVAC_SWITCH_TO_PROGRAM_MODE(),
                ChannelFunctionAction::HVAC_SWITCH_TO_MANUAL_MODE(),
                ChannelFunctionAction::HVAC_SET_TEMPERATURE(),
            ],
            self::HVAC_THERMOSTAT_HEAT_COOL => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::HVAC_SWITCH_TO_PROGRAM_MODE(),
                ChannelFunctionAction::HVAC_SWITCH_TO_MANUAL_MODE(),
                ChannelFunctionAction::HVAC_SET_TEMPERATURES(),
            ],
            self::HVAC_DRYER => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::HVAC_SWITCH_TO_PROGRAM_MODE(),
                ChannelFunctionAction::HVAC_SWITCH_TO_MANUAL_MODE(),
                ChannelFunctionAction::HVAC_SET_TEMPERATURE(),
            ],
            self::HVAC_FAN => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::HVAC_SWITCH_TO_PROGRAM_MODE(),
                ChannelFunctionAction::HVAC_SWITCH_TO_MANUAL_MODE(),
                ChannelFunctionAction::HVAC_SET_TEMPERATURE(),
            ],
            self::HVAC_THERMOSTAT_DIFFERENTIAL => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::HVAC_SWITCH_TO_PROGRAM_MODE(),
                ChannelFunctionAction::HVAC_SWITCH_TO_MANUAL_MODE(),
                ChannelFunctionAction::HVAC_SET_TEMPERATURE(),
            ],
            self::HVAC_DOMESTIC_HOT_WATER => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::HVAC_SWITCH_TO_PROGRAM_MODE(),
                ChannelFunctionAction::HVAC_SWITCH_TO_MANUAL_MODE(),
                ChannelFunctionAction::HVAC_SET_TEMPERATURE(),
            ],
            self::VALVEOPENCLOSE => [
                ChannelFunctionAction::OPEN(),
                ChannelFunctionAction::CLOSE(),
            ],
            self::VALVEPERCENTAGE => [
//                ChannelFunctionAction::OPEN_PARTIALLY(),
            ],
            self::SCENE => [
                ChannelFunctionAction::EXECUTE(),
                ChannelFunctionAction::INTERRUPT(),
                ChannelFunctionAction::INTERRUPT_AND_EXECUTE(),
            ],
            self::SCHEDULE => [
                ChannelFunctionAction::ENABLE(),
                ChannelFunctionAction::DISABLE(),
            ],
            self::NOTIFICATION => [
                ChannelFunctionAction::SEND(),
            ],
            self::DIGIGLASS_HORIZONTAL => [
                ChannelFunctionAction::SET(),
            ],
            self::DIGIGLASS_VERTICAL => [
                ChannelFunctionAction::SET(),
            ],
        ];
        $actions[self::TERRACE_AWNING] = $actions[self::PROJECTOR_SCREEN];
        $actions[self::CURTAIN] = $actions[self::CONTROLLINGTHEROLLERSHUTTER];
        $actions[self::VERTICAL_BLIND] = $actions[self::CONTROLLINGTHEFACADEBLIND];
        return $actions;
    }

    public static function captions(): array {
        return [
            self::UNSUPPORTED => 'Unsupported function', // i18n
            self::NONE => 'None', // i18n
            self::SCENE => 'Scene', // i18n
            self::SCHEDULE => 'Schedule', // i18n
            self::NOTIFICATION => 'Notification', // i18n
            self::CONTROLLINGTHEGATEWAYLOCK => 'Gateway lock operation', // i18n
            self::CONTROLLINGTHEGATE => 'Gate operation', // i18n
            self::CONTROLLINGTHEGARAGEDOOR => 'Garage door operation', // i18n
            self::THERMOMETER => 'Thermometer', // i18n
            self::OPENINGSENSOR_GATEWAY => 'Gateway opening sensor', // i18n
            self::OPENINGSENSOR_GATE => 'Gate opening sensor',  // i18n
            self::OPENINGSENSOR_GARAGEDOOR => 'Garage door opening sensor', // i18n
            self::NOLIQUIDSENSOR => 'No liquid sensor', // i18n
            self::CONTROLLINGTHEDOORLOCK => 'Door lock operation', // i18n
            self::OPENINGSENSOR_DOOR => 'Door opening sensor', // i18n
            self::CONTROLLINGTHEROLLERSHUTTER => 'Roller shutter operation', // i18n
            self::CONTROLLINGTHEFACADEBLIND => 'Facade blind operation', // i18n
            self::CONTROLLINGTHEROOFWINDOW => 'Roof window shutter operation', // i18n
            self::OPENINGSENSOR_ROLLERSHUTTER => 'Roller shutter opening sensor', // i18n
            self::OPENINGSENSOR_ROOFWINDOW => 'Roof window opening sensor', // i18n
            self::POWERSWITCH => 'On/Off switch', // i18n
            self::LIGHTSWITCH => 'Light switch', // i18n
            self::HUMIDITY => 'Humidity sensor', // i18n
            self::HUMIDITYANDTEMPERATURE => 'Temperature and humidity sensor', // i18n
            self::DIMMER => 'Dimmer', // i18n
            self::RGBLIGHTING => 'RGB lighting', // i18n
            self::DIMMERANDRGBLIGHTING => 'Dimmer and RGB lighting', // i18n
            self::DISTANCESENSOR => 'Distance sensor', // i18n
            self::DEPTHSENSOR => 'Depth sensor', // i18n
            self::OPENINGSENSOR_WINDOW => 'Window opening sensor', // i18n
            self::HOTELCARDSENSOR => 'Hotel card sensor', // i18n
            self::ALARM_ARMAMENT_SENSOR => 'Alarm armament sensor', // i18n
            self::MAILSENSOR => 'Mail sensor', // i18n
            self::WINDSENSOR => 'Wind sensor', // i18n
            self::PRESSURESENSOR => 'Pressure sensor', // i18n
            self::RAINSENSOR => 'Rain sensor', // i18n
            self::WEIGHTSENSOR => 'Weight sensor', // i18n
            self::WEATHER_STATION => 'Weather Station', // i18n
            self::STAIRCASETIMER => 'Staircase timer', // i18n
            self::ELECTRICITYMETER => 'Electricity meter', // i18n
            self::IC_ELECTRICITYMETER => 'Electricity meter', // i18n
            self::IC_GASMETER => 'Gas meter', // i18n
            self::IC_WATERMETER => 'Water meter', // i18n
            self::IC_HEATMETER => 'Heat meter', // i18n
            self::THERMOSTAT => 'Thermostat', // i18n
            self::THERMOSTATHEATPOLHOMEPLUS => 'Home+ Heater', // i18n
            self::HVAC_THERMOSTAT => 'Thermostat', // i18n
            self::HVAC_THERMOSTAT_HEAT_COOL => 'Automatic thermostat', // i18n
            self::HVAC_DRYER => 'Dryer', // i18n
            self::HVAC_FAN => 'Fan', // i18n
            self::HVAC_THERMOSTAT_DIFFERENTIAL => 'Differential thermostat', // i18n
            self::HVAC_DOMESTIC_HOT_WATER => 'Domestic hot water', // i18n
            self::VALVEOPENCLOSE => 'Valve', // i18n
            self::VALVEPERCENTAGE => 'Valve', // i18n
            self::GENERAL_PURPOSE_MEASUREMENT => 'General purpose measurement', // i18n
            self::GENERAL_PURPOSE_METER => 'General purpose meter', // i18n
            self::ACTION_TRIGGER => 'Action trigger', // i18n
            self::DIGIGLASS_VERTICAL => 'Digi Glass Vertical', // i18n
            self::DIGIGLASS_HORIZONTAL => 'Digi Glass Horizontal', // i18n
            self::TERRACE_AWNING => 'Terrace awning', // i18n
            self::PROJECTOR_SCREEN => 'Projector screen', // i18n
            self::CURTAIN => 'Curtain', // i18n
            self::VERTICAL_BLIND => 'Vertical blind', // i18n
            self::ROLLER_GARAGE_DOOR => 'Roller garage door', // i18n
            self::PUMPSWITCH => 'Pump switch', // i18n
            self::HEATORCOLDSOURCESWITCH => 'Heat or cold source switch', // i18n
            self::CONTAINER => 'Container', // i18n
            self::SEPTIC_TANK => 'Septic tank', // i18n
            self::WATER_TANK => 'Water tank', // i18n
            self::CONTAINER_LEVEL_SENSOR => 'Container level sensor', // i18n
        ];
    }

    public static function maxAlternativeIconIndexes(): array {
        return [
            self::POWERSWITCH => 4,
            self::LIGHTSWITCH => 2,
            self::CONTROLLINGTHEGATE => 2,
            self::OPENINGSENSOR_GATE => 2,
            self::STAIRCASETIMER => 1,
            self::THERMOSTAT => 3,
            self::THERMOSTATHEATPOLHOMEPLUS => 3,
            self::DIGIGLASS_VERTICAL => 1,
            self::DIGIGLASS_HORIZONTAL => 1,
            self::ELECTRICITYMETER => 1,
            self::SCENE => 19,
            self::GENERAL_PURPOSE_MEASUREMENT => 31,
            self::GENERAL_PURPOSE_METER => 14,
            self::THERMOMETER => 7,
            self::HUMIDITYANDTEMPERATURE => 7,
            self::ALARM_ARMAMENT_SENSOR => 3,
            self::HEATORCOLDSOURCESWITCH => 5,
        ];
    }

    public static function possibleVisualStates(): array {
        return [
            self::UNSUPPORTED => [],
            self::NONE => [],
            self::SCENE => ['default'],
            self::SCHEDULE => [],
            self::NOTIFICATION => [],
            self::CONTROLLINGTHEGATEWAYLOCK => ['opened', 'closed'],
            self::CONTROLLINGTHEGATE => ['opened', 'closed', 'partially_closed'],
            self::CONTROLLINGTHEGARAGEDOOR => ['opened', 'closed', 'partially_closed'],
            self::THERMOMETER => ['default'],
            self::OPENINGSENSOR_GATEWAY => ['opened', 'closed'],
            self::OPENINGSENSOR_GATE => ['opened', 'closed'],
            self::OPENINGSENSOR_GARAGEDOOR => ['opened', 'closed'],
            self::NOLIQUIDSENSOR => ['empty', 'full'],
            self::CONTROLLINGTHEDOORLOCK => ['opened', 'closed'],
            self::OPENINGSENSOR_DOOR => ['opened', 'closed'],
            self::CONTROLLINGTHEROLLERSHUTTER => ['revealed', 'shut'],
            self::CONTROLLINGTHEFACADEBLIND => ['revealed', 'shut'],
            self::CONTROLLINGTHEROOFWINDOW => ['revealed', 'shut'],
            self::OPENINGSENSOR_ROLLERSHUTTER => ['revealed', 'shut'],
            self::OPENINGSENSOR_ROOFWINDOW => ['revealed', 'shut'],
            self::POWERSWITCH => ['off', 'on'],
            self::LIGHTSWITCH => ['off', 'on'],
            self::HUMIDITY => ['default'],
            self::HUMIDITYANDTEMPERATURE => ['humidity', 'temperature'],
            self::DIMMER => ['off', 'on'],
            self::RGBLIGHTING => ['off', 'on'],
            self::DIMMERANDRGBLIGHTING => ['rgb_off_dim_off', 'rgb_off_dim_on', 'rgb_on_dim_off', 'rgb_on_dim_on'],
            self::DISTANCESENSOR => ['default'],
            self::DEPTHSENSOR => ['default'],
            self::OPENINGSENSOR_WINDOW => ['opened', 'closed'],
            self::HOTELCARDSENSOR => ['empty', 'full'],
            self::ALARM_ARMAMENT_SENSOR => ['empty', 'full'],
            self::MAILSENSOR => ['empty', 'full'],
            self::WINDSENSOR => ['default'],
            self::PRESSURESENSOR => ['default'],
            self::RAINSENSOR => ['empty', 'full'],
            self::WEIGHTSENSOR => ['default'],
            self::WEATHER_STATION => ['default'],
            self::STAIRCASETIMER => ['off', 'on'],
            self::ELECTRICITYMETER => ['default'],
            self::IC_ELECTRICITYMETER => ['default'],
            self::IC_GASMETER => ['default'],
            self::IC_WATERMETER => ['default'],
            self::IC_HEATMETER => ['default'],
            self::THERMOSTAT => ['off', 'on'],
            self::THERMOSTATHEATPOLHOMEPLUS => ['off', 'on'],
            self::HVAC_THERMOSTAT => ['heating', 'cooling'],
            self::HVAC_THERMOSTAT_HEAT_COOL => ['default'],
            self::HVAC_DRYER => ['default'],
            self::HVAC_FAN => ['default'],
            self::HVAC_THERMOSTAT_DIFFERENTIAL => ['default'],
            self::HVAC_DOMESTIC_HOT_WATER => ['default'],
            self::VALVEOPENCLOSE => ['opened', 'closed'],
            self::VALVEPERCENTAGE => ['opened', 'closed'],
            self::GENERAL_PURPOSE_MEASUREMENT => ['default'],
            self::GENERAL_PURPOSE_METER => ['default'],
            self::ACTION_TRIGGER => ['default'],
            self::DIGIGLASS_VERTICAL => ['revealed', 'shut'],
            self::DIGIGLASS_HORIZONTAL => ['revealed', 'shut'],
            self::TERRACE_AWNING => ['revealed', 'shut'],
            self::PROJECTOR_SCREEN => ['revealed', 'shut'],
            self::CURTAIN => ['revealed', 'shut'],
            self::VERTICAL_BLIND => ['revealed', 'shut'],
            self::ROLLER_GARAGE_DOOR => ['revealed', 'shut'],
            self::PUMPSWITCH => ['off', 'on'],
            self::HEATORCOLDSOURCESWITCH => ['off', 'on'],
            self::CONTAINER => ['default'],
            self::SEPTIC_TANK => ['default'],
            self::WATER_TANK => ['default'],
            self::CONTAINER_LEVEL_SENSOR => ['empty', 'full'],
        ];
    }

    /** @Groups({"basic"}) */
    public function isOutput(): bool {
        return in_array($this->value, self::outputFunctions());
    }

    /** @return int[] */
    public static function outputFunctions(): array {
        return array_values(array_unique(array_merge(array_keys(self::actions()), [
            // extra output functions here, if any
        ])));
    }

    /** @return int[] */
    public static function inputFunctions(): array {
        return array_values(array_filter(array_merge(array_diff(self::toArray(), self::outputFunctions()), [
            self::THERMOSTAT, self::THERMOSTATHEATPOLHOMEPLUS,
        ]), function ($functionId) {
            return $functionId > 0;
        }));
    }

    public static function fromString(string $functionName): ChannelFunction {
        $functionName = trim($functionName);
        if (is_numeric($functionName)) {
            if (self::isValid((int)$functionName)) {
                return new self((int)$functionName);
            }
        } else {
            $functionName = strtoupper($functionName);
            if (isset(self::$deprecatedNames[$functionName])) {
                $functionName = self::$deprecatedNames[$functionName];
            }
            if (self::isValidKey($functionName)) {
                return self::$functionName();
            }
        }
        throw new ApiException('Invalid function given: ' . $functionName, 400);
    }

    /**
     * @param string[] $functionNames
     * @return ChannelFunction[]
     */
    public static function fromStrings(array $functionNames): array {
        return array_map(self::class . '::fromString', $functionNames);
    }

    public static function safeInstance($functionId): self {
        $functionId = intval($functionId);
        try {
            return new self($functionId);
        } catch (UnexpectedValueException $e) {
            $function = self::UNSUPPORTED();
            $function->unsupportedFunctionId = $functionId;
            return $function;
        }
    }
}

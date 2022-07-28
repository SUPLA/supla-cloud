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
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Exception\ApiException;
use Symfony\Component\Serializer\Annotation\Groups;
use UnexpectedValueException;

/**
 * @OA\Schema(schema="ChannelFunctionEnumNames", type="string", example="OPENINGSENSOR_GATE", enum={"UNSUPPORTED","NONE","SCENE","CONTROLLINGTHEGATEWAYLOCK","CONTROLLINGTHEGATE","CONTROLLINGTHEGARAGEDOOR","THERMOMETER","HUMIDITY","HUMIDITYANDTEMPERATURE","OPENINGSENSOR_GATEWAY","OPENINGSENSOR_GATE","OPENINGSENSOR_GARAGEDOOR","NOLIQUIDSENSOR","CONTROLLINGTHEDOORLOCK","OPENINGSENSOR_DOOR","CONTROLLINGTHEROLLERSHUTTER","CONTROLLINGTHEROOFWINDOW","OPENINGSENSOR_ROLLERSHUTTER","OPENINGSENSOR_ROOFWINDOW","POWERSWITCH","LIGHTSWITCH","DIMMER","RGBLIGHTING","DIMMERANDRGBLIGHTING","DEPTHSENSOR","DISTANCESENSOR","OPENINGSENSOR_WINDOW","MAILSENSOR","WINDSENSOR","PRESSURESENSOR","RAINSENSOR","WEIGHTSENSOR","WEATHER_STATION","STAIRCASETIMER","ELECTRICITYMETER","IC_ELECTRICITYMETER","IC_GASMETER","IC_WATERMETER","IC_HEATMETER","THERMOSTAT","THERMOSTATHEATPOLHOMEPLUS","VALVEOPENCLOSE","VALVEPERCENTAGE","GENERAL_PURPOSE_MEASUREMENT","ACTION_TRIGGER","DIGIGLASS_HORIZONTAL","DIGIGLASS_VERTICAL"})
 * @OA\Schema(
 *   schema="ChannelFunction", type="object",
 *   @OA\Property(property="id", type="integer", example=60, enum={-1,0,2000,10,20,30,40,42,45,50,60,70,80,90,100,110,115,120,125,130,140,180,190,200,210,220,230,240,250,260,270,280,290,300,310,315,320,330,340,400,410,500,510,520,700,800,810}),
 *   @OA\Property(property="name", ref="#/components/schemas/ChannelFunctionEnumNames"),
 *   @OA\Property(property="caption", type="string", example="Gate opening sensor"),
 *   @OA\Property(property="maxAlternativeIconIndex", type="integer"),
 *   @OA\Property(property="possibleVisualStates", type="array", description="Possible visual states of channel with this function. Ordered.", @OA\Items(type="string", enum={"default","opened","closed","partially_closed","empty","full","revealed","shut","off","on","humidity","temperature","rgb_off_dim_off","rgb_off_dim_on","rgb_on_dim_off","rgb_on_dim_on"})),
 *   @OA\Property(property="output", type="boolean", description="Whether the function is output type (i.e. can execute action) or input (i.e. provides data)", example=false),
 * )
 *
 * @method static ChannelFunction UNSUPPORTED()
 * @method static ChannelFunction NONE()
 * @method static ChannelFunction SCENE()
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
 * @method static ChannelFunction VALVEOPENCLOSE()
 * @method static ChannelFunction VALVEPERCENTAGE()
 * @method static ChannelFunction GENERAL_PURPOSE_MEASUREMENT()
 * @method static ChannelFunction ACTION_TRIGGER()
 * @method static ChannelFunction DIGIGLASS_VERTICAL()
 * @method static ChannelFunction DIGIGLASS_HORIZONTAL()
 */
final class ChannelFunction extends Enum {
    const UNSUPPORTED = -1;
    const NONE = 0;
    const SCENE = 2000;
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
    const VALVEOPENCLOSE = 500;
    const VALVEPERCENTAGE = 510;
    const GENERAL_PURPOSE_MEASUREMENT = 520;
    const ACTION_TRIGGER = 700;
    const DIGIGLASS_HORIZONTAL = 800;
    const DIGIGLASS_VERTICAL = 810;

    private $unsupportedFunctionId;

    private static $deprecatedNames = [
        'GASMETER' => 'IC_GASMETER',
        'WATERMETER' => 'IC_WATERMETER',
        'HEATMETER' => 'IC_HEATMETER',
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
        if (in_array($type->getId(), [ChannelType::RELAY, ChannelType::BRIDGE])) {
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
        return [
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
                ChannelFunctionAction::SHUT(),
                ChannelFunctionAction::REVEAL(),
                ChannelFunctionAction::REVEAL_PARTIALLY(),
                ChannelFunctionAction::SHUT_PARTIALLY(),
                ChannelFunctionAction::STOP(),
                ChannelFunctionAction::COPY(),
            ],
            self::CONTROLLINGTHEROOFWINDOW => [
                ChannelFunctionAction::SHUT(),
                ChannelFunctionAction::REVEAL(),
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
            ],
            self::THERMOSTATHEATPOLHOMEPLUS => [
                ChannelFunctionAction::TURN_ON(),
                ChannelFunctionAction::TURN_OFF(),
                ChannelFunctionAction::TOGGLE(),
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
            self::DIGIGLASS_HORIZONTAL => [
                ChannelFunctionAction::SET(),
            ],
            self::DIGIGLASS_VERTICAL => [
                ChannelFunctionAction::SET(),
            ],
        ];
    }

    public static function captions(): array {
        return [
            self::UNSUPPORTED => 'Unsupported function', // i18n
            self::NONE => 'None', // i18n
            self::SCENE => 'Scene', // i18n
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
            self::VALVEOPENCLOSE => 'Valve', // i18n
            self::VALVEPERCENTAGE => 'Valve', // i18n
            self::GENERAL_PURPOSE_MEASUREMENT => 'General purpose measurement', // i18n
            self::ACTION_TRIGGER => 'Action trigger', // i18n
            self::DIGIGLASS_VERTICAL => 'Digi Glass Vertical', // i18n
            self::DIGIGLASS_HORIZONTAL => 'Digi Glass Horizontal', // i18n
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
        ];
    }

    public static function possibleVisualStates(): array {
        return [
            self::UNSUPPORTED => [],
            self::NONE => [],
            self::SCENE => ['default'],
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
            self::VALVEOPENCLOSE => ['opened', 'closed'],
            self::VALVEPERCENTAGE => ['opened', 'closed'],
            self::GENERAL_PURPOSE_MEASUREMENT => ['default'],
            self::ACTION_TRIGGER => ['default'],
            self::DIGIGLASS_VERTICAL => ['revealed', 'shut'],
            self::DIGIGLASS_HORIZONTAL => ['revealed', 'shut'],
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

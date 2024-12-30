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
use SuplaBundle\Exception\ApiException;
use Symfony\Component\Serializer\Annotation\Groups;
use UnexpectedValueException;

/**
 * @see https://github.com/SUPLA/supla-core/blob/develop/supla-common/proto.h#L405
 *
 * @OA\Schema(schema="ChannelTypeEnumNames", type="string", example="SENSORNO")
 * @OA\Schema(
 *   schema="ChannelType", type="object",
 *   @OA\Property(property="id", type="integer", example=1000),
 *   @OA\Property(property="name", ref="#/components/schemas/ChannelTypeEnumNames"),
 *   @OA\Property(property="caption", type="string", example="Sensor (normal open)"),
 * )
 *
 * @method static ChannelType UNSUPPORTED()
 * @method static ChannelType SENSORNO()
 * @method static ChannelType SENSORNC()
 * @method static ChannelType DISTANCESENSOR()
 * @method static ChannelType CALLBUTTON()
 * @method static ChannelType RELAYHFD4()
 * @method static ChannelType RELAYG5LA1A()
 * @method static ChannelType RELAY2XG5LA1A()
 * @method static ChannelType RELAY()
 * @method static ChannelType THERMOMETERDS18B20()
 * @method static ChannelType DHT11()
 * @method static ChannelType DHT21()
 * @method static ChannelType DHT22()
 * @method static ChannelType AM2301()
 * @method static ChannelType AM2302()
 * @method static ChannelType THERMOMETER()
 * @method static ChannelType HUMIDITYSENSOR()
 * @method static ChannelType HUMIDITYANDTEMPSENSOR()
 * @method static ChannelType WINDSENSOR()
 * @method static ChannelType PRESSURESENSOR()
 * @method static ChannelType RAINSENSOR()
 * @method static ChannelType WEIGHTSENSOR()
 * @method static ChannelType WEATHER_STATION()
 * @method static ChannelType CONTAINER()
 * @method static ChannelType DIMMER()
 * @method static ChannelType RGBLEDCONTROLLER()
 * @method static ChannelType DIMMERANDRGBLED()
 * @method static ChannelType ELECTRICITYMETER()
 * @method static ChannelType IMPULSECOUNTER()
 * @method static ChannelType THERMOSTAT()
 * @method static ChannelType THERMOSTATHEATPOLHOMEPLUS()
 * @method static ChannelType HVAC()
 * @method static ChannelType VALVEOPENCLOSE()
 * @method static ChannelType VALVEPERCENTAGE()
 * @method static ChannelType BRIDGE()
 * @method static ChannelType GENERAL_PURPOSE_MEASUREMENT()
 * @method static ChannelType GENERAL_PURPOSE_METER()
 * @method static ChannelType ACTION_TRIGGER()
 * @method static ChannelType DIGIGLASS()
 */
final class ChannelType extends Enum {
    const UNSUPPORTED = -1;
    const SENSORNO = 1000;
    const SENSORNC = 1010;
    const DISTANCESENSOR = 1020;
    const CALLBUTTON = 1500;
    const RELAYHFD4 = 2000;
    const RELAYG5LA1A = 2010;
    const RELAY2XG5LA1A = 2020;
    const RELAY = 2900;
    const THERMOMETERDS18B20 = 3000;
    const DHT11 = 3010;
    const DHT21 = 3022;
    const DHT22 = 3020;
    const AM2301 = 3032;
    const AM2302 = 3030;
    const THERMOMETER = 3034;
    const HUMIDITYSENSOR = 3036;
    const HUMIDITYANDTEMPSENSOR = 3038;
    const WINDSENSOR = 3042;
    const PRESSURESENSOR = 3044;
    const RAINSENSOR = 3048;
    const WEIGHTSENSOR = 3050;
    const WEATHER_STATION = 3100;
    const CONTAINER = 3200;
    const DIMMER = 4000;
    const RGBLEDCONTROLLER = 4010;
    const DIMMERANDRGBLED = 4020;
    const ELECTRICITYMETER = 5000;
    const IMPULSECOUNTER = 5010;
    const THERMOSTAT = 6000;
    const THERMOSTATHEATPOLHOMEPLUS = 6010;
    const HVAC = 6100;
    const VALVEOPENCLOSE = 7000;
    const VALVEPERCENTAGE = 7010;
    const BRIDGE = 8000;
    const GENERAL_PURPOSE_MEASUREMENT = 9000;
    const GENERAL_PURPOSE_METER = 9010;
    const ACTION_TRIGGER = 11000;
    const DIGIGLASS = 12000;

    private $unsupportedTypeId;

    /** @Groups({"basic"}) */
    public function getId(): int {
        return $this->value == self::UNSUPPORTED ? ($this->unsupportedTypeId ?: 0) : $this->value;
    }

    /** @Groups({"basic"}) */
    public function getName(): string {
        return $this->getKey();
    }

    /** @Groups({"basic"}) */
    public function getCaption(): string {
        return self::captions()[$this->value] ?? 'None';
    }

    public static function captions(): array {
        return [
            self::UNSUPPORTED => 'Unsupported type', // i18n
            self::SENSORNO => 'Binary sensor', // i18n
            self::SENSORNC => 'Binary sensor', // i18n
            self::RELAY => 'Relay', // i18n
            self::RELAYHFD4 => 'HFD4 Relay', // i18n
            self::RELAYG5LA1A => 'G5LA1A Relay', // i18n
            self::RELAY2XG5LA1A => 'G5LA1A Relay x2', // i18n
            self::THERMOMETERDS18B20 => 'DS18B20 Thermometer', // i18n
            self::DHT11 => 'DHT11 Temperature & Humidity Sensor', // i18n
            self::DHT21 => 'DHT21 Temperature & Humidity Sensor', // i18n
            self::DHT22 => 'DHT22 Temperature & Humidity Sensor', // i18n
            self::AM2301 => 'AM2301 Temperature & Humidity Sensor', // i18n
            self::AM2302 => 'AM2302 Temperature & Humidity Sensor', // i18n
            self::THERMOMETER => 'Temperature Sensor', // i18n
            self::HUMIDITYSENSOR => 'Humidity Sensor', // i18n
            self::HUMIDITYANDTEMPSENSOR => 'Temperature & Humidity Sensor', // i18n
            self::WINDSENSOR => 'Wind sensor', // i18n
            self::PRESSURESENSOR => 'Pressure sensor', // i18n
            self::RAINSENSOR => 'Rain sensor', // i18n
            self::WEIGHTSENSOR => 'Weight sensor', // i18n
            self::WEATHER_STATION => 'Weather Station', // i18n
            self::CONTAINER => 'Container', // i18n
            self::DIMMER => 'Dimmer', // i18n
            self::RGBLEDCONTROLLER => 'RGB led controller', // i18n
            self::DIMMERANDRGBLED => 'Dimmer & RGB led controller', // i18n
            self::DISTANCESENSOR => 'Distance sensor', // i18n
            self::CALLBUTTON => 'Distance sensor', // i18n
            self::ELECTRICITYMETER => 'Electricity meter', // i18n
            self::IMPULSECOUNTER => 'Impulse counter', // i18n
            self::THERMOSTAT => 'Thermostat', // i18n
            self::THERMOSTATHEATPOLHOMEPLUS => 'Home+ Heater', // i18n
            self::HVAC => 'Heating / ventilation / air conditioning', // i18n
            self::VALVEOPENCLOSE => 'Valve', // i18n
            self::VALVEPERCENTAGE => 'Valve', // i18n
            self::BRIDGE => 'Bridge', // i18n
            self::GENERAL_PURPOSE_MEASUREMENT => 'General purpose measurement', // i18n
            self::GENERAL_PURPOSE_METER => 'General purpose meter', // i18n
            self::ACTION_TRIGGER => 'Action trigger', // i18n
            self::DIGIGLASS => 'Digi Glass', // i18n
        ];
    }

    /** @return ChannelFunction[][] */
    public static function functions(): array {
        $map = [
            self::UNSUPPORTED => [],
            self::SENSORNO => [
                ChannelFunction::OPENINGSENSOR_GATEWAY(),
                ChannelFunction::OPENINGSENSOR_GATE(),
                ChannelFunction::OPENINGSENSOR_GARAGEDOOR(),
                ChannelFunction::OPENINGSENSOR_DOOR(),
                ChannelFunction::NOLIQUIDSENSOR(),
                ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER(),
                ChannelFunction::OPENINGSENSOR_ROOFWINDOW(),
                ChannelFunction::OPENINGSENSOR_WINDOW(),
                ChannelFunction::HOTELCARDSENSOR(),
                ChannelFunction::ALARM_ARMAMENT_SENSOR(),
                ChannelFunction::MAILSENSOR(),
                ChannelFunction::CONTAINER_LEVEL_SENSOR(),
            ],
            self::RELAYHFD4 => [
                ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(),
                ChannelFunction::CONTROLLINGTHEGATE(),
                ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
                ChannelFunction::CONTROLLINGTHEDOORLOCK(),
            ],
            self::RELAYG5LA1A => [
                ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(),
                ChannelFunction::CONTROLLINGTHEGATE(),
                ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
                ChannelFunction::CONTROLLINGTHEDOORLOCK(),
                ChannelFunction::POWERSWITCH(),
                ChannelFunction::LIGHTSWITCH(),
                ChannelFunction::STAIRCASETIMER(),
            ],
            self::RELAY2XG5LA1A => [
                ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(),
                ChannelFunction::CONTROLLINGTHEGATE(),
                ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
                ChannelFunction::CONTROLLINGTHEDOORLOCK(),
                ChannelFunction::POWERSWITCH(),
                ChannelFunction::LIGHTSWITCH(),
                ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(),
                ChannelFunction::CONTROLLINGTHEROOFWINDOW(),
                ChannelFunction::STAIRCASETIMER(),
                ChannelFunction::CONTROLLINGTHEFACADEBLIND(),
                ChannelFunction::TERRACE_AWNING(),
                ChannelFunction::PROJECTOR_SCREEN(),
                ChannelFunction::CURTAIN(),
                ChannelFunction::VERTICAL_BLIND(),
                ChannelFunction::ROLLER_GARAGE_DOOR(),
                ChannelFunction::PUMPSWITCH(),
                ChannelFunction::HEATORCOLDSOURCESWITCH(),
            ],
            self::THERMOMETERDS18B20 => [ChannelFunction::THERMOMETER()],
            self::DIMMER => [ChannelFunction::DIMMER()],
            self::RGBLEDCONTROLLER => [ChannelFunction::RGBLIGHTING()],
            self::DIMMERANDRGBLED => [ChannelFunction::DIMMERANDRGBLIGHTING()],
            self::DISTANCESENSOR => [
                ChannelFunction::DEPTHSENSOR(),
                ChannelFunction::DISTANCESENSOR(),
            ],
            self::THERMOMETER => [ChannelFunction::THERMOMETER()],
            self::HUMIDITYSENSOR => [ChannelFunction::HUMIDITY()],
            self::HUMIDITYANDTEMPSENSOR => [ChannelFunction::HUMIDITYANDTEMPERATURE()],
            self::WINDSENSOR => [ChannelFunction::WINDSENSOR()],
            self::PRESSURESENSOR => [ChannelFunction::PRESSURESENSOR()],
            self::RAINSENSOR => [ChannelFunction::RAINSENSOR()],
            self::WEIGHTSENSOR => [ChannelFunction::WEIGHTSENSOR()],
            self::WEATHER_STATION => [ChannelFunction::WEATHER_STATION()],
            self::CALLBUTTON => [],
            self::ELECTRICITYMETER => [ChannelFunction::ELECTRICITYMETER()],
            self::IMPULSECOUNTER => [
                ChannelFunction::IC_ELECTRICITYMETER(),
                ChannelFunction::IC_GASMETER(),
                ChannelFunction::IC_WATERMETER(),
                ChannelFunction::IC_HEATMETER(),
            ],
            self::THERMOSTAT => [ChannelFunction::THERMOSTAT()],
            self::THERMOSTATHEATPOLHOMEPLUS => [ChannelFunction::THERMOSTATHEATPOLHOMEPLUS()],
            self::HVAC => [
                ChannelFunction::HVAC_THERMOSTAT(),
                ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(),
                ChannelFunction::HVAC_DRYER(),
                ChannelFunction::HVAC_FAN(),
                ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
                ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
            ],
            self::VALVEOPENCLOSE => [ChannelFunction::VALVEOPENCLOSE()],
            self::VALVEPERCENTAGE => [ChannelFunction::VALVEPERCENTAGE()],
            self::GENERAL_PURPOSE_MEASUREMENT => [ChannelFunction::GENERAL_PURPOSE_MEASUREMENT()],
            self::GENERAL_PURPOSE_METER => [ChannelFunction::GENERAL_PURPOSE_METER()],
            self::ACTION_TRIGGER => [ChannelFunction::ACTION_TRIGGER()],
            self::DIGIGLASS => [ChannelFunction::DIGIGLASS_VERTICAL(), ChannelFunction::DIGIGLASS_HORIZONTAL()],
            self::CONTAINER => [
                ChannelFunction::CONTAINER(),
                ChannelFunction::SEPTIC_TANK(),
                ChannelFunction::WATER_TANK(),
            ],
        ];
        $map[self::SENSORNC] = $map[self::SENSORNO];
        foreach ([self::DHT11, self::DHT21, self::DHT22, self::AM2301, self::AM2302] as $humidityAndTemperatureType) {
            $map[$humidityAndTemperatureType] = $map[self::HUMIDITYANDTEMPSENSOR];
        }
        return $map;
    }

    public static function safeInstance($typeId): self {
        $typeId = intval($typeId);
        try {
            return new self($typeId);
        } catch (UnexpectedValueException $e) {
            $type = self::UNSUPPORTED();
            $type->unsupportedTypeId = $typeId;
            return $type;
        }
    }

    public static function fromString(string $typeName): ChannelType {
        $typeName = trim($typeName);
        if (is_numeric($typeName)) {
            if (self::isValid((int)$typeName)) {
                return new self((int)$typeName);
            }
        } else {
            $typeName = strtoupper($typeName);
            if (self::isValidKey($typeName)) {
                return self::$typeName();
            }
        }
        throw new ApiException('Invalid type given: ' . $typeName, 400);
    }

    /**
     * @param string[] $functionNames
     * @return ChannelType[]
     */
    public static function fromStrings(array $functionNames): array {
        return array_map(self::class . '::fromString', $functionNames);
    }
}

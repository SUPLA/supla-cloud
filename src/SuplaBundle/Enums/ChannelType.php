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
use Symfony\Component\Serializer\Annotation\Groups;
use UnexpectedValueException;

/**
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
 * @method static ChannelType DIMMER()
 * @method static ChannelType RGBLEDCONTROLLER()
 * @method static ChannelType DIMMERANDRGBLED()
 * @method static ChannelType ELECTRICITYMETER()
 * @method static ChannelType IMPULSECOUNTER()
 * @method static ChannelType THERMOSTAT()
 * @method static ChannelType THERMOSTATHEATPOLHOMEPLUS()
 * @method static ChannelType GENERAL_PURPOSE_MEASUREMENT()
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
    const DIMMER = 4000;
    const RGBLEDCONTROLLER = 4010;
    const DIMMERANDRGBLED = 4020;
    const ELECTRICITYMETER = 5000;
    const IMPULSECOUNTER = 5010;
    const THERMOSTAT = 6000;
    const THERMOSTATHEATPOLHOMEPLUS = 6010;
    const GENERAL_PURPOSE_MEASUREMENT = 9000;

    private $unsupportedTypeId;

    /** @Groups({"basic"}) */
    public function getId(): int {
        return $this->value == self::UNSUPPORTED ? $this->unsupportedTypeId : $this->value;
    }

    /** @Groups({"basic"}) */
    public function getName(): string {
        return $this->getKey();
    }

    /** @Groups({"basic"}) */
    public function getCaption(): string {
        return self::captions()[$this->value] ?? 'None';
    }

    /** @Groups({"basic"}) */
    public function isOutput(): bool {
        return in_array($this->value, self::outputTypes());
    }

    /** @return int[] */
    public static function outputTypes(): array {
        return [
            self::DIMMER,
            self::RGBLEDCONTROLLER,
            self::DIMMERANDRGBLED,
            self::RELAY,
            self::RELAYG5LA1A,
            self::RELAY2XG5LA1A,
            self::RELAYHFD4,
            self::THERMOSTAT,
            self::THERMOSTATHEATPOLHOMEPLUS,
        ];
    }

    /** @return int[] */
    public static function inputTypes(): array {
        return array_merge(array_diff(self::toArray(), self::outputTypes()), [self::THERMOSTAT, self::THERMOSTATHEATPOLHOMEPLUS]);
    }

    public static function captions(): array {
        return [
            self::UNSUPPORTED => 'Unsupported type', // i18n
            self::SENSORNO => 'Sensor (normal open)', // i18n
            self::SENSORNC => 'Sensor (normal closed)', // i18n
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
            self::DIMMER => 'Dimmer', // i18n
            self::RGBLEDCONTROLLER => 'RGB led controller', // i18n
            self::DIMMERANDRGBLED => 'Dimmer & RGB led controller', // i18n
            self::DISTANCESENSOR => 'Distance sensor', // i18n
            self::CALLBUTTON => 'Distance sensor', // i18n
            self::ELECTRICITYMETER => 'Electricity meter', // i18n
            self::IMPULSECOUNTER => 'Impulse counter', // i18n
            self::THERMOSTAT => 'Thermostat', // i18n
            self::THERMOSTATHEATPOLHOMEPLUS => 'Home+ Heater', // i18n
            self::GENERAL_PURPOSE_MEASUREMENT => 'General purpose measurement', // i18n
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
                ChannelFunction::OPENINGSENSOR_WINDOW(),
                ChannelFunction::MAILSENSOR(),
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
                ChannelFunction::STAIRCASETIMER(),
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
            ],
            self::THERMOSTAT => [ChannelFunction::THERMOSTAT()],
            self::THERMOSTATHEATPOLHOMEPLUS => [ChannelFunction::THERMOSTATHEATPOLHOMEPLUS()],
            self::GENERAL_PURPOSE_MEASUREMENT => [ChannelFunction::GENERAL_PURPOSE_MEASUREMENT()],
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
}

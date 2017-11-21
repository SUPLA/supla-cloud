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

/**
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
 */
final class ChannelType extends Enum {
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
}

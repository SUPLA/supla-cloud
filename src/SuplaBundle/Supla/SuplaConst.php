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

namespace SuplaBundle\Supla;

use SuplaBundle\Enums\ChannelFunction;

class SuplaConst {
    const TYPE_SENSORNO = 1000;
    const TYPE_SENSORNC = 1010;
    const TYPE_DISTANCESENSOR = 1020;
    const TYPE_CALLBUTTON = 1500;
    const TYPE_RELAYHFD4 = 2000;
    const TYPE_RELAYG5LA1A = 2010;
    const TYPE_2XRELAYG5LA1A = 2020;
    const TYPE_RELAY = 2900;
    const TYPE_THERMOMETERDS18B20 = 3000;
    const TYPE_DHT11 = 3010;
    const TYPE_DHT21 = 3022;
    const TYPE_DHT22 = 3020;
    const TYPE_AM2301 = 3032;
    const TYPE_AM2302 = 3030;
    const TYPE_THERMOMETER = 3034;
    const TYPE_HUMIDITYSENSOR = 3036;
    const TYPE_HUMIDITYANDTEMPSENSOR = 3038;
    const TYPE_WINDSENSOR = 3042;
    const TYPE_PRESSURESENSOR = 3044;
    const TYPE_RAINSENSOR = 3048;
    const TYPE_WEIGHTSENSOR = 3050;
    const TYPE_WEATHER_STATION = 3100;

    const TYPE_DIMMER = 4000;
    const TYPE_RGBLEDCONTROLLER = 4010;
    const TYPE_DIMMERANDRGBLED = 4020;

    const BIT_RELAYFNC_CONTROLLINGTHEGATEWAYLOCK = 0x00000001;
    const BIT_RELAYFNC_CONTROLLINGTHEGATE = 0x00000002;
    const BIT_RELAYFNC_CONTROLLINGTHEGARAGEDOOR = 0x00000004;
    const BIT_RELAYFNC_CONTROLLINGTHEDOORLOCK = 0x00000008;
    const BIT_RELAYFNC_CONTROLLINGTHEROLLERSHUTTER = 0x00000010;
    const BIT_RELAYFNC_POWERSWITCH = 0x00000020;
    const BIT_RELAYFNC_LIGHTSWITCH = 0x00000040;
    const BIT_RELAYFNC_STAIRCASETIMER = 0x00000080;

    const FNC_NONE = ChannelFunction::NONE;
    const FNC_CONTROLLINGTHEGATEWAYLOCK = ChannelFunction::CONTROLLINGTHEGATEWAYLOCK;
    const FNC_CONTROLLINGTHEGATE = ChannelFunction::CONTROLLINGTHEGATE;
    const FNC_CONTROLLINGTHEGARAGEDOOR = ChannelFunction::CONTROLLINGTHEGARAGEDOOR;
    const FNC_THERMOMETER = ChannelFunction::THERMOMETER;
    const FNC_HUMIDITY = ChannelFunction::HUMIDITY;
    const FNC_HUMIDITYANDTEMPERATURE = ChannelFunction::HUMIDITYANDTEMPERATURE;
    const FNC_OPENINGSENSOR_GATEWAY = ChannelFunction::OPENINGSENSOR_GATEWAY;
    const FNC_OPENINGSENSOR_GATE = ChannelFunction::OPENINGSENSOR_GATE;
    const FNC_OPENINGSENSOR_GARAGEDOOR = ChannelFunction::OPENINGSENSOR_GARAGEDOOR;
    const FNC_NOLIQUIDSENSOR = ChannelFunction::NOLIQUIDSENSOR;
    const FNC_CONTROLLINGTHEDOORLOCK = ChannelFunction::CONTROLLINGTHEDOORLOCK;
    const FNC_OPENINGSENSOR_DOOR = ChannelFunction::OPENINGSENSOR_DOOR;
    const FNC_CONTROLLINGTHEROLLERSHUTTER = ChannelFunction::CONTROLLINGTHEROLLERSHUTTER;
    const FNC_OPENINGSENSOR_ROLLERSHUTTER = ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER;
    const FNC_POWERSWITCH = ChannelFunction::POWERSWITCH;
    const FNC_LIGHTSWITCH = ChannelFunction::LIGHTSWITCH;
    const FNC_DIMMER = ChannelFunction::DIMMER;
    const FNC_RGBLIGHTING = ChannelFunction::RGBLIGHTING;
    const FNC_DIMMERANDRGBLIGHTING = ChannelFunction::DIMMERANDRGBLIGHTING;
    const FNC_DEPTHSENSOR = ChannelFunction::DEPTHSENSOR;
    const FNC_DISTANCESENSOR = ChannelFunction::DISTANCESENSOR;
    const FNC_OPENINGSENSOR_WINDOW = ChannelFunction::OPENINGSENSOR_WINDOW;
    const FNC_MAILSENSOR = ChannelFunction::MAILSENSOR;
    const FNC_WINDSENSOR = ChannelFunction::WINDSENSOR;
    const FNC_PRESSURESENSOR = ChannelFunction::PRESSURESENSOR;
    const FNC_RAINSENSOR = ChannelFunction::RAINSENSOR;
    const FNC_WEIGHTSENSOR = ChannelFunction::WEIGHTSENSOR;
    const FNC_WEATHER_STATION = ChannelFunction::WEATHER_STATION;
    const FNC_STAIRCASETIMER = ChannelFunction::STAIRCASETIMER;

    // @codingStandardsIgnoreStart

    const typeStr = [
        1000 => 'TYPE_SENSORNO',
        1010 => 'TYPE_SENSORNC',
        1020 => 'TYPE_DISTANCESENSOR',
        1500 => 'TYPE_CALLBUTTON',
        2000 => 'TYPE_RELAYHFD4',
        2010 => 'TYPE_RELAYG5LA1A',
        2020 => 'TYPE_2XRELAYG5LA1A',
        2900 => 'TYPE_RELAY',
        3000 => 'TYPE_THERMOMETERDS18B20',
        3010 => 'TYPE_DHT11',
        3022 => 'TYPE_DHT21',
        3020 => 'TYPE_DHT22',
        3032 => 'TYPE_AM2301',
        3030 => 'TYPE_AM2302',
        3034 => 'TYPE_THERMOMETER',
        3036 => 'TYPE_HUMIDITYSENSOR',
        3038 => 'TYPE_HUMIDITYANDTEMPSENSOR',
        3042 => 'TYPE_WINDSENSOR',
        3044 => 'TYPE_PRESSURESENSOR',
        3048 => 'TYPE_RAINSENSOR',
        3050 => 'TYPE_WEIGHTSENSOR',
        3100 => 'TYPE_WEATHER_STATION',
        4000 => 'TYPE_DIMMER',
        4010 => 'TYPE_RGBLEDCONTROLLER',
        4020 => 'TYPE_DIMMERANDRGBLED',
    ];

    const bitStr = [
        0x00000001 => 'BIT_RELAYFNC_CONTROLLINGTHEGATEWAYLOCK',
        0x00000002 => 'BIT_RELAYFNC_CONTROLLINGTHEGATE',
        0x00000004 => 'BIT_RELAYFNC_CONTROLLINGTHEGARAGEDOOR',
        0x00000008 => 'BIT_RELAYFNC_CONTROLLINGTHEDOORLOCK',
        0x00000010 => 'BIT_RELAYFNC_CONTROLLINGTHEROLLERSHUTTER',
        0x00000020 => 'BIT_RELAYFNC_POWERSWITCH',
        0x00000040 => 'BIT_RELAYFNC_LIGHTSWITCH',
        0x00000080 => 'BIT_RELAYFNC_STAIRCASETIMER',
    ];

    // @codingStandardsIgnoreEnd
}

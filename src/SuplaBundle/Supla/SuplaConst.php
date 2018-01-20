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
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\RelayFunctionBits;

/**
 * @deprecated use finer-graned enums
 */
class SuplaConst {
    const TYPE_SENSORNO = ChannelType::SENSORNO;
    const TYPE_SENSORNC = ChannelType::SENSORNC;
    const TYPE_DISTANCESENSOR = ChannelType::DISTANCESENSOR;
    const TYPE_CALLBUTTON = ChannelType::CALLBUTTON;
    const TYPE_RELAYHFD4 = ChannelType::RELAYHFD4;
    const TYPE_RELAYG5LA1A = ChannelType::RELAYG5LA1A;
    const TYPE_RELAY2XG5LA1A = ChannelType::RELAY2XG5LA1A;
    const TYPE_RELAY = ChannelType::RELAY;
    const TYPE_THERMOMETERDS18B20 = ChannelType::THERMOMETERDS18B20;
    const TYPE_DHT11 = ChannelType::DHT11;
    const TYPE_DHT21 = ChannelType::DHT21;
    const TYPE_DHT22 = ChannelType::DHT22;
    const TYPE_AM2301 = ChannelType::AM2301;
    const TYPE_AM2302 = ChannelType::AM2302;
    const TYPE_THERMOMETER = ChannelType::THERMOMETER;
    const TYPE_HUMIDITYSENSOR = ChannelType::HUMIDITYSENSOR;
    const TYPE_HUMIDITYANDTEMPSENSOR = ChannelType::HUMIDITYANDTEMPSENSOR;
    const TYPE_WINDSENSOR = ChannelType::WINDSENSOR;
    const TYPE_PRESSURESENSOR = ChannelType::PRESSURESENSOR;
    const TYPE_RAINSENSOR = ChannelType::RAINSENSOR;
    const TYPE_WEIGHTSENSOR = ChannelType::WEIGHTSENSOR;
    const TYPE_WEATHER_STATION = ChannelType::WEATHER_STATION;

    const TYPE_DIMMER = ChannelType::DIMMER;
    const TYPE_RGBLEDCONTROLLER = ChannelType::RGBLEDCONTROLLER;
    const TYPE_DIMMERANDRGBLED = ChannelType::DIMMERANDRGBLED;

    const BIT_RELAYFNC_CONTROLLINGTHEGATEWAYLOCK = RelayFunctionBits::CONTROLLINGTHEGATEWAYLOCK;
    const BIT_RELAYFNC_CONTROLLINGTHEGATE = RelayFunctionBits::CONTROLLINGTHEGATE;
    const BIT_RELAYFNC_CONTROLLINGTHEGARAGEDOOR = RelayFunctionBits::CONTROLLINGTHEGARAGEDOOR;
    const BIT_RELAYFNC_CONTROLLINGTHEDOORLOCK = RelayFunctionBits::CONTROLLINGTHEDOORLOCK;
    const BIT_RELAYFNC_CONTROLLINGTHEROLLERSHUTTER = RelayFunctionBits::CONTROLLINGTHEROLLERSHUTTER;
    const BIT_RELAYFNC_POWERSWITCH = RelayFunctionBits::POWERSWITCH;
    const BIT_RELAYFNC_LIGHTSWITCH = RelayFunctionBits::LIGHTSWITCH;
    const BIT_RELAYFNC_STAIRCASETIMER = RelayFunctionBits::STAIRCASETIMER;

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
}

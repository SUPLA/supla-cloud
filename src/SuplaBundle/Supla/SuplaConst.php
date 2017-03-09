<?php
/*
 src/SuplaBundle/Supla/SuplaConst.php

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


class SuplaConst
{
	const TYPE_SENSORNO            = 1000;
	const TYPE_SENSORNC            = 1010;
	const TYPE_DISTANCESENSOR      = 1020;
	const TYPE_CALLBUTTON          = 1500;
	const TYPE_RELAYHFD4           = 2000;
	const TYPE_RELAYG5LA1A         = 2010;
	const TYPE_2XRELAYG5LA1A       = 2020;
	const TYPE_RELAY               = 2900;
	const TYPE_THERMOMETERDS18B20  = 3000;
	const TYPE_DHT11               = 3010;
	const TYPE_DHT21               = 3022;
	const TYPE_DHT22               = 3020;
	const TYPE_AM2301              = 3032;
	const TYPE_AM2302              = 3030;
	const TYPE_DIMMER              = 4000;
	const TYPE_RGBLEDCONTROLLER    = 4010;
	const TYPE_DIMMERANDRGBLED     = 4020;
	
	const BIT_RELAYFNC_CONTROLLINGTHEGATEWAYLOCK       =  0x00000001;
	const BIT_RELAYFNC_CONTROLLINGTHEGATE              =  0x00000002;
	const BIT_RELAYFNC_CONTROLLINGTHEGARAGEDOOR        =  0x00000004;
	const BIT_RELAYFNC_CONTROLLINGTHEDOORLOCK          =  0x00000008;
	const BIT_RELAYFNC_CONTROLLINGTHEROLLERSHUTTER     =  0x00000010;
	const BIT_RELAYFNC_POWERSWITCH                     =  0x00000020;
	const BIT_RELAYFNC_LIGHTSWITCH                     =  0x00000040;
	
	const FNC_NONE                          = 0;
	const FNC_CONTROLLINGTHEGATEWAYLOCK     = 10;
	const FNC_CONTROLLINGTHEGATE            = 20;
	const FNC_CONTROLLINGTHEGARAGEDOOR      = 30;
	const FNC_THERMOMETER                   = 40;
	const FNC_HUMIDITY                      = 42;
	const FNC_HUMIDITYANDTEMPERATURE        = 45;
	const FNC_OPENINGSENSOR_GATEWAY         = 50;
	const FNC_OPENINGSENSOR_GATE            = 60;
	const FNC_OPENINGSENSOR_GARAGEDOOR      = 70;
	const FNC_NOLIQUIDSENSOR                = 80;
	const FNC_CONTROLLINGTHEDOORLOCK        = 90;
	const FNC_OPENINGSENSOR_DOOR            = 100;
	const FNC_CONTROLLINGTHEROLLERSHUTTER   = 110;
	const FNC_OPENINGSENSOR_ROLLERSHUTTER   = 120;
	const FNC_POWERSWITCH                   = 130;
	const FNC_LIGHTSWITCH                   = 140;
	const FNC_DIMMER                        = 180;
	const FNC_RGBLIGHTING                   = 190;
	const FNC_DIMMERANDRGBLIGHTING          = 200;
	const FNC_DEPTHSENSOR                   = 210;
	const FNC_DISTANCESENSOR                = 220;
	
	const ACTION_OPEN          = 10;
	const ACTION_CLOSE         = 20;
	const ACTION_SHUT          = 30;
	const ACTION_REVEAL        = 40;
	const ACTION_TURN_ON       = 50;
	const ACTION_TURN_OFF      = 60;
	const ACTION_DIM           = 70;
	const ACTION_SET_RGB_COLOR = 80;
	const ACTION_SET_RANDOM_RGB_COLOR = 90;
	
	const ACTION_EXECUTION_RESULT_UNKNOWN = 0;
	const ACTION_EXECUTION_RESULT_SUCCESS = 1;
	const ACTION_EXECUTION_RESULT_DEVICE_UNREACHABLE = 2;
	const ACTION_EXECUTION_RESULT_NO_SENSOR = 3;
	const ACTION_EXECUTION_RESULT_OVERDUE = 4;
	const ACTION_EXECUTION_RESULT_ZOMBIE =  5;

	const typeStr = array(
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
			4000 => 'TYPE_DIMMER',
			4010 => 'TYPE_RGBLEDCONTROLLER',
			4020 => 'TYPE_DIMMERANDRGBLED',
	);
	
	const bitStr = array(
			0x00000001 => 'BIT_RELAYFNC_CONTROLLINGTHEGATEWAYLOCK',
			0x00000002 => 'BIT_RELAYFNC_CONTROLLINGTHEGATE',
			0x00000004 => 'BIT_RELAYFNC_CONTROLLINGTHEGARAGEDOOR',
			0x00000008 => 'BIT_RELAYFNC_CONTROLLINGTHEDOORLOCK',
			0x00000010 => 'BIT_RELAYFNC_CONTROLLINGTHEROLLERSHUTTER',
			0x00000020 => 'BIT_RELAYFNC_POWERSWITCH',
			0x00000040 => 'BIT_RELAYFNC_LIGHTSWITCH',
	);
	
	const fncStr = array(
			  0 => 'FNC_NONE',  
			 10 => 'FNC_CONTROLLINGTHEGATEWAYLOCK',  
			 20 => 'FNC_CONTROLLINGTHEGATE',  
			 30 => 'FNC_CONTROLLINGTHEGARAGEDOOR',  
			 40 => 'FNC_THERMOMETER',  
			 42 => 'FNC_HUMIDITY',  
			 45 => 'FNC_HUMIDITYANDTEMPERATURE',  
			 50 => 'FNC_OPENINGSENSOR_GATEWAY',  
			 60 => 'FNC_OPENINGSENSOR_GATE',  
			 70 => 'FNC_OPENINGSENSOR_GARAGEDOOR',  
			 80 => 'FNC_NOLIQUIDSENSOR',  
			 90 => 'FNC_CONTROLLINGTHEDOORLOCK',  
			100 => 'FNC_OPENINGSENSOR_DOOR',  
			110 => 'FNC_CONTROLLINGTHEROLLERSHUTTER',  
			120 => 'FNC_OPENINGSENSOR_ROLLERSHUTTER',  
			130 => 'FNC_POWERSWITCH',  
			140 => 'FNC_LIGHTSWITCH',  
			180 => 'FNC_DIMMER',  
			190 => 'FNC_RGBLIGHTING',  
			200 => 'FNC_DIMMERANDRGBLIGHTING',  
			210 => 'FNC_DEPTHSENSOR',  
			220 => 'FNC_DISTANCESENSOR',  
	);
	
}

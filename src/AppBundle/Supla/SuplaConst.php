<?php
/*
 src/AppBundle/Supla/SuplaConst.php

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

namespace AppBundle\Supla;

/**
 * @author Przemyslaw Zygmunt przemek@supla.org
 */
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

	const ACTION_OPEN = 10;
	const ACTION_CLOSE = 20;
	const ACTION_COVER = 30;
	const ACTION_UNCOVER = 40;
	const ACTION_TURN_ON = 50;
	const ACTION_TURN_OFF = 60;
	const ACTION_DIM = 70;
	const ACTION_SET_RGB_COLOR = 80;
}

?>

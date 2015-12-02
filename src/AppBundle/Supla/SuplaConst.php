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
 * @author Przemyslaw Zygmunt p.zygmunt@acsoftware.pl [AC SOFTWARE]
 */
class SuplaConst
{
	const TYPE_SENSORNO            = 1000;
	const TYPE_RELAYHFD4           = 2000;
	const TYPE_RELAYG5LA1A         = 2010;
	const TYPE_2XRELAYG5LA1A       = 2020;
	const TYPE_THERMOMETERDS18B20  = 3000;
	
	const FNC_NONE                          = 0;
	const FNC_CONTROLLINGTHEGATEWAYLOCK     = 10;
	const FNC_CONTROLLINGTHEGATE            = 20;
	const FNC_CONTROLLINGTHEGARAGEDOOR      = 30;
	const FNC_THERMOMETER                   = 40;
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

	
}

?>

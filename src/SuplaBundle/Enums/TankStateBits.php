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

/**
 * @method static TankStateBits WARNING_LEVEL()
 * @method static TankStateBits ALARM_LEVEL()
 * @method static TankStateBits INVALID_SENSOR_STATE()
 * @method static TankStateBits SOUND_ALARM_ON()
 */
final class TankStateBits extends ChannelFunctionBits {
    const WARNING_LEVEL = 1 << 0;
    const ALARM_LEVEL = 1 << 1;
    const INVALID_SENSOR_STATE = 1 << 2;
    const SOUND_ALARM_ON = 1 << 3;
}

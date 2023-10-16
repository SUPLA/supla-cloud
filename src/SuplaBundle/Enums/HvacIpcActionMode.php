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

final class HvacIpcActionMode extends Enum {
    const MODE_NOT_SET = 0;
    const MODE_OFF = 1;
    const MODE_HEAT = 2;
    const MODE_COOL = 3;
    const MODE_AUTO = 4;
    const MODE_FAN_ONLY = 6;
    const MODE_DRY = 7;
    const MODE_CMD_TURN_ON = 8;
    const MODE_CMD_WEEKLY_SCHEDULE = 9;
    const MODE_CMD_SWITCH_TO_MANUAL = 10;
}

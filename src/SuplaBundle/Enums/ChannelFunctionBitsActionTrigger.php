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
 * @method static ChannelFunctionBitsActionTrigger PRESS()
 * @method static ChannelFunctionBitsActionTrigger RELEASE()
 * @method static ChannelFunctionBitsActionTrigger HOLD()
 * @method static ChannelFunctionBitsActionTrigger PRESS_2X()
 * @method static ChannelFunctionBitsActionTrigger PRESS_3X()
 */
final class ChannelFunctionBitsActionTrigger extends ChannelFunctionBits {
    const PRESS = 1 << 8; // i18n:['actionTrigger_PRESS','actionTriggerDescription_PRESS']
    const RELEASE = 1 << 9; // i18n:['actionTrigger_RELEASE','actionTriggerDescription_RELEASE']
    const HOLD = 1 << 10; // i18n:['actionTrigger_HOLD','actionTriggerDescription_HOLD']
    const PRESS_2X = 1 << 11; // i18n:['actionTrigger_PRESS_2X','actionTriggerDescription_PRESS_2X']
    const PRESS_3X = 1 << 12; // i18n:['actionTrigger_PRESS_3X','actionTriggerDescription_PRESS_3X']
}

/*
TODO
// Recommended for bistable buttons
#define SUPLA_ACTION_CAP_TURN_ON (1<<0)
#define SUPLA_ACTION_CAP_TURN_OFF (1<<1)
#define SUPLA_ACTION_CAP_TOGGLE_x1 (1<<2)
#define SUPLA_ACTION_CAP_TOGGLE_x2 (1<<3)
#define SUPLA_ACTION_CAP_TOGGLE_x3 (1<<4)

// Recommended for monostable buttons
#define SUPLA_ACTION_CAP_HOLD (1<<10)
#define SUPLA_ACTION_CAP_SHORT_PRESS_x1 (1<<11)
#define SUPLA_ACTION_CAP_SHORT_PRESS_x2 (1<<12)
#define SUPLA_ACTION_CAP_SHORT_PRESS_x3 (1<<13)
#define SUPLA_ACTION_CAP_SHORT_PRESS_x4 (1<<14)

 */

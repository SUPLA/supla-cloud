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
 * @method ChannelFunctionBitsActionTrigger PRESS()
 * @method ChannelFunctionBitsActionTrigger RELEASE()
 * @method ChannelFunctionBitsActionTrigger HOLD()
 * @method ChannelFunctionBitsActionTrigger PRESS_2X()
 * @method ChannelFunctionBitsActionTrigger PRESS_3X()
 */
final class ChannelFunctionBitsActionTrigger extends ChannelFunctionBits {
    const PRESS = 1 << 8; // i18n:['actionTriggerBehavior_PRESS','actionTriggerBehaviorDescription_PRESS']
    const RELEASE = 1 << 9; // i18n:['actionTriggerBehavior_RELEASE','actionTriggerBehaviorDescription_RELEASE']
    const HOLD = 1 << 10; // i18n:['actionTriggerBehavior_HOLD','actionTriggerBehaviorDescription_HOLD']
    const PRESS_2X = 1 << 11; // i18n:['actionTriggerBehavior_PRESS_2X','actionTriggerBehaviorDescription_PRESS_2X']
    const PRESS_3X = 1 << 12; // i18n:['actionTriggerBehavior_PRESS_3X','actionTriggerBehaviorDescription_PRESS_3X']
}

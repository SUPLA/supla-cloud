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
 * @method static ChannelConfigChangeScope CHANNEL_FUNCTION()
 * @method static ChannelConfigChangeScope CAPTION()
 * @method static ChannelConfigChangeScope LOCATION()
 * @method static ChannelConfigChangeScope VISIBILITY()
 * @method static ChannelConfigChangeScope ICON()
 * @method static ChannelConfigChangeScope JSON_BASIC()
 * @method static ChannelConfigChangeScope JSON_WEEKLY_SCHEDULE()
 * @method static ChannelConfigChangeScope JSON_ALT_WEEKLY_SCHEDULE()
 * @method static ChannelConfigChangeScope RELATIONS()
 * @method static ChannelConfigChangeScope ALEXA_INTEGRATION_ENABLED()
 * @method static ChannelConfigChangeScope OCR()
 */
final class ChannelConfigChangeScope extends ChannelFunctionBits {
    const CHANNEL_FUNCTION = 0x1;
    const CAPTION = 0x2;
    const LOCATION = 0x4;
    const VISIBILITY = 0x8;
    const ICON = 0x10;
    const JSON_BASIC = 0x20;
    const JSON_WEEKLY_SCHEDULE = 0x40;
    const JSON_ALT_WEEKLY_SCHEDULE = 0x80;
    const RELATIONS = 0x100;
    const ALEXA_INTEGRATION_ENABLED = 0x200;
    const OCR = 0x400;
}

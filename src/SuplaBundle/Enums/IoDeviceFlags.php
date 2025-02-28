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
 * @method static IoDeviceFlags ENTER_CONFIGURATION_MODE_AVAILABLE()
 * @method static IoDeviceFlags SLEEP_MODE_ENABLED()
 * @method static IoDeviceFlags REMOTE_RESTART_AVAILABLE()
 * @method static IoDeviceFlags DEVICE_LOCKED()
 * @method static IoDeviceFlags PAIRING_SUBDEVICES_AVAILABLE()
 * @method static IoDeviceFlags ALWAYS_ALLOW_CHANNEL_DELETION()
 * @method static IoDeviceFlags ALWAYS_ALLOW_SUBDEVICE_CHANNEL_DELETION()
 * @method static IoDeviceFlags BLOCK_ADDING_CHANNELS_AFTER_DELETION()
 * @method static IoDeviceFlags IDENTIFY_DEVICE_AVAILABLE()
 */
final class IoDeviceFlags extends ChannelFunctionBits {
    /** @see https://github.com/SUPLA/supla-cloud/issues/448 */
    const ENTER_CONFIGURATION_MODE_AVAILABLE = 0x0010;
    /** @see https://github.com/SUPLA/supla-cloud/issues/622 */
    const SLEEP_MODE_ENABLED = 0x0020;
    const IDENTIFY_DEVICE_AVAILABLE = 0x0400;
    const REMOTE_RESTART_AVAILABLE = 0x0800;

    const DEVICE_LOCKED = 0x0100;

    const PAIRING_SUBDEVICES_AVAILABLE = 0x0200;
    const ALWAYS_ALLOW_CHANNEL_DELETION = 0x1000;
    const ALWAYS_ALLOW_SUBDEVICE_CHANNEL_DELETION = 0x4000;
    const BLOCK_ADDING_CHANNELS_AFTER_DELETION = 0x2000;
}

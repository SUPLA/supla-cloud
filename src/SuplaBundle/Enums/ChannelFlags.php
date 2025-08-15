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
 * @method static ChannelFlags TIME_SETTING_NOT_AVAILABLE()
 * @method static ChannelFlags RESET_COUNTERS_ACTION_AVAILABLE()
 * @method static ChannelFlags AUTO_CALIBRATION_AVAILABLE()
 * @method static ChannelFlags RECALIBRATE_ACTION_AVAILABLE()
 * @method static ChannelFlags ROLLER_SHUTTER_STEP_BY_STEP_ACTIONS()
 * @method static ChannelFlags ELECTRICITY_METER_PHASE1_UNSUPPORTED()
 * @method static ChannelFlags ELECTRICITY_METER_PHASE2_UNSUPPORTED()
 * @method static ChannelFlags ELECTRICITY_METER_PHASE3_UNSUPPORTED()
 * @method static ChannelFlags IDENTIFY_SUBDEVICE_AVAILABLE()
 * @method static ChannelFlags RESTART_SUBDEVICE_AVAILABLE()
 * @method static ChannelFlags BATTERY_COVER_AVAILABLE()
 * @method static ChannelFlags RUNTIME_CHANNEL_CONFIG_UPDATE()
 * @method static ChannelFlags FLOOD_SENSORS_SUPPORTED()
 * @method static ChannelFlags TANK_FILL_LEVEL_REPORTING_IN_FULL_RANGE()
 * @method static ChannelFlags VALVE_MOTOR_ALARM_SUPPORTED()
 * @method static ChannelFlags ALWAYS_ALLOW_CHANNEL_DELETION()
 * @method static ChannelFlags HAS_EXTENDED_CHANNEL_STATE()
 */
final class ChannelFlags extends ChannelFunctionBits {
    /** @see https://github.com/SUPLA/supla-core/blob/ffa56e4579812c50ca15202c698d0c1d363a0258/supla-common/proto.h#L458 */
    const RESET_COUNTERS_ACTION_AVAILABLE = 0x2000;
    /** @see https://github.com/SUPLA/supla-core/blob/ffa56e4579812c50ca15202c698d0c1d363a0258/supla-common/proto.h#L464 */
    const TIME_SETTING_NOT_AVAILABLE = 0x00100000;
    /** @see https://github.com/SUPLA/supla-core/blob/ffa56e4579812c50ca15202c698d0c1d363a0258/supla-common/proto.h#L457 */
    const AUTO_CALIBRATION_AVAILABLE = 0x1000;
    const RECALIBRATE_ACTION_AVAILABLE = 0x4000;
    /** @see https://github.com/SUPLA/supla-core/blob/9c4af87e14fc0164ac9d80e66e107cf9dc113f92/supla-common/proto.h#L457 */
    const ROLLER_SHUTTER_STEP_BY_STEP_ACTIONS = 0x0080;
    /** @see https://github.com/SUPLA/supla-cloud/issues/639 */
    const ELECTRICITY_METER_PHASE1_UNSUPPORTED = 0x00020000;
    const ELECTRICITY_METER_PHASE2_UNSUPPORTED = 0x00040000;
    const ELECTRICITY_METER_PHASE3_UNSUPPORTED = 0x00080000;
    const IDENTIFY_SUBDEVICE_AVAILABLE = 0x8000;
    const RESTART_SUBDEVICE_AVAILABLE = 0x40000000;
    /** @see https://github.com/SUPLA/supla-cloud/issues/916 */
    const BATTERY_COVER_AVAILABLE = 0x80000000;
    const RUNTIME_CHANNEL_CONFIG_UPDATE = 0x08000000;
    const FLOOD_SENSORS_SUPPORTED = 0x0010;
    const TANK_FILL_LEVEL_REPORTING_IN_FULL_RANGE = 0x0020;
    const VALVE_MOTOR_ALARM_SUPPORTED = 0x0040;
    const ALWAYS_ALLOW_CHANNEL_DELETION = 0x0200;
    const HAS_EXTENDED_CHANNEL_STATE = 0x00010000;
}

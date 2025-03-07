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
 * @see https://github.com/SUPLA/supla-cloud/issues/752
 *
 * @method static HvacIpcValueFlags TEMPERATURE_HEAT_SET()
 * @method static HvacIpcValueFlags TEMPERATURE_COOL_SET()
 * @method static HvacIpcValueFlags HEATING()
 * @method static HvacIpcValueFlags COOLING()
 * @method static HvacIpcValueFlags WEEKLY_SCHEDULE()
 * @method static HvacIpcValueFlags COUNTDOWN_TIMER()
 * @method static HvacIpcValueFlags THERMOMETER_ERROR()
 * @method static HvacIpcValueFlags CLOCK_ERROR()
 * @method static HvacIpcValueFlags FORCED_OFF_BY_SENSOR()
 * @method static HvacIpcValueFlags WEEKLY_SCHEDULE_TEMPORAL_OVERRIDE()
 * @method static HvacIpcValueFlags BATTERY_COVER_OPEN()
 * @method static HvacIpcValueFlags CALIBRATION_ERROR()
 */
final class HvacIpcValueFlags extends ChannelFunctionBits {
    const TEMPERATURE_HEAT_SET = 1 << 0;
    const TEMPERATURE_COOL_SET = 1 << 1;
    const HEATING = 1 << 2;
    const COOLING = 1 << 3;
    const WEEKLY_SCHEDULE = 1 << 4;
    const COUNTDOWN_TIMER = 1 << 5;
    const THERMOMETER_ERROR = 1 << 7;
    const CLOCK_ERROR = 1 << 8;
    const FORCED_OFF_BY_SENSOR = 1 << 9;
    const WEEKLY_SCHEDULE_TEMPORAL_OVERRIDE = 1 << 11;
    const BATTERY_COVER_OPEN = 1 << 12;
    const CALIBRATION_ERROR = 1 << 13;
}

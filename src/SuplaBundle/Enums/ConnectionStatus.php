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

/**
 * @method static ConnectionStatus NOT_CONNECTED()
 * @method static ConnectionStatus CONNECTED()
 * @method static ConnectionStatus CONNECTED_NOT_AVAILABLE()
 * @method static ConnectionStatus OFFLINE_REMOTE_WAKEUP_NOT_SUPPORTED()
 */
final class ConnectionStatus extends Enum {
    const NOT_CONNECTED = 0;
    const CONNECTED = 1;
    const CONNECTED_NOT_AVAILABLE = 2;
    const OFFLINE_REMOTE_WAKEUP_NOT_SUPPORTED = 3;

    public function isConnected(): bool {
        return in_array($this->value, [
            self::CONNECTED,
            self::CONNECTED_NOT_AVAILABLE,
        ], true);
    }
}

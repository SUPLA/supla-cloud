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
 * @method static ApiClientType WEBAPP()
 * @method static ApiClientType CLIENT_APP()
 * @method static ApiClientType ADMIN()
 * @method static ApiClientType USER()
 * @method static ApiClientType BROKER()
 */
final class ApiClientType extends Enum {
    /** API Client for issuing tokens for SUPLA-Cloud webapp. */
    const WEBAPP = 1;
    /** API Clients for client apps (smartphones) communication. */
    const CLIENT_APP = 2;
    /** API Clients created by administrators, i.e. the provided console command. */
    const ADMIN = 3;
    /** API Clients created by users with provided GUI in SUPLA-Cloud webapp. */
    const USER = 4;
    /** Public OAuth Clients created in Target Cloud with mapped clientIds, based on data given from Autodiscover. */
    const BROKER = 5;
}

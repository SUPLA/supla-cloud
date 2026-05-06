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

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Dark images for user icons.
 */
class Version20240327152635 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user_icons ADD image_dark1 LONGBLOB DEFAULT NULL, ADD image_dark2 LONGBLOB DEFAULT NULL, ADD image_dark3 LONGBLOB DEFAULT NULL, ADD image_dark4 LONGBLOB DEFAULT NULL');
    }
}

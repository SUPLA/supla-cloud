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
 * The IFNULL function was added when setting flags
 */
class Version20220222110707 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_update_channel_flags`');

        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_update_channel_flags`(IN `_channel_id` INT, IN `_user_id` INT, IN `_flags` INT)
    NO SQL
UPDATE supla_dev_channel SET flags = IFNULL(flags, 0) | IFNULL(_flags, 0) WHERE id = _channel_id AND user_id = _user_id
PROCEDURE
        );
    }
}

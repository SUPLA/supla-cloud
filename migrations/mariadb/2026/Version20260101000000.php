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

use App\Migrations\NoWayBackMigration;

/**
 * Add general_purpose_text log table and stored procedure.
 */
class Version20260101000000 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `supla_gp_text_log` (
              `channel_id` int(11) NOT NULL,
              `date` datetime NOT NULL COMMENT '(DC2Type:stringdatetime)',
              `value` varchar(255) NOT NULL,
              PRIMARY KEY (`channel_id`, `date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        $this->addSql("DROP PROCEDURE IF EXISTS `supla_add_gp_text_log_item`");
        $this->addSql("
            CREATE PROCEDURE `supla_add_gp_text_log_item`(
              IN `_channel_id` INT,
              IN `_value` VARCHAR(255)
            )
            NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER
            INSERT INTO `supla_gp_text_log`(`channel_id`, `date`, `value`)
            VALUES (_channel_id, UTC_TIMESTAMP(), _value)
        ");
    }
}

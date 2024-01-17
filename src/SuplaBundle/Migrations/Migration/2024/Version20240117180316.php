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
 * Use utf8mb4 in all tables with user inputs.
 */
class Version20240117180316 extends NoWayBackMigration {
    public function migrate() {
        foreach ([
                     'supla_accessid',
                     'supla_audit',
                     'supla_client',
                     'supla_dev_channel',
                     'supla_direct_link',
                     'supla_iodevice',
                     'supla_dev_channel_group',
                     'supla_location',
                     'supla_scene',
                     'supla_scene_operation',
                     'supla_schedule',
                     'supla_settings_string',
                     'supla_user',
                     'supla_value_based_trigger',
                 ] as $tableName) {
            $this->addSql("ALTER TABLE `$tableName` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        }
    }
}

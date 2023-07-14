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

namespace Supla\Migrations;

/**
 * Delete zombie notifications left after VBT edits.
 */
class Version20230714142433 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql(<<<SQL
        DELETE FROM supla_push_notification WHERE id IN(
            SELECT pn.id FROM (SELECT * FROM supla_push_notification) pn
            LEFT JOIN supla_value_based_trigger vbt ON pn.id = vbt.push_notification_id
            LEFT JOIN supla_scene_operation so ON pn.id = so.push_notification_id 
            WHERE pn.channel_id IS NULL AND pn.iodevice_id IS NULL AND vbt.id IS NULL AND so.id IS NULL
        );
SQL
        );
        $this->addSql(<<<SQL
        UPDATE supla_push_notification pn SET channel_id = (
            SELECT owning_channel_id FROM supla_value_based_trigger WHERE push_notification_id = pn.id
        )
        WHERE channel_id IS NULL AND iodevice_id IS NULL;
SQL
        );
    }
}

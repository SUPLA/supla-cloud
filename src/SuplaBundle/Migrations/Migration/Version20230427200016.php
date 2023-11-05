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
 * Schedule as a subject for direct links and scenes.
 * Schedule enable/disable procedures
 * New procedure added "supla_enable_schedule"
 * New procedure added "supla_disable_schedule"
 * New procedure added "supla_set_channel_group_caption"
 */
class Version20230427200016 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_direct_link ADD schedule_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_direct_link ADD CONSTRAINT FK_6AE7809FA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES supla_schedule (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_6AE7809FA40BC2D5 ON supla_direct_link (schedule_id)');
        $this->addSql('ALTER TABLE supla_scene_operation ADD schedule_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_scene_operation ADD CONSTRAINT FK_64A50CF5A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES supla_schedule (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_64A50CF5A40BC2D5 ON supla_scene_operation (schedule_id)');
        $this->addSql('CREATE PROCEDURE `supla_enable_schedule`(IN `_user_id` INT, IN `_id` INT) UPDATE supla_schedule SET enabled = 1 WHERE id = _id AND user_id = _user_id');
        $this->addSql('CREATE PROCEDURE `supla_disable_schedule`(IN `_user_id` INT, IN `_id` INT) UPDATE supla_schedule SET enabled = 0 WHERE id = _id AND user_id = _user_id');
        $this->addSql("CREATE PROCEDURE `supla_set_channel_group_caption`(IN `_user_id` INT, IN `_channel_group_id` INT, IN `_caption` VARCHAR(255) CHARSET utf8mb4) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER UPDATE supla_dev_channel_group SET caption = _caption WHERE id = _channel_group_id AND user_id = _user_id");
    }
}

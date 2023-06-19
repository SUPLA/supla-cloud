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
 * New field supla_iodevice.user_config
 * New procedure supla_set_device_user_config
 * New procedure supla_set_channel_user_config
 */
class Version20230604004315 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql("ALTER TABLE supla_iodevice ADD user_config VARCHAR(2048) DEFAULT NULL");
        $this->addSql("CREATE PROCEDURE `supla_set_device_user_config`(IN `_user_id` INT, IN `_device_id` INT, IN `_user_config` VARCHAR(2048) CHARSET utf8mb4, IN `_md5` VARCHAR(32)) BEGIN UPDATE supla_iodevice SET user_config = _user_config WHERE id = _device_id AND user_id = _user_id AND MD5(IFNULL(user_config, '')) = _md5; SELECT STRCMP(user_config, _user_config) FROM supla_iodevice WHERE id = _device_id AND user_id = _user_id; END");
        $this->addSql("CREATE PROCEDURE `supla_set_channel_user_config`(IN `_user_id` INT, IN `_channel_id` INT, IN `_user_config` VARCHAR(2048) CHARSET utf8mb4, IN `_md5` VARCHAR(32)) BEGIN UPDATE supla_dev_channel SET user_config = _user_config WHERE id = _channel_id AND user_id = _user_id AND MD5(IFNULL(user_config, '')) = _md5; SELECT STRCMP(user_config, _user_config) FROM supla_dev_channel WHERE id = _channel_id AND user_id = _user_id; END");
    }
}

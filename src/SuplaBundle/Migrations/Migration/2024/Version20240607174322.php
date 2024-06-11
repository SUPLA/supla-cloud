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
 * Add a column informing that adding channels is blocked to the device table.
 * Add a sub device id column for grouping devices.
 * Add a column containing the channel conflict detail in JSON format.
 */
class Version20240607174322 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_iodevice ADD channel_addition_blocked TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel ADD sub_device_id SMALLINT NOT NULL DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel ADD conflict_details VARCHAR(256) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_channel`');
        $this->addSql('CREATE PROCEDURE `supla_add_channel`(IN `_type` INT, IN `_func` INT, IN `_param1` INT, IN `_param2` INT, IN `_param3` INT, IN `_user_id` INT, IN `_channel_number` INT, IN `_iodevice_id` INT, IN `_flist` INT, IN `_flags` INT, IN `_alt_icon` INT, IN `_sub_device_id` SMALLINT UNSIGNED) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN INSERT INTO `supla_dev_channel` (`type`, `func`, `param1`, `param2`, `param3`, `user_id`, `channel_number`, `iodevice_id`, `flist`, `flags`, `alt_icon`, `sub_device_id`) VALUES (_type, _func, _param1, _param2, _param3, _user_id, _channel_number, _iodevice_id, _flist, _flags, _alt_icon, _sub_device_id); END');
    }
}

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
 * New table: supla_gpm_measurement_log.
 * New table: supla_gpm_meter_log.
 * The supla_add_channel procedure has been extended with the 'alt_icon' parameter.
 * The size of the "flags" variable has been increased, from INT to UNSIGNED BIGINT
 */
class Version20240117202218 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_gpm_measurement_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', open_value DOUBLE PRECISION NOT NULL, close_value DOUBLE PRECISION NOT NULL, avg_value DOUBLE PRECISION NOT NULL, max_value DOUBLE PRECISION NOT NULL, min_value DOUBLE PRECISION NOT NULL, PRIMARY KEY(channel_id, date)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_gpm_meter_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', counter DOUBLE PRECISION NOT NULL, calculated_value DOUBLE PRECISION NOT NULL, PRIMARY KEY(channel_id, date)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_channel`');
        $this->addSql('CREATE PROCEDURE `supla_add_channel`(IN `_type` INT, IN `_func` INT, IN `_param1` INT, IN `_param2` INT, IN `_param3` INT, IN `_user_id` INT, IN `_channel_number` INT, IN `_iodevice_id` INT, IN `_flist` INT, IN `_flags` INT, IN `_alt_icon` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN INSERT INTO `supla_dev_channel` (`type`, `func`, `param1`, `param2`, `param3`, `user_id`, `channel_number`, `iodevice_id`, `flist`, `flags`, `alt_icon`) VALUES (_type, _func, _param1, _param2, _param3, _user_id, _channel_number, _iodevice_id, _flist, _flags, _alt_icon); END');
        $this->addSql('ALTER TABLE `supla_dev_channel` CHANGE `flags` `flags` BIGINT UNSIGNED NULL DEFAULT NULL');
    }
}

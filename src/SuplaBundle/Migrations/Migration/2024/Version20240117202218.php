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
 * New table: supla_gp_measurement_log.
 * New table: supla_gp_meter_log.
 * The supla_add_channel procedure has been extended with the 'alt_icon' parameter.
 * The size of the "flags" variable has been increased, from INT to UNSIGNED BIGINT
 * New procedure: supla_add_gp_measurement_log_item
 * New procedure: supla_add_gp_meter_log_item
 * Remove collation from the input parameters of the supla_set_channel_json_config procedure
 * Remove collation from the input parameters of the supla_set_device_json_config procedure
 */
class Version20240117202218 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_gp_measurement_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', open_value DOUBLE PRECISION NOT NULL, close_value DOUBLE PRECISION NOT NULL, avg_value DOUBLE PRECISION NOT NULL, max_value DOUBLE PRECISION NOT NULL, min_value DOUBLE PRECISION NOT NULL, PRIMARY KEY(channel_id, date)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_gp_meter_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', value DOUBLE PRECISION NOT NULL, PRIMARY KEY(channel_id, date)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_channel`');
        $this->addSql('CREATE PROCEDURE `supla_add_channel`(IN `_type` INT, IN `_func` INT, IN `_param1` INT, IN `_param2` INT, IN `_param3` INT, IN `_user_id` INT, IN `_channel_number` INT, IN `_iodevice_id` INT, IN `_flist` INT, IN `_flags` INT, IN `_alt_icon` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN INSERT INTO `supla_dev_channel` (`type`, `func`, `param1`, `param2`, `param3`, `user_id`, `channel_number`, `iodevice_id`, `flist`, `flags`, `alt_icon`) VALUES (_type, _func, _param1, _param2, _param3, _user_id, _channel_number, _iodevice_id, _flist, _flags, _alt_icon); END');
        $this->addSql('ALTER TABLE `supla_dev_channel` CHANGE `flags` `flags` BIGINT UNSIGNED NULL DEFAULT NULL');
        $this->addSql('CREATE PROCEDURE `supla_add_gp_measurement_log_item`(IN `_channel_id` INT, IN `_open_value` DOUBLE, IN `_close_value` DOUBLE, IN `_avg_value` DOUBLE, IN `_max_value` DOUBLE, IN `_min_value` DOUBLE) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER INSERT INTO `supla_gp_measurement_log`(`channel_id`, `date`, `open_value`, `close_value`, `avg_value`, `max_value`, `min_value`) VALUES (_channel_id, NOW(),_open_value, _close_value, _avg_value, _max_value, _min_value)');
        $this->addSql('CREATE PROCEDURE `supla_add_gp_meter_log_item`(IN `_channel_id` INT, IN `_value` DOUBLE) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER INSERT INTO `supla_gp_meter_log`(`channel_id`, `date`, `value`) VALUES (_channel_id, NOW(), _value)');
        $this->addSql('DROP PROCEDURE `supla_set_channel_json_config`');
        $this->addSql('CREATE PROCEDURE `supla_set_channel_json_config`(IN `_user_id` INT, IN `_channel_id` INT, IN `_user_config` VARCHAR(4096), IN `_user_config_md5` VARCHAR(32), IN `_properties` VARCHAR(2048), IN `_properties_md5` VARCHAR(32)) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER BEGIN UPDATE supla_dev_channel SET user_config = _user_config, properties = _properties WHERE id = _channel_id AND user_id = _user_id AND MD5(IFNULL(user_config, '')) = _user_config_md5 AND MD5(IFNULL(properties, '')) = _properties_md5; SELECT ABS(STRCMP(user_config, _user_config))+ABS(STRCMP(properties, _properties)) FROM supla_dev_channel WHERE id = _channel_id AND user_id = _user_id; END');
        $this->addSql('DROP PROCEDURE `supla_set_device_json_config`');
        $this->addSql('CREATE PROCEDURE `supla_set_device_json_config`(IN `_user_id` INT, IN `_device_id` INT, IN `_user_config` VARCHAR(4096), IN `_user_config_md5` VARCHAR(32), IN `_properties` VARCHAR(2048), IN `_properties_md5` VARCHAR(32)) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER BEGIN UPDATE supla_iodevice SET user_config = _user_config, properties = _properties WHERE id = _device_id AND user_id = _user_id AND MD5(IFNULL(user_config, '')) = _user_config_md5 AND MD5(IFNULL(properties, '')) = _properties_md5; SELECT ABS(STRCMP(user_config, _user_config))+ABS(STRCMP(properties, _properties)) FROM supla_iodevice WHERE id = _device_id AND user_id = _user_id; END');
    }
}

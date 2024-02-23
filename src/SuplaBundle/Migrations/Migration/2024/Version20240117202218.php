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
 * Changing charset to utf8mb4 in the supla_mqtt_broker_auth procedure
 * Table supla_em_voltage_log renamed to supla_em_voltage_aberration_log
 * Added tables and procedures for storing the history of voltage, current and active power
 * Added profile_name field to the supla_client table.
 * Added _not_null parameter to supla_set_channel_caption procedure
 */
class Version20240117202218 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_gp_measurement_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', open_value DOUBLE PRECISION NOT NULL, close_value DOUBLE PRECISION NOT NULL, avg_value DOUBLE PRECISION NOT NULL, max_value DOUBLE PRECISION NOT NULL, min_value DOUBLE PRECISION NOT NULL, PRIMARY KEY(channel_id, date)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_gp_meter_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', value DOUBLE PRECISION NOT NULL, PRIMARY KEY(channel_id, date)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_channel`');
        $this->addSql('CREATE PROCEDURE `supla_add_channel`(IN `_type` INT, IN `_func` INT, IN `_param1` INT, IN `_param2` INT, IN `_param3` INT, IN `_user_id` INT, IN `_channel_number` INT, IN `_iodevice_id` INT, IN `_flist` INT, IN `_flags` INT, IN `_alt_icon` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN INSERT INTO `supla_dev_channel` (`type`, `func`, `param1`, `param2`, `param3`, `user_id`, `channel_number`, `iodevice_id`, `flist`, `flags`, `alt_icon`) VALUES (_type, _func, _param1, _param2, _param3, _user_id, _channel_number, _iodevice_id, _flist, _flags, _alt_icon); END');
        $this->addSql('ALTER TABLE `supla_dev_channel` CHANGE `flags` `flags` BIGINT UNSIGNED NULL DEFAULT NULL');
        $this->addSql('CREATE PROCEDURE `supla_add_gp_measurement_log_item`(IN `_channel_id` INT, IN `_open_value` DOUBLE, IN `_close_value` DOUBLE, IN `_avg_value` DOUBLE, IN `_max_value` DOUBLE, IN `_min_value` DOUBLE) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER INSERT INTO `supla_gp_measurement_log`(`channel_id`, `date`, `open_value`, `close_value`, `avg_value`, `max_value`, `min_value`) VALUES (_channel_id, UTC_TIMESTAMP(),_open_value, _close_value, _avg_value, _max_value, _min_value)');
        $this->addSql('CREATE PROCEDURE `supla_add_gp_meter_log_item`(IN `_channel_id` INT, IN `_value` DOUBLE) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER INSERT INTO `supla_gp_meter_log`(`channel_id`, `date`, `value`) VALUES (_channel_id, UTC_TIMESTAMP(), _value)');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_set_channel_json_config`');
        $this->addSql('CREATE PROCEDURE `supla_set_channel_json_config`(IN `_user_id` INT, IN `_channel_id` INT, IN `_user_config` VARCHAR(4096), IN `_user_config_md5` VARCHAR(32), IN `_properties` VARCHAR(2048), IN `_properties_md5` VARCHAR(32)) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER BEGIN UPDATE supla_dev_channel SET user_config = _user_config, properties = _properties WHERE id = _channel_id AND user_id = _user_id AND MD5(IFNULL(user_config, \'\')) = _user_config_md5 AND MD5(IFNULL(properties, \'\')) = _properties_md5; SELECT ABS(STRCMP(user_config, _user_config))+ABS(STRCMP(properties, _properties)) FROM supla_dev_channel WHERE id = _channel_id AND user_id = _user_id; END');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_set_device_json_config`');
        $this->addSql('CREATE PROCEDURE `supla_set_device_json_config`(IN `_user_id` INT, IN `_device_id` INT, IN `_user_config` VARCHAR(4096), IN `_user_config_md5` VARCHAR(32), IN `_properties` VARCHAR(2048), IN `_properties_md5` VARCHAR(32)) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER BEGIN UPDATE supla_iodevice SET user_config = _user_config, properties = _properties WHERE id = _device_id AND user_id = _user_id AND MD5(IFNULL(user_config, \'\')) = _user_config_md5 AND MD5(IFNULL(properties, \'\')) = _properties_md5; SELECT ABS(STRCMP(user_config, _user_config))+ABS(STRCMP(properties, _properties)) FROM supla_iodevice WHERE id = _device_id AND user_id = _user_id; END');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_mqtt_broker_auth`');
        $this->addSql('CREATE PROCEDURE `supla_mqtt_broker_auth`(IN `in_suid` VARCHAR(255) CHARSET utf8mb4, IN `in_password` VARCHAR(255) CHARSET utf8mb4) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN SET @hashed_password = SHA2(in_password, 512); SELECT 1 FROM supla_user su LEFT JOIN supla_oauth_client_authorizations soca ON su.id = soca.user_id WHERE mqtt_broker_enabled = 1 AND short_unique_id = BINARY in_suid AND( su.mqtt_broker_auth_password = @hashed_password OR soca.mqtt_broker_auth_password = @hashed_password ) LIMIT 1; END');
        $this->addSql('RENAME TABLE `supla_em_voltage_log` TO `supla_em_voltage_aberration_log`');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_em_voltage_log_item`');
        $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_add_em_voltage_aberration_log_item`(
                IN `_date` DATETIME, 
                IN `_channel_id` INT(11), 
                IN `_phase_no` TINYINT,
                IN `_count_total` INT(11),
                IN `_count_above` INT(11),
                IN `_count_below` INT(11),
                IN `_sec_above` INT(11),
                IN `_sec_below` INT(11),
                IN `_max_sec_above` INT(11),
                IN `_max_sec_below` INT(11),
                IN `_min_voltage` NUMERIC(7,2),
                IN `_max_voltage` NUMERIC(7,2),
                IN `_avg_voltage` NUMERIC(7,2),
                IN `_measurement_time_sec` INT(11)
            ) NO SQL BEGIN
            INSERT INTO `supla_em_voltage_aberration_log` (`date`,channel_id, phase_no, count_total, count_above, count_below, sec_above, sec_below, max_sec_above, max_sec_below, min_voltage, max_voltage, avg_voltage, measurement_time_sec)
                                        VALUES (_date,_channel_id,_phase_no,_count_total,_count_above,_count_below,_sec_above,_sec_below,_max_sec_above,_max_sec_below,_min_voltage,_max_voltage,_avg_voltage,_measurement_time_sec);

            END
PROCEDURE
        );
        $this->addSql('CREATE TABLE supla_em_voltage_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', phase_no TINYINT NOT NULL COMMENT \'(DC2Type:tinyint)\', min NUMERIC(5, 2) NOT NULL, max NUMERIC(5, 2) NOT NULL, avg NUMERIC(5, 2) NOT NULL, PRIMARY KEY(channel_id, date, phase_no)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_add_em_voltage_log_item`(
                IN `_date` DATETIME, 
                IN `_channel_id` INT(11), 
                IN `_phase_no` TINYINT,
                IN `_min` NUMERIC(5,2),
                IN `_max` NUMERIC(5,2),
                IN `_avg` NUMERIC(5,2)
            ) NO SQL BEGIN
            INSERT INTO `supla_em_voltage_log` (`date`,channel_id, phase_no, min, max, avg)
                                        VALUES (_date,_channel_id,_phase_no,_min,_max,_avg);

            END
PROCEDURE
        );
        $this->addSql('CREATE TABLE supla_em_current_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', phase_no TINYINT NOT NULL COMMENT \'(DC2Type:tinyint)\', min NUMERIC(6, 3) NOT NULL, max NUMERIC(6, 3) NOT NULL, avg NUMERIC(6, 3) NOT NULL, PRIMARY KEY(channel_id, date, phase_no)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_add_em_current_log_item`(
                IN `_date` DATETIME, 
                IN `_channel_id` INT(11), 
                IN `_phase_no` TINYINT,
                IN `_min` NUMERIC(6,3),
                IN `_max` NUMERIC(6,3),
                IN `_avg` NUMERIC(6,3)
            ) NO SQL BEGIN
            INSERT INTO `supla_em_current_log` (`date`,channel_id, phase_no, min, max, avg)
                                        VALUES (_date,_channel_id,_phase_no,_min,_max,_avg);

            END
PROCEDURE
        );
        $this->addSql('CREATE TABLE supla_em_power_active_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', phase_no TINYINT NOT NULL COMMENT \'(DC2Type:tinyint)\', min NUMERIC(11, 5) NOT NULL, max NUMERIC(11, 5) NOT NULL, avg NUMERIC(11, 5) NOT NULL, PRIMARY KEY(channel_id, date, phase_no)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_add_em_power_active_log_item`(
                IN `_date` DATETIME, 
                IN `_channel_id` INT(11), 
                IN `_phase_no` TINYINT,
                IN `_min` NUMERIC(11,5),
                IN `_max` NUMERIC(11,5),
                IN `_avg` NUMERIC(11,5)
            ) NO SQL BEGIN
            INSERT INTO `supla_em_power_active_log` (`date`,channel_id, phase_no, min, max, avg)
                                        VALUES (_date,_channel_id,_phase_no,_min,_max,_avg);

            END
PROCEDURE
        );
        $this->addSql('ALTER TABLE `supla_client` ADD `profile_name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AFTER `devel_env`');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_update_push_notification_client_token`');
        $this->addSql('CREATE PROCEDURE `supla_update_push_notification_client_token`(IN `_user_id` INT, IN `_client_id` INT, IN `_token` VARCHAR(255) CHARSET utf8mb4, IN `_platform` TINYINT, IN `_app_id` INT, IN `_devel_env` TINYINT, IN `_profile_name` VARCHAR(50) CHARSET utf8mb4) NOT DETERMINISTIC CONTAINS SQL SQL SECURITY DEFINER UPDATE supla_client SET push_token = _token, push_token_update_time = UTC_TIMESTAMP(), platform = _platform, app_id = _app_id, devel_env = _devel_env, profile_name = _profile_name WHERE id = _client_id AND user_id = _user_id');
        $this->addSql('DROP PROCEDURE `supla_set_channel_caption`');
        $this->addSql('CREATE PROCEDURE `supla_set_channel_caption`(IN `_user_id` INT, IN `_channel_id` INT, IN `_caption` VARCHAR(100) CHARSET utf8mb4, IN `_when_not_null` BIT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER UPDATE supla_dev_channel SET caption = _caption WHERE id = _channel_id AND user_id = _user_id AND (caption IS NOT NULL OR _when_not_null = 0)');
    }
}

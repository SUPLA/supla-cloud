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
 * 1. supla_v_client_channel - Join the value from supla_dev_channel_value regardless of expiration date
 * 2. supla_update_channel_value - First do an update, and if no rows are affected then try to insert
 */
class Version20230426212833 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_channel` AS select `c`.`id` AS `id`,`c`.`type` AS `type`,`c`.`func` AS `func`,ifnull(`c`.`param1`,0) AS `param1`,ifnull(`c`.`param2`,0) AS `param2`,`c`.`caption` AS `caption`,ifnull(`c`.`param3`,0) AS `param3`,ifnull(`c`.`param4`,0) AS `param4`,`c`.`text_param1` AS `text_param1`,`c`.`text_param2` AS `text_param2`,`c`.`text_param3` AS `text_param3`,ifnull(`d`.`manufacturer_id`,0) AS `manufacturer_id`,ifnull(`d`.`product_id`,0) AS `product_id`,ifnull(`c`.`user_icon_id`,0) AS `user_icon_id`,`c`.`user_id` AS `user_id`,`c`.`channel_number` AS `channel_number`,`c`.`iodevice_id` AS `iodevice_id`,`cl`.`id` AS `client_id`,case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end AS `location_id`,ifnull(`c`.`alt_icon`,0) AS `alt_icon`,`d`.`protocol_version` AS `protocol_version`,ifnull(`c`.`flags`,0) AS `flags`,ifnull(`em_subc`.`flags`,0) AS `em_subc_flags`,`v`.`value` AS `value`,CASE WHEN `v`.`valid_to` >= utc_timestamp() THEN time_to_sec(timediff(`v`.`valid_to`,utc_timestamp())) ELSE NULL END AS `validity_time_sec`,`c`.`user_config` AS `user_config`,`em_subc`.`user_config` AS `em_subc_user_config` from (((((((`supla_dev_channel` `c` join `supla_iodevice` `d` on(`d`.`id` = `c`.`iodevice_id`)) join `supla_location` `l` on(`l`.`id` = case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end)) join `supla_rel_aidloc` `r` on(`r`.`location_id` = `l`.`id`)) join `supla_accessid` `a` on(`a`.`id` = `r`.`access_id`)) join `supla_client` `cl` on(`cl`.`access_id` = `r`.`access_id`)) left join `supla_dev_channel_value` `v` on(`c`.`id` = `v`.`channel_id`)) left join `supla_dev_channel` `em_subc` on(`em_subc`.`user_id` = `c`.`user_id` and `em_subc`.`type` = 5000 and ((`c`.`func` = 130 or `c`.`func` = 140) and `c`.`param1` = `em_subc`.`id` or `c`.`func` = 300 and `c`.`param2` = `em_subc`.`id`))) where (`c`.`func` is not null and `c`.`func` <> 0 or `c`.`type` = 8000) and ifnull(`c`.`hidden`,0) = 0 and `d`.`enabled` = 1 and `l`.`enabled` = 1 and `a`.`enabled` = 1');

        $this->addSql('DROP PROCEDURE IF EXISTS `supla_update_channel_value`');

        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_update_channel_value`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_value` VARBINARY(8),
    IN `_validity_time_sec` INT
) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER
BEGIN
    IF _validity_time_sec > 0 THEN
        SET @valid_to = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _validity_time_sec SECOND);
    END IF;
    
    UPDATE `supla_dev_channel_value` SET 
        `update_time` = UTC_TIMESTAMP(), `valid_to` = @valid_to,  `value` = _value
         WHERE user_id = _user_id AND channel_id = _id;
    
    IF ROW_COUNT() = 0 THEN
      INSERT INTO `supla_dev_channel_value` (`channel_id`, `user_id`, `update_time`, `valid_to`, `value`) 
         VALUES(_id, _user_id, UTC_TIMESTAMP(), @valid_to, _value)
      ON DUPLICATE KEY UPDATE `value` = _value, update_time = UTC_TIMESTAMP(), valid_to = @valid_to;
     END IF;
END
PROCEDURE
        );
    }
}

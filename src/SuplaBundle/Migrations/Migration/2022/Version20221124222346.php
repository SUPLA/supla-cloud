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
 * Add supla_oauth_access_tokens.issued_with_refresh_token_id
 * Add scenes.hidden and scenes.user_config
 * Add supla_v_client_channel.em_subc_flags, supla_v_client_channel.em_subc_user_config, supla_v_client_channel.user_config
 */
class Version20221124222346 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD issued_with_refresh_token_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564BD2B4D7C8 FOREIGN KEY (issued_with_refresh_token_id) REFERENCES supla_oauth_refresh_tokens (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2402564BD2B4D7C8 ON supla_oauth_access_tokens (issued_with_refresh_token_id)');
        $this->addSql('ALTER TABLE supla_scene ADD hidden TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE supla_scene ADD user_config VARCHAR(2048) DEFAULT NULL');
        $this->addSql('ALTER ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_channel` AS select `c`.`id` AS `id`,`c`.`type` AS `type`,`c`.`func` AS `func`,ifnull(`c`.`param1`,0) AS `param1`,ifnull(`c`.`param2`,0) AS `param2`,`c`.`caption` AS `caption`,ifnull(`c`.`param3`,0) AS `param3`,ifnull(`c`.`param4`,0) AS `param4`,`c`.`text_param1` AS `text_param1`,`c`.`text_param2` AS `text_param2`,`c`.`text_param3` AS `text_param3`,ifnull(`d`.`manufacturer_id`,0) AS `manufacturer_id`,ifnull(`d`.`product_id`,0) AS `product_id`,ifnull(`c`.`user_icon_id`,0) AS `user_icon_id`,`c`.`user_id` AS `user_id`,`c`.`channel_number` AS `channel_number`,`c`.`iodevice_id` AS `iodevice_id`,`cl`.`id` AS `client_id`,case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end AS `location_id`,ifnull(`c`.`alt_icon`,0) AS `alt_icon`,`d`.`protocol_version` AS `protocol_version`,ifnull(`c`.`flags`,0) AS `flags`,ifnull(`em_subc`.`flags`,0) AS `em_subc_flags`,`v`.`value` AS `value`,time_to_sec(timediff(`v`.`valid_to`,utc_timestamp())) AS `validity_time_sec`, `c`.`user_config`, `em_subc`.`user_config` AS `em_subc_user_config` from ((((((`supla_dev_channel` `c` join `supla_iodevice` `d` on(`d`.`id` = `c`.`iodevice_id`)) join `supla_location` `l` on(`l`.`id` = (case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end))) join `supla_rel_aidloc` `r` on(`r`.`location_id` = `l`.`id`)) join `supla_accessid` `a` on(`a`.`id` = `r`.`access_id`)) join `supla_client` `cl` on(`cl`.`access_id` = `r`.`access_id`)) left join `supla_dev_channel_value` `v` on(`c`.`id` = `v`.`channel_id` and `v`.`valid_to` >= utc_timestamp()) left join `supla_dev_channel` `em_subc` on(`em_subc`.`user_id` = `c`.`user_id` and `em_subc`.`type` = 5000 and (`c`.`func` = 130 or `c`.`func` = 140) and `c`.`param1` = `em_subc`.`id`)) where (`c`.`func` is not null and `c`.`func` <> 0 or `c`.`type` = 8000) and ifnull(`c`.`hidden`,0) = 0 and `d`.`enabled` = 1 and `l`.`enabled` = 1 and `a`.`enabled` = 1');
    }
}

<?php

namespace Supla\Migrations;

/**
 * Channel's param4.
 */
class Version20210323095216 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel ADD param4 INT DEFAULT 0 NOT NULL');
        $this->addSql('DROP VIEW IF EXISTS `supla_v_client_channel`');
        $this->addSql(<<<VIEW
            CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_channel` 
            AS 
            select `c`.`id` AS `id`,`c`.`type` AS `type`,`c`.`func` AS `func`, 
            `c`.`param1` AS `param1`, `c`.`param2` AS `param2`, `c`.`param3` AS `param3`, `c`.`param4` AS `param4`, 
            `c`.`caption` AS `caption`,
            `c`.`text_param1` AS `text_param1`,`c`.`text_param2` AS `text_param2`,`c`.`text_param3` AS `text_param3`,
            ifnull(`d`.`manufacturer_id`, 0) AS `manufacturer_id`,ifnull(`d`.`product_id`, 0) AS `product_id`,ifnull(`c`.`user_icon_id`,0) AS `user_icon_id`,
            `c`.`user_id` AS `user_id`,`c`.`channel_number` AS `channel_number`,`c`.`iodevice_id` AS `iodevice_id`,`cl`.`id` AS `client_id`,
            (case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end) AS `location_id`,
            ifnull(`c`.`alt_icon`,0) AS `alt_icon`,`d`.`protocol_version` AS `protocol_version` 
            FROM
            (((((`supla_dev_channel` `c` join `supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) join `supla_location` `l` on((`l`.`id` = (case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end)))) join `supla_rel_aidloc` `r` on((`r`.`location_id` = `l`.`id`))) join `supla_accessid` `a` on((`a`.`id` = `r`.`access_id`))) join `supla_client` `cl` on((`cl`.`access_id` = `r`.`access_id`))) where ((`c`.`func` is not null) and (ifnull(`c`.`hidden`,0) = 0) and (`c`.`func` <> 0) and (`d`.`enabled` = 1) and (`l`.`enabled` = 1) and (`a`.`enabled` = 1))');
VIEW
        );
    }
}

<?php


namespace Supla\Migrations;

/**
 * New fields text_param2, text_param3 for supla_dev_channel,
 * New fields manufacturer_id, product_id for supal_iodevice,
 * the user_icon_id field has been added to the supla_v_client_channel_group view,
 * fields user_icon_id, text_param1, text_param2 and text_param3 have been added to the supla_v_client_channel view
 */
class Version20181026171557  extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_dev_channel ADD text_param1 VARCHAR(255) DEFAULT NULL AFTER `param3`, ADD text_param2 VARCHAR(255) DEFAULT NULL AFTER `text_param1`, ADD text_param3 VARCHAR(255) DEFAULT NULL AFTER `text_param2`');
        $this->addSql('ALTER TABLE supla_iodevice ADD manufacturer_id smallint DEFAULT NULL, ADD product_id smallint DEFAULT NULL');
        $this->addSql('ALTER ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_channel` AS select `c`.`id` AS `id`,`c`.`type` AS `type`,`c`.`func` AS `func`,`c`.`param1` AS `param1`,`c`.`param2` AS `param2`,`c`.`caption` AS `caption`,`c`.`param3` AS `param3`,`c`.`text_param1` AS `text_param1`,`c`.`text_param2` AS `text_param2`,`c`.`text_param3` AS `text_param3`,`d`.`manufacturer_id` AS `manudacturer_id`,`d`.`product_id` AS `product_id`,`c`.`user_icon_id` AS `user_icon_id`,`c`.`user_id` AS `user_id`,`c`.`channel_number` AS `channel_number`,`c`.`iodevice_id` AS `iodevice_id`,`cl`.`id` AS `client_id`,(case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end) AS `location_id`,ifnull(`c`.`alt_icon`,0) AS `alt_icon`,`d`.`protocol_version` AS `protocol_version` from (((((`supla`.`supla_dev_channel` `c` join `supla`.`supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) join `supla`.`supla_location` `l` on((`l`.`id` = (case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end)))) join `supla`.`supla_rel_aidloc` `r` on((`r`.`location_id` = `l`.`id`))) join `supla`.`supla_accessid` `a` on((`a`.`id` = `r`.`access_id`))) join `supla`.`supla_client` `cl` on((`cl`.`access_id` = `r`.`access_id`))) where ((`c`.`func` is not null) and (ifnull(`c`.`hidden`,0) = 0) and (`c`.`func` <> 0) and (`d`.`enabled` = 1) and (`l`.`enabled` = 1) and (`a`.`enabled` = 1))');
        $this->addSql('ALTER ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW  `supla_v_client_channel_group` AS select `g`.`id` AS `id`,`g`.`func` AS `func`,`g`.`caption` AS `caption`,`g`.`user_id` AS `user_id`,`g`.`location_id` AS `location_id`,ifnull(`g`.`alt_icon`,0) AS `alt_icon`,ifnull(`g`.`user_icon_id`, 0) AS `user_icon_id`,`cl`.`id` AS `client_id` from ((((`supla`.`supla_dev_channel_group` `g` join `supla`.`supla_location` `l` on((`l`.`id` = `g`.`location_id`))) join `supla`.`supla_rel_aidloc` `r` on((`r`.`location_id` = `l`.`id`))) join `supla`.`supla_accessid` `a` on((`a`.`id` = `r`.`access_id`))) join `supla`.`supla_client` `cl` on((`cl`.`access_id` = `r`.`access_id`))) where ((`g`.`func` is not null) and (ifnull(`g`.`hidden`,0) = 0) and (`g`.`func` <> 0) and (`l`.`enabled` = 1) and (`a`.`enabled` = 1))');
    }
}

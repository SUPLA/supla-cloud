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
            CREATE algorithm = undefined SQL security definer view `supla_v_client_channel` 
            AS 
              SELECT `c`.`id`                         AS `id`, 
                     `c`.`type`                       AS `type`, 
                     `c`.`func`                       AS `func`, 
                     ifnull(`c`.`param1`, 0)          AS `param1`, 
                     ifnull(`c`.`param2`, 0)          AS `param2`, 
                     ifnull(`c`.`param3`, 0)          AS `param3`, 
                     ifnull(`c`.`param4`, 0)          AS `param4`, 
                     `c`.`caption`                    AS `caption`, 
                     `c`.`text_param1`                AS `text_param1`, 
                     `c`.`text_param2`                AS `text_param2`, 
                     `c`.`text_param3`                AS `text_param3`, 
                     ifnull(`d`.`manufacturer_id`, 0) AS `manufacturer_id`, 
                     ifnull(`d`.`product_id`, 0)      AS `product_id`, 
                     ifnull(`c`.`user_icon_id`,0)     AS `user_icon_id`, 
                     `c`.`user_id`                    AS `user_id`, 
                     `c`.`channel_number`             AS `channel_number`, 
                     `c`.`iodevice_id`                AS `iodevice_id`, 
                     `cl`.`id`                        AS `client_id`,( 
                     CASE ifnull(`c`.`location_id`,0) WHEN 0 THEN `d`.`location_id` ELSE `c`.`location_id` end) AS `location_id`, 
                     ifnull(`c`.`alt_icon`,0) AS `alt_icon`, 
                     `d`.`protocol_version`   AS `protocol_version` 
              FROM   (((((`supla_dev_channel` `c` 
              JOIN   `supla_iodevice` `d` ON (( `d`.`id` = `c`.`iodevice_id`))) 
              JOIN   `supla_location` `l` ON (( `l`.`id` = (CASE ifnull(`c`.`location_id`,0) WHEN 0 THEN `d`.`location_id` ELSE `c`.`location_id` end)))) 
              JOIN   `supla_rel_aidloc` `r` ON (( `r`.`location_id` = `l`.`id`))) 
              JOIN   `supla_accessid` `a` ON (( `a`.`id` = `r`.`access_id`))) 
              JOIN   `supla_client` `cl` ON (( `cl`.`access_id` = `r`.`access_id`))) 
              WHERE  (( 
                          `c`.`func` IS NOT NULL) 
                     AND ( ifnull(`c`.`hidden`,0) = 0) 
                     AND ( `c`.`func` <> 0) 
                     AND ( `d`.`enabled` = 1) 
                     AND ( `l`.`enabled` = 1) 
                     AND ( `a`.`enabled` = 1))
VIEW
        );

    }
}

<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * 1. https://github.com/SUPLA/supla-core/issues/334
 * 2. The view should return "BRIDGE" type channels even if they have no function assigned
 */
class Version20200108201101 extends NoWayBackMigration {
    public function migrate() {
        $ic = ChannelType::IMPULSECOUNTER();
        $this->addSql("UPDATE `supla_dev_channel` SET param1 = param1 * 100 WHERE type=$ic AND param1 > 0");
        $this->addSql("DROP VIEW IF EXISTS `supla_v_client_channel`");
        $this->addSql('CREATE ALGORITHM = UNDEFINED VIEW `supla_v_client_channel` AS select `c`.`id` AS `id`,`c`.`type` AS `type`,`c`.`func` AS `func`,ifnull(`c`.`param1`,0) AS `param1`,ifnull(`c`.`param2`,0) AS `param2`,`c`.`caption` AS `caption`,ifnull(`c`.`param3`,0) AS `param3`,`c`.`text_param1` AS `text_param1`,`c`.`text_param2` AS `text_param2`,`c`.`text_param3` AS `text_param3`,ifnull(`d`.`manufacturer_id`,0) AS `manufacturer_id`,ifnull(`d`.`product_id`,0) AS `product_id`,ifnull(`c`.`user_icon_id`,0) AS `user_icon_id`,`c`.`user_id` AS `user_id`,`c`.`channel_number` AS `channel_number`,`c`.`iodevice_id` AS `iodevice_id`,`cl`.`id` AS `client_id`,case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end AS `location_id`,ifnull(`c`.`alt_icon`,0) AS `alt_icon`,`d`.`protocol_version` AS `protocol_version`, ifnull(`c`.`flags`,0) AS `flags` from (((((`supla_dev_channel` `c` join `supla_iodevice` `d` on(`d`.`id` = `c`.`iodevice_id`)) join `supla_location` `l` on(`l`.`id` = (case ifnull(`c`.`location_id`,0) when 0 then `d`.`location_id` else `c`.`location_id` end))) join `supla_rel_aidloc` `r` on(`r`.`location_id` = `l`.`id`)) join `supla_accessid` `a` on(`a`.`id` = `r`.`access_id`)) join `supla_client` `cl` on(`cl`.`access_id` = `r`.`access_id`)) where ((`c`.`func` is not null and `c`.`func` <> 0) or `c`.`type` = 8000) and ifnull(`c`.`hidden`,0) = 0 and `d`.`enabled` = 1 and `l`.`enabled` = 1 and `a`.`enabled` = 1');
    }
}

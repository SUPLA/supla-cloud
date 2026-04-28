<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

class Version20181129231132 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP VIEW IF EXISTS `supla_v_client_channel_group`');
        $this->addSql('CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW  `supla_v_client_channel_group` AS select `g`.`id` AS `id`,`g`.`func` AS `func`,`g`.`caption` AS `caption`,`g`.`user_id` AS `user_id`,`g`.`location_id` AS `location_id`,ifnull(`g`.`alt_icon`,0) AS `alt_icon`,ifnull(`g`.`user_icon_id`, 0) AS `user_icon_id`,`cl`.`id` AS `client_id` from ((((`supla_dev_channel_group` `g` join `supla_location` `l` on((`l`.`id` = `g`.`location_id`))) join `supla_rel_aidloc` `r` on((`r`.`location_id` = `l`.`id`))) join `supla_accessid` `a` on((`a`.`id` = `r`.`access_id`))) join `supla_client` `cl` on((`cl`.`access_id` = `r`.`access_id`))) where ((`g`.`func` is not null) and (ifnull(`g`.`hidden`,0) = 0) and (`g`.`func` <> 0) and (`l`.`enabled` = 1) and (`a`.`enabled` = 1))');
    }
}

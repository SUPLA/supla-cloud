<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * The hardcoded database name has been removed from the view.
 */
class Version20200414213205 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP VIEW IF EXISTS `supla_v_rel_cg`');
        $this->addSql('CREATE ALGORITHM = UNDEFINED VIEW `supla_v_rel_cg` AS select `r`.`group_id` AS `group_id`,`r`.`channel_id` AS `channel_id`,`c`.`iodevice_id` AS `iodevice_id`,`d`.`protocol_version` AS `protocol_version`,`g`.`client_id` AS `client_id`,`c`.`hidden` AS `channel_hidden` from (((`supla_v_client_channel_group` `g` join `supla_rel_cg` `r` on(`r`.`group_id` = `g`.`id`)) join `supla_dev_channel` `c` on(`c`.`id` = `r`.`channel_id`)) join `supla_iodevice` `d` on(`d`.`id` = `c`.`iodevice_id`)) where `d`.`enabled` = 1');
    }
}

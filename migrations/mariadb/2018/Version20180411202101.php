<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add views for channel group processing
 */
class Version20180411202101 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql(<<<CREATE_VIEW
				CREATE
				 ALGORITHM = UNDEFINED
				 VIEW `supla_v_client_channel_group`
				 AS SELECT `g`.`id` AS `id`,
				 `g`.`func` AS `func`,
				 `g`.`caption` AS `caption`,
				 `g`.`user_id` AS `user_id`,
				 `g`.`location_id`,
				 ifnull(`g`.`alt_icon`,0) AS `alt_icon`,
				 `cl`.`id` AS `client_id`
				FROM `supla_dev_channel_group` `g`
				  JOIN `supla_location` `l` on(`l`.`id` = `g`.`location_id`)
				  JOIN `supla_rel_aidloc` `r` on(`r`.`location_id` = `l`.`id`)
				  JOIN `supla_accessid` `a` on(`a`.`id` = `r`.`access_id`)
				  JOIN `supla_client` `cl` on(`cl`.`access_id` = `r`.`access_id`)
				WHERE ((`g`.`func` IS NOT NULL)
				  AND (ifnull(`g`.`hidden`,0) = 0)
				  AND (`g`.`func` <> 0)
				  AND (`l`.`enabled` = 1)
				  AND (`a`.`enabled` = 1))
CREATE_VIEW
        );

        $this->addSql(<<<CREATE_VIEW
				CREATE
				 ALGORITHM = UNDEFINED
				 VIEW `supla_v_rel_cg`
				 AS SELECT `r`.`group_id` AS `group_id`,`r`.`channel_id` AS `channel_id`,
                 `c`.`iodevice_id` AS `iodevice_id`,`d`.`protocol_version` AS `protocol_version`,
                 `g`.`client_id` AS `client_id`,`c`.`hidden` AS `channel_hidden` from 
                 (((`supla_v_client_channel_group` `g` join `supla_rel_cg` `r` on((`r`.`group_id` = `g`.`id`))) 
                 join `supla_dev_channel` `c` on((`c`.`id` = `r`.`channel_id`))) 
                 join `supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) where `d`.`enabled` = 1
CREATE_VIEW
        );
    }
}

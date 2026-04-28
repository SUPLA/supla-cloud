<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add views for channel group processing
 */
class Version20180416201401 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql(<<<CREATE_VIEW
				CREATE
				 ALGORITHM = UNDEFINED
				 VIEW `supla_v_user_channel_group`  
				 AS  select `g`.`id` AS `id`,`g`.`func` AS `func`,`g`.`caption` AS `caption`,`g`.`user_id` AS `user_id`,
				 `g`.`location_id` AS `location_id`,ifnull(`g`.`alt_icon`,0) AS `alt_icon`,
				 `rel`.`channel_id` AS `channel_id`,`c`.`iodevice_id` AS `iodevice_id` 
				 from ((((`supla_dev_channel_group` `g` join `supla_location` `l` on((`l`.`id` = `g`.`location_id`))) 
				 join `supla_rel_cg` `rel` on((`rel`.`group_id` = `g`.`id`))) 
				 join `supla_dev_channel` `c` on((`c`.`id` = `rel`.`channel_id`))) 
				 join `supla_iodevice` `d` on((`d`.`id` = `c`.`iodevice_id`))) 
				 where ((`g`.`func` is not null) and (ifnull(`g`.`hidden`,0) = 0) and (`g`.`func` <> 0) 
				 and (`l`.`enabled` = 1) and (`d`.`enabled` = 1)) 
CREATE_VIEW
        );
    }
}

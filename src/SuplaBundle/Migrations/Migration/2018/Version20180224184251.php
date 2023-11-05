<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Auto-generated Migration: supla_v_client_channel - skip hidden channels. Include channel location.
 */
class Version20180224184251 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql(<<<VIEW
						CREATE OR REPLACE algorithm=undefined SQL SECURITY DEFINER VIEW `supla_v_client_channel` AS
						SELECT `c`.`id` AS `id`,
						       `c`.`type` AS `type`,
						       `c`.`func` AS `func`,
						       `c`.`param1` AS `param1`,
						       `c`.`param2` AS `param2`,
						       `c`.`caption` AS `caption`,
						       `c`.`param3` AS `param3`,
						       `c`.`user_id` AS `user_id`,
						       `c`.`channel_number` AS `channel_number`,
						       `c`.`iodevice_id` AS `iodevice_id`,
						       `cl`.`id` AS `client_id`,
						       (CASE ifnull(`c`.`location_id`,0)
						            WHEN 0 THEN `d`.`location_id`
						            ELSE `c`.`location_id`
						        END) AS `location_id`,
						       ifnull(`c`.`alt_icon`,0) AS `alt_icon`,
						       `d`.`protocol_version` AS `protocol_version`
						FROM `supla_dev_channel` `c`
						      JOIN `supla_iodevice` `d` ON (`d`.`id` = `c`.`iodevice_id`)
						      JOIN `supla_location` `l` ON (`l`.`id` = CASE ifnull(`c`.`location_id`,0)
						                                                        WHEN 0 THEN `d`.`location_id`
						                                                        ELSE `c`.`location_id`
						                                                    END)
						      JOIN `supla_rel_aidloc` `r` ON (`r`.`location_id` = `l`.`id`)
						      JOIN `supla_accessid` `a` ON (`a`.`id` = `r`.`access_id`)
						      JOIN `supla_client` `cl` ON (`cl`.`access_id` = `r`.`access_id`)
						WHERE (`c`.`func` IS NOT NULL)
						       AND (ifnull(`c`.`hidden`,0) = 0)
						       AND (`c`.`func` <> 0)
						       AND (`d`.`enabled` = 1)
						       AND (`l`.`enabled` = 1)
						       AND (`a`.`enabled` = 1);
VIEW
        );
    }
}

<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * 1. https://github.com/SUPLA/supla-core/issues/115
 * 2. https://github.com/SUPLA/supla-core/issues/116
 * 3. https://github.com/SUPLA/supla-cloud/issues/338
 */
class Version20191226160845 extends NoWayBackMigration {
    public function migrate() {
        $em = ChannelFunction::ELECTRICITYMETER;
        $ic = ChannelFunction::IC_ELECTRICITYMETER;
        $this->addSql("UPDATE `supla_dev_channel` SET func=$ic WHERE type=5010 AND func=$em");
        $this->addSql("INSERT INTO supla_user_icons (user_id, func, image1) SELECT user_id, $ic, image1 FROM supla_user_icons WHERE func = $em");
        $this->addSql("UPDATE supla_dev_channel c SET user_icon_id=(SELECT MAX(id) FROM supla_user_icons WHERE func=$ic AND user_id = c.user_id AND image1=(SELECT image1 FROM supla_user_icons WHERE id=c.user_icon_id)) WHERE func=$ic AND user_icon_id IS NOT NULL;");
        $this->addSql("DROP VIEW IF EXISTS `supla_v_user_channel_group`");
        $this->addSql('CREATE ALGORITHM = UNDEFINED VIEW `supla_v_user_channel_group` AS select `g`.`id` AS `id`,`g`.`func` AS `func`,`g`.`caption` AS `caption`,`g`.`user_id` AS `user_id`,`g`.`location_id` AS `location_id`,ifnull(`g`.`alt_icon`,0) AS `alt_icon`,`rel`.`channel_id` AS `channel_id`,`c`.`iodevice_id` AS `iodevice_id`,ifnull(`g`.`hidden`,0) AS `hidden` from ((((`supla_dev_channel_group` `g` join `supla_location` `l` on(`l`.`id` = `g`.`location_id`)) join `supla_rel_cg` `rel` on(`rel`.`group_id` = `g`.`id`)) join `supla_dev_channel` `c` on(`c`.`id` = `rel`.`channel_id`)) join `supla_iodevice` `d` on(`d`.`id` = `c`.`iodevice_id`)) where `g`.`func` is not null and `g`.`func` <> 0 and `l`.`enabled` = 1 and `d`.`enabled` = 1');
        $this->addSql('ALTER TABLE supla_dev_channel ADD config LONGTEXT DEFAULT NULL');
    }
}

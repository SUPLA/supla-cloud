<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * supla_set_channel_function - device_id changed to user_id
 * New procedure supla_set_channel_caption
 */
class Version20200412183701 extends NoWayBackMigration {
    public function migrate() {
        $bridge = ChannelType::BRIDGE;
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_set_channel_function`');
        $this->addSql("CREATE PROCEDURE `supla_set_channel_function`(IN `_user_id` INT, IN `_channel_id` INT, IN `_func` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER UPDATE supla_dev_channel SET func = _func WHERE id = _channel_id AND user_id = _user_id AND type = $bridge");
        $this->addSql("CREATE PROCEDURE `supla_set_channel_caption`(IN `_user_id` INT, IN `_channel_id` INT, IN `_caption` VARCHAR(100) CHARSET utf8mb4) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER UPDATE supla_dev_channel SET caption = _caption WHERE id = _channel_id AND user_id = _user_id");
    }
}

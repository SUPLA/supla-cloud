<?php

namespace Supla\Migrations;

use SuplaBundle\Enums\ChannelType;

/**
 * New procedure to change the channel function
 */
class Version20200122200601 extends NoWayBackMigration {
    public function migrate() {
        $bridge = ChannelType::BRIDGE;
        $this->addSql("CREATE PROCEDURE `supla_set_channel_function`(IN `_device_id` INT, IN `_channel_id` INT, IN `_func` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER UPDATE supla_dev_channel SET func = _func WHERE id = _channel_id AND iodevice_id = _device_id AND type = $bridge");
    }
}

<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * New procedure added "supla_set_location_caption"
 */
class Version20210228201414 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_set_location_caption`');
        $this->addSql("CREATE PROCEDURE `supla_set_location_caption`(IN `_user_id` INT, IN `_location_id` INT, IN `_caption` VARCHAR(100) CHARSET utf8mb4) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER UPDATE supla_location SET caption = _caption WHERE id = _location_id AND user_id = _user_id");
    }
}

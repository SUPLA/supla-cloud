<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * 1. New procedure added "supla_set_scene_caption"
 * 2. Change "caption" charset to utf8mb4_unicode_ci
 */
class Version20221005003914 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql("CREATE PROCEDURE `supla_set_scene_caption`(IN `_user_id` INT, IN `_scene_id` INT, IN `_caption` VARCHAR(255) CHARSET utf8mb4) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER UPDATE supla_scene SET caption = _caption WHERE id = _scene_id AND user_id = _user_id");
        $this->addSql('ALTER TABLE `supla_scene` CHANGE `caption` `caption` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL');
    }
}

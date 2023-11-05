<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Create procedure supla_update_google_home
 */
class Version20190105130410 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_update_google_home`');
        $this->addSql('CREATE PROCEDURE `supla_update_google_home`(IN `_access_token` VARCHAR(255) CHARSET utf8, IN `_user_id` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN UPDATE supla_google_home SET `access_token` = _access_token WHERE `user_id` = _user_id; END');
    }
}

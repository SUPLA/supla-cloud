<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * New supla_update_state_webhook PROCEDURE
 */
class Version20200911231401 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE PROCEDURE `supla_update_state_webhook`(IN `_access_token` VARCHAR(255) CHARSET utf8, IN `_refresh_token` VARCHAR(255) CHARSET utf8, IN `_expires_in` INT, IN `_user_id` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN UPDATE supla_state_webhooks SET `access_token` = _access_token, `refresh_token` = _refresh_token, `expires_at` = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _expires_in second) WHERE `user_id` = _user_id; END');
    }
}

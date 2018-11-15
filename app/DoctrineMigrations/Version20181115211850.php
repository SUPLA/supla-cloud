<?php

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * New stored procedure for deleting and updating alexa gateway credentials.
 */
class Version20181115211850 extends NoWayBackMigration
{
    public function migrate() {
        $this->addSql('CREATE PROCEDURE `supla_delete_alexa_egc`(IN `_user_id` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN DELETE FROM supla_alexa_egc WHERE `user_id` = _user_id; END');
        $this->addSql('CREATE PROCEDURE `supla_update_alexa_egc`(IN `_access_token` VARCHAR(1024) CHARSET utf8, IN `_refresh_token` VARCHAR(1024) CHARSET utf8, IN `_expires_in` INT, IN `_user_id` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN UPDATE supla_alexa_egc SET `access_token` = _access_token, `refresh_token` = _refresh_token, `expires_at` = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _expires_in second) WHERE `user_id` = _user_id; END');
    }

}

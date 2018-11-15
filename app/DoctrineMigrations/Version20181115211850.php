<?php

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * New stored procedure for deleting alexa gateway credentials.
 */
class Version20181115211850 extends NoWayBackMigration
{
    public function migrate() {
        $this->addSql('CREATE PROCEDURE `supla_delete_alexa_egc`(IN `_user_id` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN DELETE FROM supla_alexa_egc WHERE `user_id` = _user_id; END');
    }

}

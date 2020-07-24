<?php

namespace Supla\Migrations;

/**
 * Time calculation method corrected. Should correctly be calculated based on UTC_TIMESTAMP instead of NOW ()
 */
class Version20200123235701 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_set_registration_enabled`');
        $this->addSql("CREATE PROCEDURE `supla_set_registration_enabled`(IN `user_id` INT, IN `iodevice_sec` INT, IN `client_sec` INT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN IF iodevice_sec >= 0 THEN SET @date = NULL; IF iodevice_sec > 0 THEN SET @date = DATE_ADD(UTC_TIMESTAMP, INTERVAL iodevice_sec SECOND); END IF; UPDATE supla_user SET iodevice_reg_enabled = @date WHERE id = user_id; END IF; IF client_sec >= 0 THEN SET @date = NULL; IF client_sec > 0 THEN SET @date = DATE_ADD(UTC_TIMESTAMP, INTERVAL client_sec SECOND); END IF; UPDATE supla_user SET client_reg_enabled = @date WHERE id = user_id; END IF; END");
    }
}

<?php

namespace Supla\Migrations;

/**
 * OAuth mqtt_broker_auth_password.
 */
class Version20210118124714 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_oauth_client_authorizations ADD mqtt_broker_auth_password VARCHAR(128) DEFAULT NULL');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_mqtt_broker_auth`');
        $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_mqtt_broker_auth`(IN `in_suid` VARCHAR(255) CHARSET utf8, IN `in_password` VARCHAR(255) CHARSET utf8)
            NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER
            SELECT 1 FROM supla_user WHERE mqtt_broker_enabled = 1 AND short_unique_id = BINARY in_suid COLLATE utf8_general_ci 
            AND mqtt_broker_auth_password = SHA2(in_password COLLATE utf8_general_ci, 512);
PROCEDURE
        );
    }
}

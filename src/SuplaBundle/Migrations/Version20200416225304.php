<?php

namespace SuplaBundle\Migrations;

/**
 *  supla_add_client - Skipping checking if registration is enabled.
 */
class Version20200416225304 extends NoWayBackMigration {
    public function migrate() {

        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_client`');
        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_add_client` (IN `_access_id` INT(11), IN `_guid` VARBINARY(16), IN `_name` VARCHAR(100) CHARSET utf8mb4, IN `_reg_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20) CHARSET utf8, IN `_protocol_version` INT(11), IN `_user_id` INT(11), IN `_auth_key` VARCHAR(64) CHARSET utf8, OUT `_id` INT(11))  NO SQL
BEGIN

IF EXISTS(SELECT 1 FROM `supla_user` WHERE `id` = _user_id) THEN

INSERT INTO `supla_client`(`access_id`, `guid`, `name`, `enabled`, `reg_ipv4`, `reg_date`, `last_access_ipv4`,
`last_access_date`,`software_version`, `protocol_version`, `user_id`, `auth_key`)
VALUES (_access_id, _guid, _name, 1, _reg_ipv4, UTC_TIMESTAMP(), _reg_ipv4, UTC_TIMESTAMP(), _software_version, _protocol_version,
    _user_id, _auth_key);

SELECT LAST_INSERT_ID() INTO _id;

END IF;
END
PROCEDURE
        );
    }
}

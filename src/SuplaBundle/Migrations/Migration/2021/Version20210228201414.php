<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * 1. supla_add_client:
 * Removed verification if registration is enabled, due to the possibility of client
 * registration without logging into cloud.supla.org after providing the
 * login and password of the superuser.
 *
 * 2. New procedure added "supla_set_location_caption"
 */
class Version20210228201414 extends NoWayBackMigration {

    public function migrate() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_client`');
        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_add_client` (IN `_access_id` INT(11), IN `_guid` VARBINARY(16), IN `_name` VARCHAR(100) CHARSET utf8, 
IN `_reg_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20) CHARSET utf8, IN `_protocol_version` INT(11), IN `_user_id` INT(11), 
IN `_auth_key` VARCHAR(64) CHARSET utf8, OUT `_id` INT(11))  NO SQL
BEGIN

INSERT INTO `supla_client`(`access_id`, `guid`, `name`, `enabled`, `reg_ipv4`, `reg_date`, `last_access_ipv4`, 
`last_access_date`,`software_version`, `protocol_version`, `user_id`, `auth_key`) 
VALUES (_access_id, _guid, _name, 1, _reg_ipv4, UTC_TIMESTAMP(), _reg_ipv4, UTC_TIMESTAMP(), _software_version, _protocol_version, 
_user_id, _auth_key);

SELECT LAST_INSERT_ID() INTO _id;

END
PROCEDURE
        );

        $this->addSql('DROP PROCEDURE IF EXISTS `supla_set_location_caption`');
        $this->addSql("CREATE PROCEDURE `supla_set_location_caption`(IN `_user_id` INT, IN `_location_id` INT, IN `_caption` VARCHAR(100) CHARSET utf8mb4) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER UPDATE supla_location SET caption = _caption WHERE id = _location_id AND user_id = _user_id");
    }
}

<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Supla\Migrations;

/**
 * Add notification about new client connected.
 */
class Version20211218174444 extends NoWayBackMigration {
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

SELECT CONCAT('{"template": "new_client_app", "userId": ', _user_id, ', "data": {"clientAppId": ', _id, '}}') INTO @notification_data;
INSERT INTO `supla_email_notifications` (`body`, `headers`, `queue_name`, `created_at`, `available_at`)
VALUES(@notification_data, '[]', 'supla-server', NOW(), NOW());

END
PROCEDURE
        );
    }
}

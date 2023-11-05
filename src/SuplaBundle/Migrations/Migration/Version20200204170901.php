<?php

namespace SuplaBundle\Migrations\Migration;

/**
 * Solves the problem of too small version field size. #353
 */
class Version20200204170901 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE `esp_update` CHANGE `latest_software_version` `latest_software_version` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL');
        $this->addSql('ALTER TABLE `supla_iodevice` CHANGE `software_version` `software_version` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_iodevice`');
        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_add_iodevice` (IN `_location_id` INT(11), IN `_user_id` INT(11), IN `_guid` VARBINARY(16), IN `_name` VARCHAR(100) CHARSET utf8, IN `_reg_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20), IN `_protocol_version` INT(11), IN `_product_id` SMALLINT, IN `_manufacturer_id` SMALLINT, IN `_original_location_id` INT(11), IN `_auth_key` VARCHAR(64), IN `_flags` INT(11), OUT `_id` INT(11))  NO SQL
BEGIN

SET @mfr_id = _manufacturer_id;

IF _manufacturer_id = 0 THEN
  IF _name LIKE '%sonoff%' THEN SELECT 6 INTO @mfr_id; END IF;
  IF _name LIKE 'NICE %' THEN SELECT  5 INTO @mfr_id; END IF;
  IF _name LIKE 'ZAMEL %' THEN SELECT 4 INTO @mfr_id; END IF;
END IF;

INSERT INTO `supla_iodevice`(`location_id`, `user_id`, `guid`, `name`, `enabled`, `reg_date`, `reg_ipv4`, `last_connected`, `last_ipv4`, 
`software_version`, `protocol_version`, `manufacturer_id`, `product_id`, `original_location_id`, `auth_key`, `flags`) 
VALUES (_location_id, _user_id, _guid, _name, 1, UTC_TIMESTAMP(), _reg_ipv4, UTC_TIMESTAMP(), _reg_ipv4, _software_version, 
_protocol_version, @mfr_id, _product_id, _original_location_id, _auth_key, _flags);

SELECT LAST_INSERT_ID() INTO _id;

END
PROCEDURE
        );

        $this->addSql('DROP PROCEDURE IF EXISTS `supla_update_iodevice`');
        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_update_iodevice` (IN `_name` VARCHAR(100) CHARSET utf8, IN `_last_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20) CHARSET utf8, IN `_protocol_version` INT(11), IN `_original_location_id` INT(11), IN `_auth_key` VARCHAR(64) CHARSET utf8, IN `_id` INT(11))  NO SQL
BEGIN

UPDATE `supla_iodevice` 
SET 
`name` = _name, 
`last_connected` = UTC_TIMESTAMP(),
`last_ipv4` = _last_ipv4, 
`software_version` = _software_version, 
`protocol_version` = _protocol_version, 
original_location_id = _original_location_id WHERE `id` = _id;

IF _auth_key IS NOT NULL THEN
  UPDATE `supla_iodevice` 
  SET `auth_key` = _auth_key WHERE `id` = _id AND `auth_key` IS NULL;
END IF;

END
PROCEDURE
        );
    }
}

<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Adding two columns to the esp_update table.
 * Modification of supla_get_device_firmware_url so that it checks new columns.
 */
class Version20211123193415 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE `esp_update` ADD `fparam3` INT NOT NULL DEFAULT 0 AFTER `fparam2`, ADD `fparam4` INT NOT NULL DEFAULT 0 AFTER `fparam3`');
        $this->addSql('ALTER TABLE `esp_update` ADD INDEX(`fparam3`)');
        $this->addSql('ALTER TABLE `esp_update` ADD INDEX(`fparam4`)');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_get_device_firmware_url`');
        $this->addSql(<<<PROCEDURE
CREATE PROCEDURE `supla_get_device_firmware_url`(IN `in_device_id` INT, IN `in_platform` TINYINT, IN `in_fparam1` INT, IN `in_fparam2` INT, IN `in_fparam3` INT, IN `in_fparam4` INT, OUT `out_protocols` TINYINT, OUT `out_host` VARCHAR(100), OUT `out_port` INT, OUT `out_path` VARCHAR(100))
    NO SQL
BEGIN

SET @var_protocols = 0;
SET @var_host = '';
SET @var_port = 0;
SET @var_path = '';

SET @fparam1 = in_fparam1;
SET @fparam2 = in_fparam2;
SET @fparam3 = in_fparam3;
SET @fparam4 = in_fparam4;
SET @platform = in_platform;
SET @device_id = in_device_id;

SET @update_count = 0;
SELECT COUNT(*) INTO @update_count FROM `esp_update_log` WHERE `device_id` = @device_id AND `date`  + INTERVAL 30 MINUTE >= NOW();

IF @update_count = 0 THEN

SELECT u.`protocols`, u.`host`, u.`port`, u.`path` INTO @var_protocols, @var_host, @var_port, @var_path FROM supla_iodevice AS d, esp_update AS u WHERE d.id = @device_id AND u.`platform` = @platform AND u.`fparam1` = @fparam1 AND u.`fparam2` = @fparam2 AND @fparam3 = u.`fparam3` AND @fparam4 = u.`fparam4` AND u.`device_name` = d.name AND u.`latest_software_version` != d.`software_version` AND 

(
version_to_int(d.`software_version`) = 0 OR
version_to_int(u.`latest_software_version`) = 0 OR
version_to_int(u.`latest_software_version`) > version_to_int(d.`software_version`)
)

AND ( u.`device_id` = 0 OR u.`device_id` = @device_id ) LIMIT 1;

END IF;

INSERT INTO `esp_update_log`(`date`, `device_id`, `platform`, `fparam1`, `fparam2`, `fparam3`, `fparam4`) VALUES (NOW(),in_device_id,in_platform,in_fparam1,in_fparam2,in_fparam3,in_fparam4);

SET out_protocols = @var_protocols;
SET out_host = @var_host;
SET out_port = @var_port;
SET out_path = @var_path;

END
PROCEDURE
        );
    }
}

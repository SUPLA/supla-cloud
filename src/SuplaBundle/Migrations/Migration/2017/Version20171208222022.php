<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add possibly missing procedures and views to the database.
 */
class Version20171208222022 extends NoWayBackMigration {
    public function migrate() {
        $this->createSuplaServerViews();
        $this->createSuplaServerProcedures();
    }

    private function createSuplaServerViews() {
        $this->addSql(<<<VIEW
        CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client` AS  
        SELECT `c`.`id` AS `id`,`c`.`access_id` AS `access_id`,`c`.`guid` AS `guid`,`c`.`name` AS `name`,`c`.`reg_ipv4` AS `reg_ipv4`,
               `c`.`reg_date` AS `reg_date`,`c`.`last_access_ipv4` AS `last_access_ipv4`,`c`.`last_access_date` AS `last_access_date`,
               `c`.`software_version` AS `software_version`,`c`.`protocol_version` AS `protocol_version`,`c`.`enabled` AS `enabled`,
               `a`.`user_id` AS `user_id` 
        FROM (`supla_client` `c` JOIN `supla_accessid` `a` ON((`a`.`id` = `c`.`access_id`))) ;
VIEW
        );
        $this->addSql(<<<VIEW
        CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_channel` AS  
        SELECT `c`.`id` AS `id`,`c`.`type` AS `type`,`c`.`func` AS `func`,`c`.`param1` AS `param1`,`c`.`param2` AS `param2`,
               `c`.`caption` AS `caption`,`c`.`param3` AS `param3`,`c`.`user_id` AS `user_id`,`c`.`channel_number` AS `channel_number`,
               `c`.`iodevice_id` AS `iodevice_id`,`cl`.`id` AS `client_id`,`r`.`location_id` AS `location_id`,
               ifnull(`c`.`alt_icon`,0) AS `alt_icon`,`d`.`protocol_version` AS `protocol_version` 
        FROM (((((`supla_dev_channel` `c` JOIN `supla_iodevice` `d` ON((`d`.`id` = `c`.`iodevice_id`))) 
        JOIN `supla_location` `l` ON((`l`.`id` = `d`.`location_id`))) JOIN `supla_rel_aidloc` `r` ON((`r`.`location_id` = `l`.`id`))) 
        JOIN `supla_accessid` `a` ON((`a`.`id` = `r`.`access_id`))) JOIN `supla_client` `cl` ON((`cl`.`access_id` = `r`.`access_id`)))
        WHERE ((`c`.`func` is not null) AND (`c`.`func` <> 0) AND (`d`.`enabled` = 1) AND (`l`.`enabled` = 1) AND (`a`.`enabled` = 1));
VIEW
        );
        $this->addSql(<<<VIEW
        CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_location` AS 
        SELECT `l`.`id` AS `id`,`l`.`caption` AS `caption`,`c`.`id` AS `client_id` 
        FROM ((`supla_rel_aidloc` `al` JOIN `supla_location` `l` ON((`l`.`id` = `al`.`location_id`))) 
        JOIN `supla_client` `c` ON((`c`.`access_id` = `al`.`access_id`))) 
        WHERE (`l`.`enabled` = 1);
VIEW
        );
        $this->addSql(<<<VIEW
        CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_device_accessid` AS
        SELECT `a`.`id` AS `id`,`a`.`user_id` AS `user_id`,cast(`a`.`enabled` as unsigned) AS `enabled`,`a`.`password` AS `password`,
               `u`.`limit_client` AS `limit_client` 
        FROM (`supla_accessid` `a` JOIN `supla_user` `u` ON((`u`.`id` = `a`.`user_id`)));
VIEW
        );
        $this->addSql(<<<VIEW
        CREATE OR REPLACE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_device_location` AS
        SELECT `l`.`id` AS `id`,`l`.`user_id` AS `user_id`,cast(`l`.`enabled` as unsigned) AS `enabled`,`u`.`limit_iodev` AS `limit_iodev`,
               `l`.`password` AS `password`
        FROM (`supla_location` `l` JOIN `supla_user` `u` ON((`u`.`id` = `l`.`user_id`)));
VIEW
        );
    }

    private function createSuplaServerProcedures() {
        if (!$this->procedureExists('supla_on_channeladded')) {
            $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_on_channeladded`(IN `_device_id` INT, IN `_channel_id` INT) NO SQL
            BEGIN
                SET @type = NULL;
                SELECT type INTO @type FROM supla_dev_channel WHERE `func` = 0 AND id = _channel_id;
                IF @type = 3000 THEN
                    UPDATE supla_dev_channel SET `func` = 40 WHERE id = _channel_id;
                END IF;
            END;
PROCEDURE
            );
        }
        if (!$this->procedureExists('supla_on_newdevice')) {
            $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_on_newdevice`(IN `_device_id` INT) MODIFIES SQL DATA
            BEGIN
            END;
PROCEDURE
            );
        }
        if (!$this->procedureExists('supla_get_device_firmware_url')) {
            $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_get_device_firmware_url`(IN `device_id` INT, IN `platform` TINYINT, IN `fparam1` INT, IN `fparam2` INT, IN `fparam3` INT, IN `fparam4` INT, OUT `protocols` TINYINT, OUT `host` VARCHAR(100), OUT `port` INT, OUT `path` VARCHAR(100))
                NO SQL
            BEGIN
                SET @protocols = 0;
                SET @host = '';
                SET @port = 0;
                SET @path = '';
                
                SET @fparam1 = fparam1;
                SET @fparam2 = fparam2;
                SET @platform = platform;
                SET @device_id = device_id;
                
                INSERT INTO `esp_update_log`(`date`, `device_id`, `platform`, `fparam1`, `fparam2`, `fparam3`, `fparam4`) VALUES (NOW(),device_id,platform,fparam1,fparam2,fparam3,fparam4);
                
                SELECT u.`protocols`, u.`host`, u.`port`, u.`path` INTO @protocols, @host, @port, @path FROM supla_iodevice AS d, esp_update AS u WHERE d.id = @device_id AND u.`platform` = @platform AND u.`fparam1` = @fparam1 AND u.`fparam2` = @fparam2 AND u.`device_name` = d.name AND u.`latest_software_version` != d.`software_version` AND ( u.`device_id` = 0 OR u.`device_id` = device_id ) LIMIT 1;
                
                SET protocols = @protocols;
                SET host = @host;
                SET port = @port;
                SET path = @path;
            END;
PROCEDURE
            );
        }
    }

    private function procedureExists(string $name): bool {
        return !!$this->getConnection()->fetchOne('SELECT COUNT(*) FROM information_schema.routines WHERE routine_type="PROCEDURE" AND routine_schema=DATABASE() AND routine_name="' . $name . '"');
    }
}

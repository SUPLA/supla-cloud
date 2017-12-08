<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add possibly missing procedures and views to the database.
 */
class Version20171208222022 extends AbstractMigration {
    public function up(Schema $schema) {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql(<<<VIEW
        CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW IF NOT EXISTS `supla_v_client` AS  
        SELECT `c`.`id` AS `id`,`c`.`access_id` AS `access_id`,`c`.`guid` AS `guid`,`c`.`name` AS `name`,`c`.`reg_ipv4` AS `reg_ipv4`,
               `c`.`reg_date` AS `reg_date`,`c`.`last_access_ipv4` AS `last_access_ipv4`,`c`.`last_access_date` AS `last_access_date`,
               `c`.`software_version` AS `software_version`,`c`.`protocol_version` AS `protocol_version`,`c`.`enabled` AS `enabled`,
               `a`.`user_id` AS `user_id` 
        FROM (`supla_client` `c` JOIN `supla_accessid` `a` ON((`a`.`id` = `c`.`access_id`))) ;
VIEW
        );
        $this->addSql(<<<VIEW
        CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW IF NOT EXISTS `supla_v_client_channel` AS  
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
        CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW IF NOT EXISTS `supla_v_client_location` AS 
        SELECT `l`.`id` AS `id`,`l`.`caption` AS `caption`,`c`.`id` AS `client_id` 
        FROM ((`supla_rel_aidloc` `al` JOIN `supla_location` `l` ON((`l`.`id` = `al`.`location_id`))) 
        JOIN `supla_client` `c` ON((`c`.`access_id` = `al`.`access_id`))) 
        WHERE (`l`.`enabled` = 1);
VIEW
        );
        $this->addSql(<<<VIEW
        CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW IF NOT EXISTS `supla_v_device_accessid` AS
        SELECT `a`.`id` AS `id`,`a`.`user_id` AS `user_id`,cast(`a`.`enabled` as unsigned) AS `enabled`,`a`.`password` AS `password`,
               `u`.`limit_client` AS `limit_client` 
        FROM (`supla_accessid` `a` JOIN `supla_user` `u` ON((`u`.`id` = `a`.`user_id`)));
VIEW
        );
        $this->addSql(<<<VIEW
        CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_device_location` AS
        SELECT `l`.`id` AS `id`,`l`.`user_id` AS `user_id`,cast(`l`.`enabled` as unsigned) AS `enabled`,`u`.`limit_iodev` AS `limit_iodev`,
               `l`.`password` AS `password`
        FROM (`supla_location` `l` JOIN `supla_user` `u` ON((`u`.`id` = `l`.`user_id`)));
VIEW
        );
    }

    public function down(Schema $schema) {
    }
}

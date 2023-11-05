<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Create supla_thermostat_log table
 */
class Version20190219184847 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_thermostat_log_item`');
        $this->addSql('CREATE PROCEDURE `supla_add_thermostat_log_item`(IN `_channel_id` INT(11), IN `_measured_temperature` DECIMAL(5,2), IN `_preset_temperature` DECIMAL(5,2)) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN INSERT INTO `supla_thermostat_log`(`channel_id`, `date`, `measured_temperature`, `preset_temperature`) VALUES (_channel_id,UTC_TIMESTAMP(),_measured_temperature, _preset_temperature); END');
        $this->addSql('DROP TABLE IF EXISTS supla_thermostat_log');
        $this->addSql('CREATE TABLE supla_thermostat_log (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', `on` TINYINT(1) NOT NULL, measured_temperature NUMERIC(5, 2) NOT NULL, preset_temperature NUMERIC(5, 2) NOT NULL, INDEX channel_id_idx (channel_id), INDEX date_idx (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }
}

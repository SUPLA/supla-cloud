<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * The 'on' parameter has been added to the supla_add_thermostat_log_item procedure
 */
class Version20200210145902 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_thermostat_log_item`');
        $this->addSql('CREATE PROCEDURE `supla_add_thermostat_log_item`(IN `_channel_id` INT(11), IN `_measured_temperature` DECIMAL(5,2), IN `_preset_temperature` DECIMAL(5,2), IN `_on` TINYINT) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER BEGIN INSERT INTO `supla_thermostat_log`(`channel_id`, `date`, `measured_temperature`, `preset_temperature`, `on`) VALUES (_channel_id,UTC_TIMESTAMP(),_measured_temperature, _preset_temperature, _on); END');
    }
}

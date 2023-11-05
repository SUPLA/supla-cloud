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

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add supla_em_voltage_log.
 * Add procedure supla_add_em_log_item.
 */
class Version20221010103958 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_em_voltage_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', phase_no TINYINT NOT NULL COMMENT \'(DC2Type:tinyint)\', count_total INT NOT NULL, count_above INT NOT NULL, count_below INT NOT NULL, sec_total INT NOT NULL, sec_above INT NOT NULL, sec_below INT NOT NULL, max_sec_above INT NOT NULL, max_sec_below INT NOT NULL, min_voltage NUMERIC(7, 2) NOT NULL, max_voltage NUMERIC(7, 2) NOT NULL, avg_voltage NUMERIC(7, 2) NOT NULL, measurement_time_sec INT NOT NULL, PRIMARY KEY(channel_id, date, phase_no)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_add_em_voltage_log_item`');
        $this->addSql(<<<PROCEDURE
            CREATE PROCEDURE `supla_add_em_voltage_log_item`(
                IN `_date` DATETIME, 
                IN `_channel_id` INT(11), 
                IN `_phase_no` TINYINT,
                IN `_count_total` INT(11),
                IN `_count_above` INT(11),
                IN `_count_below` INT(11),
                IN `_sec_total` INT(11),
                IN `_sec_above` INT(11),
                IN `_sec_below` INT(11),
                IN `_max_sec_above` INT(11),
                IN `_max_sec_below` INT(11),
                IN `_min_voltage` NUMERIC(7,2),
                IN `_max_voltage` NUMERIC(7,2),
                IN `_avg_voltage` NUMERIC(7,2),
                IN `_measurement_time_sec` INT(11)
            ) NO SQL BEGIN

            INSERT INTO `supla_em_voltage_log` (`date`,channel_id, phase_no, count_total, count_above, count_below, sec_total, sec_above, sec_below, max_sec_above, max_sec_below, min_voltage, max_voltage, avg_voltage, measurement_time_sec)
                              VALUES (_date,_channel_id,_phase_no,_count_total,_count_above,_count_below,_sec_total,_sec_above,_sec_below,_max_sec_above,_max_sec_below,_min_voltage,_max_voltage,_avg_voltage,_measurement_time_sec);

            END
PROCEDURE
        );
    }
}

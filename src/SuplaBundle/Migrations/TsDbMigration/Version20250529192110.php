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

namespace SuplaBundle\Migrations\TsDbMigration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * TSDB initial structure.
 */
class Version20250529192110 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_em_current_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, phase_no SMALLINT NOT NULL, min NUMERIC(6, 3) NOT NULL, max NUMERIC(6, 3) NOT NULL, avg NUMERIC(6, 3) NOT NULL, PRIMARY KEY(channel_id, date, phase_no))');
        $this->addSql('COMMENT ON COLUMN supla_em_current_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_em_current_log.phase_no IS \'(DC2Type:tinyint)\'');
        $this->addSql('SELECT create_hypertable(\'supla_em_current_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_em_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, phase1_fae BIGINT DEFAULT NULL, phase1_rae BIGINT DEFAULT NULL, phase1_fre BIGINT DEFAULT NULL, phase1_rre BIGINT DEFAULT NULL, phase2_fae BIGINT DEFAULT NULL, phase2_rae BIGINT DEFAULT NULL, phase2_fre BIGINT DEFAULT NULL, phase2_rre BIGINT DEFAULT NULL, phase3_fae BIGINT DEFAULT NULL, phase3_rae BIGINT DEFAULT NULL, phase3_fre BIGINT DEFAULT NULL, phase3_rre BIGINT DEFAULT NULL, fae_balanced BIGINT DEFAULT NULL, rae_balanced BIGINT DEFAULT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_em_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('SELECT create_hypertable(\'supla_em_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_em_power_active_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, phase_no SMALLINT NOT NULL, min NUMERIC(11, 5) NOT NULL, max NUMERIC(11, 5) NOT NULL, avg NUMERIC(11, 5) NOT NULL, PRIMARY KEY(channel_id, date, phase_no))');
        $this->addSql('COMMENT ON COLUMN supla_em_power_active_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_em_power_active_log.phase_no IS \'(DC2Type:tinyint)\'');
        $this->addSql('SELECT create_hypertable(\'supla_em_power_active_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_em_voltage_aberration_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, phase_no SMALLINT NOT NULL, count_total INT NOT NULL, count_above INT NOT NULL, count_below INT NOT NULL, sec_above INT NOT NULL, sec_below INT NOT NULL, max_sec_above INT NOT NULL, max_sec_below INT NOT NULL, min_voltage NUMERIC(7, 2) NOT NULL, max_voltage NUMERIC(7, 2) NOT NULL, avg_voltage NUMERIC(7, 2) NOT NULL, measurement_time_sec INT NOT NULL, PRIMARY KEY(channel_id, date, phase_no))');
        $this->addSql('COMMENT ON COLUMN supla_em_voltage_aberration_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_em_voltage_aberration_log.phase_no IS \'(DC2Type:tinyint)\'');
        $this->addSql('SELECT create_hypertable(\'supla_em_voltage_aberration_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_em_voltage_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, phase_no SMALLINT NOT NULL, min NUMERIC(5, 2) NOT NULL, max NUMERIC(5, 2) NOT NULL, avg NUMERIC(5, 2) NOT NULL, PRIMARY KEY(channel_id, date, phase_no))');
        $this->addSql('COMMENT ON COLUMN supla_em_voltage_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_em_voltage_log.phase_no IS \'(DC2Type:tinyint)\'');
        $this->addSql('SELECT create_hypertable(\'supla_em_voltage_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_gp_measurement_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, open_value DOUBLE PRECISION NOT NULL, close_value DOUBLE PRECISION NOT NULL, avg_value DOUBLE PRECISION NOT NULL, max_value DOUBLE PRECISION NOT NULL, min_value DOUBLE PRECISION NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_gp_measurement_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('SELECT create_hypertable(\'supla_gp_measurement_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_gp_meter_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, value DOUBLE PRECISION NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_gp_meter_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('SELECT create_hypertable(\'supla_gp_meter_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_ic_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, counter BIGINT NOT NULL, calculated_value BIGINT NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_ic_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('SELECT create_hypertable(\'supla_ic_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_temperature_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, temperature NUMERIC(8, 4) NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_temperature_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('SELECT create_hypertable(\'supla_temperature_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_temphumidity_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, temperature NUMERIC(8, 4) NOT NULL, humidity NUMERIC(8, 4) NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_temphumidity_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('SELECT create_hypertable(\'supla_temphumidity_log\', \'date\')');
        $this->addSql('CREATE TABLE supla_thermostat_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "on" BOOLEAN NOT NULL, measured_temperature NUMERIC(5, 2) NOT NULL, preset_temperature NUMERIC(5, 2) NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_thermostat_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('SELECT create_hypertable(\'supla_thermostat_log\', \'date\')');
    }
}

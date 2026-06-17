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

use App\Migrations\NoWayBackMigration;

/**
 * supla_em_delta_log
 * supla_energy_tariff
 * supla_energy_tariff_assignment
 * supla_energy_tariff_resolved_zone
 * supla_energy_tariff_holidays
 */
class Version20260611125123 extends NoWayBackMigration {
    public function migrate(): void {
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_holidays_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_price_list_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_price_list_assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_price_list_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_resolved_zone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE supla_em_delta_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, phase1_fae VARCHAR(255) DEFAULT NULL, phase1_rae VARCHAR(255) DEFAULT NULL, phase2_fae VARCHAR(255) DEFAULT NULL, phase2_rae VARCHAR(255) DEFAULT NULL, phase3_fae VARCHAR(255) DEFAULT NULL, phase3_rae VARCHAR(255) DEFAULT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_em_delta_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff (id BIGINT NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, config_json JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff.created_at IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff.updated_at IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff_assignment (id BIGINT NOT NULL, tariff_id BIGINT NOT NULL, channel_id INT NOT NULL, valid_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, valid_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C7C949B292348FD2 ON supla_energy_tariff_assignment (tariff_id)');
        $this->addSql('CREATE INDEX idx_assignment_channel_time ON supla_energy_tariff_assignment (channel_id, valid_from, valid_to)');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_assignment.valid_from IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_assignment.valid_to IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff_holidays (id BIGINT NOT NULL, timezone VARCHAR(100) NOT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uq_timezone_date ON supla_energy_tariff_holidays (timezone, date)');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_holidays.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff_price_list (id BIGINT NOT NULL, tariff_id BIGINT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CE9607AA92348FD2 ON supla_energy_tariff_price_list (tariff_id)');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_price_list.created_at IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_price_list.updated_at IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff_price_list_assignment (id BIGINT NOT NULL, price_list_id BIGINT NOT NULL, channel_id INT NOT NULL, valid_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, valid_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_742323C65688DED7 ON supla_energy_tariff_price_list_assignment (price_list_id)');
        $this->addSql('CREATE INDEX idx_price_assignment_channel_time ON supla_energy_tariff_price_list_assignment (channel_id, valid_from, valid_to)');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_price_list_assignment.valid_from IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_price_list_assignment.valid_to IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff_price_list_item (id BIGINT NOT NULL, price_list_id BIGINT NOT NULL, component_code VARCHAR(100) NOT NULL, zone_code VARCHAR(100) DEFAULT NULL, amount NUMERIC(12, 6) NOT NULL, unit VARCHAR(20) NOT NULL, currency VARCHAR(10) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4999D4095688DED7 ON supla_energy_tariff_price_list_item (price_list_id)');
        $this->addSql('CREATE INDEX idx_price_list_component_zone ON supla_energy_tariff_price_list_item (price_list_id, component_code, zone_code)');
        $this->addSql('CREATE TABLE supla_energy_tariff_resolved_zone (id BIGINT NOT NULL, tariff_id BIGINT NOT NULL, zone_code VARCHAR(100) NOT NULL, period_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, period_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_tariff_period ON supla_energy_tariff_resolved_zone (tariff_id, period_start, period_end)');
        $this->addSql('CREATE UNIQUE INDEX uq_tariff_zone_start ON supla_energy_tariff_resolved_zone (tariff_id, zone_code, period_start)');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_resolved_zone.period_start IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_resolved_zone.period_end IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('ALTER TABLE supla_energy_tariff_assignment ADD CONSTRAINT FK_C7C949B292348FD2 FOREIGN KEY (tariff_id) REFERENCES supla_energy_tariff (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE supla_energy_tariff_price_list ADD CONSTRAINT FK_CE9607AA92348FD2 FOREIGN KEY (tariff_id) REFERENCES supla_energy_tariff (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE supla_energy_tariff_price_list_assignment ADD CONSTRAINT FK_742323C65688DED7 FOREIGN KEY (price_list_id) REFERENCES supla_energy_tariff_price_list (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE supla_energy_tariff_price_list_item ADD CONSTRAINT FK_4999D4095688DED7 FOREIGN KEY (price_list_id) REFERENCES supla_energy_tariff_price_list (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('SELECT create_hypertable(\'supla_em_delta_log\', \'date\')');
    }
}

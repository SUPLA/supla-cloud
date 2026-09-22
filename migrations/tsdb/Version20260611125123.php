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
 * Tariffs.
 */
class Version20260611125123 extends NoWayBackMigration {
    public function migrate(): void {
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_holidays_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_profile_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_profile_price_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_profile_price_period_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_profile_tariff_period_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supla_energy_tariff_resolved_zone_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE supla_em_delta_log (channel_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, phase1_fae INT DEFAULT NULL, phase1_rae INT DEFAULT NULL, phase2_fae INT DEFAULT NULL, phase2_rae INT DEFAULT NULL, phase3_fae INT DEFAULT NULL, phase3_rae INT DEFAULT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_em_delta_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff (id BIGINT NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, config_json JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff.created_at IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff.updated_at IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff_holidays (id BIGINT NOT NULL, timezone VARCHAR(100) NOT NULL, date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uq_timezone_date ON supla_energy_tariff_holidays (timezone, date)');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_holidays.date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile (id BIGINT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_energy_tariff_profile_user ON supla_energy_tariff_profile (user_id)');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_profile.created_at IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_profile.updated_at IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile_assignment (channel_id INT NOT NULL, profile_id BIGINT NOT NULL, PRIMARY KEY(channel_id))');
        $this->addSql('CREATE INDEX idx_tariff_profile_assignment_profile ON supla_energy_tariff_profile_assignment (profile_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_tariff_profile_assignment_channel ON supla_energy_tariff_profile_assignment (channel_id)');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile_price_item (id BIGINT NOT NULL, price_period_id BIGINT NOT NULL, component_code INT NOT NULL, zone_code VARCHAR(100) DEFAULT NULL, amount NUMERIC(12, 6) NOT NULL, unit VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9AC6D6BA6F3A4922 ON supla_energy_tariff_profile_price_item (price_period_id)');
        $this->addSql('CREATE INDEX idx_tariff_profile_price_item_component_zone ON supla_energy_tariff_profile_price_item (price_period_id, component_code, zone_code)');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile_price_period (id BIGINT NOT NULL, tariff_period_id BIGINT NOT NULL, billing_period_length INT NOT NULL, billing_period_unit VARCHAR(255) NOT NULL, currency VARCHAR(10) NOT NULL, valid_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, valid_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2B674158A3DDABB8 ON supla_energy_tariff_profile_price_period (tariff_period_id)');
        $this->addSql('CREATE INDEX idx_tariff_profile_price_period_time ON supla_energy_tariff_profile_price_period (tariff_period_id, valid_from, valid_to)');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_profile_price_period.valid_from IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_profile_price_period.valid_to IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile_tariff_period (id BIGINT NOT NULL, profile_id BIGINT NOT NULL, tariff_id BIGINT NOT NULL, valid_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, valid_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A7CCE22CCCFA12B8 ON supla_energy_tariff_profile_tariff_period (profile_id)');
        $this->addSql('CREATE INDEX idx_tariff_profile_period_profile_time ON supla_energy_tariff_profile_tariff_period (profile_id, valid_from, valid_to)');
        $this->addSql('CREATE INDEX idx_tariff_profile_period_tariff ON supla_energy_tariff_profile_tariff_period (tariff_id)');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_profile_tariff_period.valid_from IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('COMMENT ON COLUMN supla_energy_tariff_profile_tariff_period.valid_to IS \'(DC2Type:utcdatetime)\'');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_assignment ADD CONSTRAINT FK_C84A45A4CCFA12B8 FOREIGN KEY (profile_id) REFERENCES supla_energy_tariff_profile (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_price_item ADD CONSTRAINT FK_9AC6D6BA6F3A4922 FOREIGN KEY (price_period_id) REFERENCES supla_energy_tariff_profile_price_period (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_price_period ADD CONSTRAINT FK_2B674158A3DDABB8 FOREIGN KEY (tariff_period_id) REFERENCES supla_energy_tariff_profile_tariff_period (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_tariff_period ADD CONSTRAINT FK_A7CCE22CCCFA12B8 FOREIGN KEY (profile_id) REFERENCES supla_energy_tariff_profile (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_tariff_period ADD CONSTRAINT FK_A7CCE22C92348FD2 FOREIGN KEY (tariff_id) REFERENCES supla_energy_tariff (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('SELECT create_hypertable(\'supla_em_delta_log\', \'date\')');
    }
}

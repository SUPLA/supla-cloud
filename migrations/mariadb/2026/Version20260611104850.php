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

use App\Migrations\NoWayBackMigration;

/**
 * Tariffs.
 */
class Version20260611104850 extends NoWayBackMigration {
    public function migrate(): void {
        $this->addSql('CREATE TABLE supla_em_delta_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', phase1_fae VARCHAR(255) DEFAULT NULL, phase1_rae VARCHAR(255) DEFAULT NULL, phase2_fae VARCHAR(255) DEFAULT NULL, phase2_rae VARCHAR(255) DEFAULT NULL, phase3_fae VARCHAR(255) DEFAULT NULL, phase3_rae VARCHAR(255) DEFAULT NULL, PRIMARY KEY(channel_id, date)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff (id BIGINT AUTO_INCREMENT NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, config_json JSON NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_holidays (id BIGINT AUTO_INCREMENT NOT NULL, timezone VARCHAR(100) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', UNIQUE INDEX uq_timezone_date (timezone, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile (id BIGINT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX idx_energy_tariff_profile_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile_assignment (channel_id INT NOT NULL, profile_id BIGINT NOT NULL, INDEX idx_tariff_profile_assignment_profile (profile_id), UNIQUE INDEX uniq_tariff_profile_assignment_channel (channel_id), PRIMARY KEY(channel_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile_price_item (id BIGINT AUTO_INCREMENT NOT NULL, price_period_id BIGINT NOT NULL, component_code INT NOT NULL, zone_code VARCHAR(100) DEFAULT NULL, amount NUMERIC(12, 6) NOT NULL, unit VARCHAR(255) NOT NULL, INDEX IDX_9AC6D6BA6F3A4922 (price_period_id), INDEX idx_tariff_profile_price_item_component_zone (price_period_id, component_code, zone_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile_price_period (id BIGINT AUTO_INCREMENT NOT NULL, tariff_period_id BIGINT NOT NULL, name VARCHAR(255) NOT NULL, billing_period_length INT NOT NULL, billing_period_unit VARCHAR(255) NOT NULL, currency VARCHAR(10) NOT NULL, valid_from DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', valid_to DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX IDX_2B674158A3DDABB8 (tariff_period_id), INDEX idx_tariff_profile_price_period_time (tariff_period_id, valid_from, valid_to), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_profile_tariff_period (id BIGINT AUTO_INCREMENT NOT NULL, profile_id BIGINT NOT NULL, tariff_id BIGINT NOT NULL, valid_from DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', valid_to DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX IDX_A7CCE22CCCFA12B8 (profile_id), INDEX idx_tariff_profile_period_profile_time (profile_id, valid_from, valid_to), INDEX idx_tariff_profile_period_tariff (tariff_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_resolved_zone (id BIGINT AUTO_INCREMENT NOT NULL, tariff_id BIGINT NOT NULL, zone_code VARCHAR(100) NOT NULL, period_start DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', period_end DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX idx_tariff_period (tariff_id, period_start, period_end), UNIQUE INDEX uq_tariff_zone_start (tariff_id, zone_code, period_start), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_assignment ADD CONSTRAINT FK_C84A45A4CCFA12B8 FOREIGN KEY (profile_id) REFERENCES supla_energy_tariff_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_price_item ADD CONSTRAINT FK_9AC6D6BA6F3A4922 FOREIGN KEY (price_period_id) REFERENCES supla_energy_tariff_profile_price_period (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_price_period ADD CONSTRAINT FK_2B674158A3DDABB8 FOREIGN KEY (tariff_period_id) REFERENCES supla_energy_tariff_profile_tariff_period (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_tariff_period ADD CONSTRAINT FK_A7CCE22CCCFA12B8 FOREIGN KEY (profile_id) REFERENCES supla_energy_tariff_profile (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_energy_tariff_profile_tariff_period ADD CONSTRAINT FK_A7CCE22C92348FD2 FOREIGN KEY (tariff_id) REFERENCES supla_energy_tariff (id) ON DELETE CASCADE');
    }
}

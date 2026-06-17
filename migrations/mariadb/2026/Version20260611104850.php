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
 * supla_em_delta_log
 * supla_energy_tariff
 * supla_energy_tariff_assignment
 * supla_energy_tariff_resolved_zone
 * supla_energy_tariff_holidays
 * supla_energy_tariff_price_list
 */
class Version20260611104850 extends NoWayBackMigration {
    public function migrate(): void {
        $this->addSql('CREATE TABLE supla_em_delta_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', phase1_fae INT DEFAULT NULL, phase1_rae INT DEFAULT NULL, phase2_fae INT DEFAULT NULL, phase2_rae INT DEFAULT NULL, phase3_fae INT DEFAULT NULL, phase3_rae INT DEFAULT NULL, PRIMARY KEY(channel_id, date)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff (id BIGINT AUTO_INCREMENT NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, config_json JSON NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_assignment (id BIGINT AUTO_INCREMENT NOT NULL, tariff_id BIGINT NOT NULL, channel_id INT NOT NULL, valid_from DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', valid_to DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX IDX_C7C949B292348FD2 (tariff_id), INDEX idx_assignment_channel_time (channel_id, valid_from, valid_to), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_holidays (id BIGINT AUTO_INCREMENT NOT NULL, timezone VARCHAR(100) NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', UNIQUE INDEX uq_timezone_date (timezone, date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_price_list (id BIGINT AUTO_INCREMENT NOT NULL, tariff_id BIGINT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, billing_period_start_day INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX IDX_CE9607AA92348FD2 (tariff_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_price_list_assignment (id BIGINT AUTO_INCREMENT NOT NULL, price_list_id BIGINT NOT NULL, channel_id INT NOT NULL, valid_from DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', valid_to DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX IDX_742323C65688DED7 (price_list_id), INDEX idx_price_assignment_channel_time (channel_id, valid_from, valid_to), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_price_list_item (id BIGINT AUTO_INCREMENT NOT NULL, price_list_id BIGINT NOT NULL, component_code VARCHAR(100) NOT NULL, zone_code VARCHAR(100) DEFAULT NULL, amount NUMERIC(12, 6) NOT NULL, unit VARCHAR(20) NOT NULL, currency VARCHAR(10) NOT NULL, INDEX IDX_4999D4095688DED7 (price_list_id), INDEX idx_price_list_component_zone (price_list_id, component_code, zone_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_resolved_zone (id BIGINT AUTO_INCREMENT NOT NULL, tariff_id BIGINT NOT NULL, zone_code VARCHAR(100) NOT NULL, period_start DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', period_end DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX idx_tariff_period (tariff_id, period_start, period_end), UNIQUE INDEX uq_tariff_zone_start (tariff_id, zone_code, period_start), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_energy_tariff_assignment ADD CONSTRAINT FK_C7C949B292348FD2 FOREIGN KEY (tariff_id) REFERENCES supla_energy_tariff (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_energy_tariff_price_list ADD CONSTRAINT FK_CE9607AA92348FD2 FOREIGN KEY (tariff_id) REFERENCES supla_energy_tariff (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_energy_tariff_price_list_assignment ADD CONSTRAINT FK_742323C65688DED7 FOREIGN KEY (price_list_id) REFERENCES supla_energy_tariff_price_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_energy_tariff_price_list_item ADD CONSTRAINT FK_4999D4095688DED7 FOREIGN KEY (price_list_id) REFERENCES supla_energy_tariff_price_list (id) ON DELETE CASCADE');
    }
}

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
 */
class Version20260611104850 extends NoWayBackMigration {
    public function migrate(): void {
        $this->addSql('CREATE TABLE supla_em_delta_log (channel_id INT NOT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', phase1_fae INT DEFAULT NULL, phase1_rae INT DEFAULT NULL, phase2_fae INT DEFAULT NULL, phase2_rae INT DEFAULT NULL, phase3_fae INT DEFAULT NULL, phase3_rae INT DEFAULT NULL, PRIMARY KEY(channel_id, date)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff (id BIGINT AUTO_INCREMENT NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, config_json JSON NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_tariff_assignment (id BIGINT AUTO_INCREMENT NOT NULL, tariff_id BIGINT NOT NULL, channel_id INT NOT NULL, valid_from DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', valid_to DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX IDX_C7C949B292348FD2 (tariff_id), INDEX idx_assignment_channel_time (channel_id, valid_from, valid_to), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_energy_tariff_assignment ADD CONSTRAINT FK_C7C949B292348FD2 FOREIGN KEY (tariff_id) REFERENCES supla_energy_tariff (id) ON DELETE CASCADE');
    }
}

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
 * Energy cost plans.
 */
class Version20260922211957 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_energy_cost_plan (id BIGINT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, configuration_json JSON NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX supla_energy_cost_plan_user_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supla_energy_cost_plan_assignment (channel_id INT NOT NULL, energy_cost_plan_id BIGINT NOT NULL, INDEX supla_energy_cost_plan_assignment_plan_idx (energy_cost_plan_id), PRIMARY KEY(channel_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_energy_cost_plan ADD CONSTRAINT FK_D7603D5FA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_energy_cost_plan_assignment ADD CONSTRAINT FK_757E6A0972F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_energy_cost_plan_assignment ADD CONSTRAINT FK_757E6A09C8BB81BB FOREIGN KEY (energy_cost_plan_id) REFERENCES supla_energy_cost_plan (id) ON DELETE CASCADE');
    }
}

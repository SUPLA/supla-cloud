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
 * OneToOne relations for push notifications.
 * User technical password.
 */
class Version20251102210217 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_scene_operation DROP INDEX IDX_64A50CF54E328CBE, ADD UNIQUE INDEX UNIQ_64A50CF54E328CBE (push_notification_id)');
        $this->addSql('ALTER TABLE supla_value_based_trigger DROP INDEX IDX_1DFF99CA4E328CBE, ADD UNIQUE INDEX UNIQ_1DFF99CA4E328CBE (push_notification_id)');
        $this->addSql('ALTER TABLE supla_user ADD technical_password VARCHAR(255) DEFAULT NULL, ADD technical_password_valid_to DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\'');
    }
}

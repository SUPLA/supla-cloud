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
 * Fix collation of some user input fields.
 */
class Version20231221114509 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql("ALTER DATABASE CHARACTER SET utf8 COLLATE utf8_unicode_ci");

        $this->addSql("ALTER TABLE supla_auto_gate_closing DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->addSql("ALTER TABLE supla_dev_channel_extended_value DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->addSql("ALTER TABLE supla_dev_channel_value DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->addSql("ALTER TABLE supla_em_voltage_log DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->addSql("ALTER TABLE supla_push_notification DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->addSql("ALTER TABLE supla_rel_aid_pushnotification DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->addSql("ALTER TABLE supla_settings_string DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->addSql("ALTER TABLE supla_state_webhooks DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
        $this->addSql("ALTER TABLE supla_value_based_trigger DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");

        $this->addSql('ALTER TABLE supla_auto_gate_closing CHANGE active_hours active_hours VARCHAR(768) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_iodevice CHANGE user_config user_config VARCHAR(4096) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE properties properties VARCHAR(2048) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE software_version software_version VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE auth_key auth_key VARCHAR(64) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE supla_dev_channel CHANGE user_config user_config VARCHAR(4096) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE properties properties VARCHAR(2048) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE supla_push_notification CHANGE title title VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE body body VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE supla_scene CHANGE user_config user_config VARCHAR(2048) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE supla_settings_string CHANGE name name VARCHAR(50) NOT NULL, CHANGE value value VARCHAR(1024) NOT NULL');
        $this->addSql('ALTER TABLE supla_state_webhooks CHANGE url url VARCHAR(255) NOT NULL, CHANGE access_token access_token VARCHAR(255) NOT NULL, CHANGE refresh_token refresh_token VARCHAR(255) NOT NULL, CHANGE functions_ids functions_ids VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE supla_value_based_trigger CHANGE `trigger` `trigger` VARCHAR(2048) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE action_param action_param VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE active_hours active_hours VARCHAR(768) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE activity_conditions activity_conditions VARCHAR(1024) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}

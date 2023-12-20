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
 * Detected changes after migrating to PHP 7.4 (removes default collation).
 */
class Version20231221114509 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_accessid CHANGE caption caption VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_auto_gate_closing CHANGE active_hours active_hours VARCHAR(768) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_client CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE caption caption VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel CHANGE caption caption VARCHAR(100) DEFAULT NULL, CHANGE text_param1 text_param1 VARCHAR(255) DEFAULT NULL, CHANGE text_param2 text_param2 VARCHAR(255) DEFAULT NULL, CHANGE text_param3 text_param3 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_dev_channel_group CHANGE caption caption VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_direct_link CHANGE caption caption VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_iodevice CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE comment comment VARCHAR(200) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_location CHANGE caption caption VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_scene CHANGE caption caption VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_schedule CHANGE caption caption VARCHAR(255) DEFAULT NULL');
    }
}

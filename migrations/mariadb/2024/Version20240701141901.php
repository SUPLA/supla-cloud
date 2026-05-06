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
 * Add a pairing_result field.
 * Create table supla_subdevice.
 */
class Version20240701141901 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE `supla_iodevice` ADD `pairing_result` VARCHAR(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `channel_addition_blocked`');
        $this->addSql('DROP TABLE IF EXISTS `supla_subdevice`');
        $this->addSql('CREATE TABLE supla_subdevice (id INT NOT NULL, iodevice_id INT NOT NULL, reg_date DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', name VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, software_version VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, product_code VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, serial_number VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_698D8D2F125F95D6 (iodevice_id), PRIMARY KEY(id, iodevice_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_subdevice ADD CONSTRAINT FK_698D8D2F125F95D6 FOREIGN KEY (iodevice_id) REFERENCES supla_iodevice (id) ON DELETE CASCADE');
    }
}

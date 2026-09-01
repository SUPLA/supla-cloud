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
 * CALCFG queue snapshot table.
 */
class Version20260901155422 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_calcfg_queue (iodevice_id INT NOT NULL, user_id INT NOT NULL, queue TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX IDX_CALCFG_QUEUE_USER (user_id), PRIMARY KEY(iodevice_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_calcfg_queue ADD CONSTRAINT FK_CALCFG_QUEUE_DEVICE FOREIGN KEY (iodevice_id) REFERENCES supla_iodevice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_calcfg_queue ADD CONSTRAINT FK_CALCFG_QUEUE_USER FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
    }
}

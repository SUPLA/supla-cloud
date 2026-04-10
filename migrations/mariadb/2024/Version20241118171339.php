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
 * channel_state table.
 */
class Version20241118171339 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_dev_channel_state (channel_id INT NOT NULL, user_id INT NOT NULL, update_time DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', state TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_A57D6ADBA76ED395 (user_id), PRIMARY KEY(channel_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_dev_channel_state ADD CONSTRAINT FK_A57D6ADB72F5A1AA FOREIGN KEY (channel_id) REFERENCES supla_dev_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_dev_channel_state ADD CONSTRAINT FK_A57D6ADBA76ED395 FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
    }
}

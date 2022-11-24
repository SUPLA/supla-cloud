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

namespace Supla\Migrations;

/**
 * Add supla_oauth_access_tokens.issued_with_refresh_token_id
 */
class Version20221124222346 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD issued_with_refresh_token_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_oauth_access_tokens ADD CONSTRAINT FK_2402564BD2B4D7C8 FOREIGN KEY (issued_with_refresh_token_id) REFERENCES supla_oauth_refresh_tokens (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2402564BD2B4D7C8 ON supla_oauth_access_tokens (issued_with_refresh_token_id)');
    }
}

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
 * OAuth PKCE codes.
 */
class Version20261020202651 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_oauth_auth_codes ADD codeChallenge VARCHAR(128) DEFAULT NULL, ADD codeChallengeMethod VARCHAR(20) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX supla_oauth_clients_public_client_id_uniq ON supla_oauth_clients (public_client_id)');
    }
}

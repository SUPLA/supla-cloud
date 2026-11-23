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
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Schema\Schema;

/**
 * OAuth PKCE codes.
 */
class Version20261020202651 extends NoWayBackMigration {
    public function migrate() {
        // take care of possible duplicated public clients
        $this->addSql('DROP TEMPORARY TABLE IF EXISTS migration_oauth_duplicate_clients');
        $this->addSql('CREATE TEMPORARY TABLE migration_oauth_duplicate_clients AS
            SELECT id, name, public_client_id FROM (
                SELECT id, name, public_client_id, ROW_NUMBER() OVER (
                    PARTITION BY public_client_id
                    ORDER BY has_tokens DESC, IF(has_tokens = 0, has_authorization, 0) DESC, id DESC
                ) AS duplicate_rank
                FROM (
                    SELECT client.id, client.name, client.public_client_id,
                        (EXISTS(SELECT 1 FROM supla_oauth_access_tokens token WHERE token.client_id = client.id)
                            OR EXISTS(SELECT 1 FROM supla_oauth_refresh_tokens token WHERE token.client_id = client.id)) AS has_tokens,
                        EXISTS(SELECT 1 FROM supla_oauth_client_authorizations authorization WHERE authorization.client_id = client.id) AS has_authorization
                    FROM supla_oauth_clients client
                    WHERE client.public_client_id IS NOT NULL
                ) candidates
            ) ranked WHERE duplicate_rank > 1');
        $this->addSql('UPDATE supla_oauth_clients client
            INNER JOIN migration_oauth_duplicate_clients duplicate ON duplicate.id = client.id
            SET client.public_client_id = NULL');

        $columns = $this->sm->listTableColumns('supla_oauth_auth_codes');
        if (!isset($columns['codechallenge'])) {
            $this->addSql('ALTER TABLE supla_oauth_auth_codes ADD codeChallenge VARCHAR(128) DEFAULT NULL');
        }
        if (!isset($columns['codechallengemethod'])) {
            $this->addSql('ALTER TABLE supla_oauth_auth_codes ADD codeChallengeMethod VARCHAR(20) DEFAULT NULL');
        }

        $indexes = $this->sm->listTableIndexes('supla_oauth_clients');
        $indexName = 'supla_oauth_clients_public_client_id_uniq';
        if (isset($indexes[$indexName])) {
            $index = $indexes[$indexName];
            $this->abortIf(
                !$index->isUnique() || $index->getColumns() !== ['public_client_id'],
                'The existing public client ID index has an unexpected definition.'
            );
        } else {
            $this->addSql('CREATE UNIQUE INDEX supla_oauth_clients_public_client_id_uniq ON supla_oauth_clients (public_client_id)');
        }
    }

    public function postUp(Schema $schema): void {
        try {
            $clients = $this->fetchAll('SELECT id, name, public_client_id FROM migration_oauth_duplicate_clients ORDER BY id');
        } catch (TableNotFoundException $e) {
            return;
        }
        foreach ($clients as $client) {
            $this->log('Cleared duplicate OAuth client public ID', [
                'client_id' => $client['id'],
                'name' => $client['name'],
                'public_client_id' => $client['public_client_id'],
            ]);
        }
        $this->getConnection()->executeStatement('DROP TEMPORARY TABLE migration_oauth_duplicate_clients');
    }
}

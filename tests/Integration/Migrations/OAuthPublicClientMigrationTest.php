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
 */

namespace App\Tests\Integration\Migrations;

use Monolog\Handler\TestHandler;
use PHPUnit\Framework\Attributes\DataProvider;

class OAuthPublicClientMigrationTest extends DatabaseMigrationTestCase {
    #[DataProvider('existingColumns')]
    public function testDeduplicatesAndCanResumeAfterPartialDdl(array $existingColumns, bool $dryRun = false): void {
        $this->loadDump('23.12');
        $this->executeCommand([
            'command' => 'doctrine:migrations:migrate',
            'version' => 'SuplaBundle\Migrations\Migration\Version20260901155422',
        ]);
        $connection = $this->getEntityManager()->getConnection();
        foreach ($existingColumns as $column => $length) {
            $connection->executeStatement("ALTER TABLE supla_oauth_auth_codes ADD $column VARCHAR($length) DEFAULT NULL");
        }
        $publicIds = [
            'access', 'access', 'refresh', 'refresh', 'consent', 'consent', 'neither', 'neither',
            'both', 'both', 'triple', 'triple', 'triple', null, null, 'Case', 'case', 'unique',
        ];
        foreach ($publicIds as $offset => $publicId) {
            $id = 1001 + $offset;
            $connection->insert('supla_oauth_clients', [
                'id' => $id,
                'random_id' => "client-$id",
                'redirect_uris' => 'a:0:{}',
                'secret' => "secret-$id",
                'allowed_grant_types' => 'a:0:{}',
                'type' => 4,
                'name' => "Application $id",
                'public_client_id' => $publicId,
            ]);
        }
        foreach ([1001, 1009, 1010, 1011] as $id) {
            $connection->insert('supla_oauth_access_tokens', [
                'client_id' => $id, 'user_id' => 1, 'token' => "access-$id", 'scope' => '',
            ]);
        }
        $connection->insert('supla_oauth_refresh_tokens', [
            'client_id' => 1003, 'user_id' => 1, 'token' => 'refresh-1003', 'scope' => '',
        ]);
        foreach ([1002, 1005, 1009, 1012] as $id) {
            $connection->insert('supla_oauth_client_authorizations', [
                'client_id' => $id, 'user_id' => 1, 'scope' => '', 'authorization_date' => '2026-01-01 00:00:00',
            ]);
        }

        $logger = self::getContainer()->get('monolog.logger.supla_migrations');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $command = [
            'command' => 'doctrine:migrations:execute',
            '--up' => true,
            'versions' => ['SuplaBundle\Migrations\Migration\Version20261020202651'],
        ];
        $this->executeCommand($dryRun ? $command + ['--dry-run' => true] : $command);
        if ($dryRun) {
            $this->assertFalse($handler->hasDebug('Cleared duplicate OAuth client public ID'));
            $this->assertEquals('access', $connection->fetchOne('SELECT public_client_id FROM supla_oauth_clients WHERE id = 1002'));
            $logger->popHandler();
            return;
        }
        $records = array_values(array_filter(
            $handler->getRecords(),
            fn ($record) => $record['message'] === 'Cleared duplicate OAuth client public ID'
        ));
        $this->assertCount(8, $records);
        $this->assertEquals(
            [1002, 1004, 1006, 1007, 1009, 1012, 1013, 1016],
            array_column(array_column($records, 'context'), 'client_id')
        );
        $this->assertEquals([
            'migration' => 'Version20261020202651',
            'client_id' => 1002,
            'name' => 'Application 1002',
            'public_client_id' => 'access',
        ], $records[0]['context']);
        $logger->popHandler();

        $retainedIds = $connection->fetchFirstColumn(
            'SELECT id FROM supla_oauth_clients WHERE id BETWEEN 1001 AND 1018 AND public_client_id IS NOT NULL ORDER BY id'
        );
        $this->assertEquals([1001, 1003, 1005, 1008, 1010, 1011, 1017, 1018], $retainedIds);
        $this->assertEquals(18, $connection->fetchOne('SELECT COUNT(*) FROM supla_oauth_clients WHERE id BETWEEN 1001 AND 1018'));
        $this->assertEquals(4, $connection->fetchOne('SELECT COUNT(*) FROM supla_oauth_access_tokens WHERE client_id BETWEEN 1001 AND 1018'));
        $this->assertEquals(1, $connection->fetchOne('SELECT COUNT(*) FROM supla_oauth_refresh_tokens WHERE client_id BETWEEN 1001 AND 1018'));
        $this->assertEquals(4, $connection->fetchOne('SELECT COUNT(*) FROM supla_oauth_client_authorizations WHERE client_id BETWEEN 1001 AND 1018'));
        $columns = $connection->createSchemaManager()->listTableColumns('supla_oauth_auth_codes');
        $this->assertArrayHasKey('codechallenge', $columns);
        $this->assertArrayHasKey('codechallengemethod', $columns);
        $indexes = $connection->createSchemaManager()->listTableIndexes('supla_oauth_clients');
        $this->assertTrue($indexes['supla_oauth_clients_public_client_id_uniq']->isUnique());
    }

    public static function existingColumns(): array {
        return [
            'fresh migration' => [[]],
            'original migration partially applied' => [['codeChallenge' => 128, 'codeChallengeMethod' => 20]],
            'one column already added' => [['codeChallenge' => 128]],
            'dry run' => [[], true],
        ];
    }
}

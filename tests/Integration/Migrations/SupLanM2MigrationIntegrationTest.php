<?php

namespace App\Tests\Integration\Migrations;

use App\Entity\Main\User;
use App\Model\UserManager;
use App\Tests\Integration\Traits\UserFixtures;
use Doctrine\DBAL\Exception\DriverException;

class SupLanM2MigrationIntegrationTest extends DatabaseMigrationTestCase {
    use UserFixtures;

    private const TABLES = ['supla_suplan_device_state', 'supla_suplan_grant', 'supla_suplan_peer_association'];

    public function testUpgradePreservesExistingSchemaAndCreatesExactContract() {
        $this->migrateTo('SuplaBundle\\Migrations\\Migration\\Version20260922203542');
        $connection = $this->getEntityManager()->getConnection();
        $before = $connection->fetchFirstColumn('SHOW TABLES');
        $definitions = [];
        foreach ($before as $table) {
            $definitions[$table] = $connection->fetchAssociative('SHOW CREATE TABLE `' . $table . '`')['Create Table'];
        }
        $this->migrateTo('SuplaBundle\\Migrations\\Migration\\Version20261007104248');
        $added = array_values(array_diff($connection->fetchFirstColumn('SHOW TABLES'), $before));
        sort($added);
        $this->assertSame(self::TABLES, $added);
        foreach ($definitions as $table => $definition) {
            $this->assertSame($definition, $connection->fetchAssociative('SHOW CREATE TABLE `' . $table . '`')['Create Table'], $table);
        }
        foreach (self::TABLES as $table) {
            $this->assertSame('0', (string)$connection->fetchOne('SELECT COUNT(*) FROM ' . $table));
            $this->assertSame('InnoDB', $connection->fetchOne(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',
                [$table]
            ));
        }
        $this->assertColumns('supla_suplan_device_state', [
            'device_id' => ['int', null, ''],
            'user_id' => ['int', null, ''],
            'current_root_epoch' => ['int unsigned', null, ''],
            'accepted_channels' => ['varbinary(512)', null, ''],
        ]);
        $this->assertColumns('supla_suplan_peer_association', [
            'id' => ['bigint unsigned', null, 'auto_increment'],
            'user_id' => ['int', null, ''],
            'source_device_id' => ['int', null, ''],
            'destination_device_id' => ['int', null, ''],
            'lifecycle' => ['tinyint unsigned', '3', ''],
            'peer_generation' => ['int unsigned', '1', ''],
            'acl_revision' => ['int unsigned', '1', ''],
            'provisioned_root_epoch' => ['int unsigned', '0', ''],
            'provisioned_peer_generation' => ['int unsigned', '0', ''],
            'canonical_acl' => ['varbinary(534)', null, ''],
            'source_empty_acked' => ['tinyint unsigned', '0', ''],
            'destination_empty_acked' => ['tinyint unsigned', '0', ''],
            'source_removed' => ['tinyint unsigned', '0', ''],
            'destination_removed' => ['tinyint unsigned', '0', ''],
        ]);
        $this->assertColumns('supla_suplan_grant', [
            'association_id' => ['bigint unsigned', null, ''],
            'resource_type' => ['tinyint unsigned', null, ''],
            'resource_id' => ['int unsigned', null, ''],
            'origin_type' => ['smallint unsigned', null, ''],
            'origin_id' => ['bigint unsigned', null, ''],
            'permissions' => ['tinyint unsigned', null, ''],
        ]);
        $this->assertIndexes('supla_suplan_device_state', [
            'PRIMARY' => [0, 'device_id'],
            'suplan_device_user' => [1, 'user_id'],
        ]);
        $this->assertIndexes('supla_suplan_peer_association', [
            'PRIMARY' => [0, 'id'],
            'suplan_destination_replay' => [1, 'user_id,destination_device_id,lifecycle'],
            'suplan_ordered_pair' => [0, 'source_device_id,destination_device_id'],
            'suplan_source_replay' => [1, 'user_id,source_device_id,lifecycle'],
        ]);
        $this->assertIndexes('supla_suplan_grant', [
            'PRIMARY' => [0, 'association_id,resource_type,resource_id,origin_type,origin_id'],
            'suplan_grant_origin' => [1, 'origin_type,origin_id,association_id'],
            'suplan_grant_resource' => [1, 'resource_type,resource_id,association_id'],
        ]);
        $foreignKeys = $connection->fetchAllAssociative(
            "SELECT k.TABLE_NAME,k.CONSTRAINT_NAME,k.COLUMN_NAME,k.REFERENCED_TABLE_NAME,k.REFERENCED_COLUMN_NAME,r.DELETE_RULE,r.UPDATE_RULE
             FROM information_schema.KEY_COLUMN_USAGE k JOIN information_schema.REFERENTIAL_CONSTRAINTS r
             ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.TABLE_NAME=k.TABLE_NAME AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME
             WHERE k.TABLE_SCHEMA=DATABASE() AND k.TABLE_NAME LIKE 'supla_suplan_%' ORDER BY k.CONSTRAINT_NAME"
        );
        $this->assertSame([
            ['supla_suplan_grant', 'suplan_grant_parent', 'association_id', 'supla_suplan_peer_association', 'id', 'CASCADE', 'RESTRICT'],
            ['supla_suplan_peer_association', 'suplan_peer_user', 'user_id', 'supla_user', 'id', 'CASCADE', 'RESTRICT'],
            ['supla_suplan_device_state', 'suplan_state_device', 'device_id', 'supla_iodevice', 'id', 'CASCADE', 'RESTRICT'],
            ['supla_suplan_device_state', 'suplan_state_user', 'user_id', 'supla_user', 'id', 'CASCADE', 'RESTRICT'],
        ], array_map('array_values', $foreignKeys));
        $checks = $connection->fetchAllKeyValue(
            "SELECT CONSTRAINT_NAME,CHECK_CLAUSE FROM information_schema.CHECK_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME LIKE 'supla_suplan_%' ORDER BY CONSTRAINT_NAME"
        );
        $expectedChecks = [
            'suplan_boolean_state' => 'source_empty_acked IN (0,1) AND destination_empty_acked IN (0,1)'
                . ' AND source_removed IN (0,1) AND destination_removed IN (0,1)',
            'suplan_counters' => 'peer_generation>0 AND acl_revision>0',
            'suplan_grant_identity' => 'resource_id>0 AND origin_type>0 AND origin_id>0',
            'suplan_grant_permissions' => 'permissions BETWEEN 1 AND 7',
            'suplan_grant_resource_type' => 'resource_type IN (1,2)',
            'suplan_lifecycle' => 'lifecycle IN (1,2,3)',
            'suplan_peer_distinct' => 'source_device_id<>destination_device_id',
            'suplan_provisioned_root' => 'provisioned_root_epoch<>4294967295',
            'suplan_valid_root' => 'current_root_epoch BETWEEN 1 AND 4294967294',
        ];
        $normalize = fn($clause) => preg_replace('/[\s`()]/', '', strtolower($clause));
        $this->assertSame(array_map($normalize, $expectedChecks), array_map($normalize, $checks));
        $managedTables = $connection->createSchemaManager()->listTableNames();
        foreach (self::TABLES as $table) {
            $this->assertNotContains($table, $managedTables);
        }
        foreach (['supla_user', 'supla_iodevice', 'supla_dev_channel'] as $table) {
            $this->assertContains($table, $managedTables);
        }
    }

    public function testChecksForeignKeysAndUniqueIdentityRejectInvalidState() {
        [$userId, $sourceId, $destinationId] = $this->createEndpoints();
        $connection = $this->getEntityManager()->getConnection();
        $state = ['device_id' => $sourceId, 'user_id' => $userId, 'current_root_epoch' => 1, 'accepted_channels' => ''];
        $connection->insert('supla_suplan_device_state', $state);
        $connection->update(
            'supla_suplan_device_state',
            ['current_root_epoch' => 4294967294, 'accepted_channels' => str_repeat('a', 512)],
            ['device_id' => $sourceId]
        );
        foreach ([0, 4294967295] as $epoch) {
            $this->assertRejected('UPDATE supla_suplan_device_state SET current_root_epoch=? WHERE device_id=?', [$epoch, $sourceId], 4025);
        }
        $this->assertRejected('UPDATE supla_suplan_device_state SET accepted_channels=? WHERE device_id=?', [str_repeat('a', 513), $sourceId], 1406);
        $this->assertRejected('UPDATE supla_suplan_device_state SET accepted_channels=NULL WHERE device_id=?', [$sourceId], 1048);
        $this->assertRejected('UPDATE supla_suplan_device_state SET user_id=2147483647 WHERE device_id=?', [$sourceId], 1452);
        $this->assertRejected('UPDATE supla_suplan_device_state SET device_id=2147483647 WHERE device_id=?', [$sourceId], 1452);
        $associationId = $this->insertAssociation($userId, $sourceId, $destinationId);
        $defaults = $connection->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$associationId]);
        foreach (
            ['lifecycle' => 3, 'peer_generation' => 1, 'acl_revision' => 1, 'provisioned_root_epoch' => 0,
                  'provisioned_peer_generation' => 0, 'source_empty_acked' => 0, 'destination_empty_acked' => 0,
                  'source_removed' => 0, 'destination_removed' => 0] as $column => $default
        ) {
            $this->assertSame($default, (int)$defaults[$column]);
        }
        $this->assertSame('', $defaults['canonical_acl']);
        foreach (
            ['source_device_id' => $destinationId, 'lifecycle' => 0, 'peer_generation' => 0, 'acl_revision' => 0,
                  'provisioned_root_epoch' => 4294967295, 'source_empty_acked' => 2, 'destination_empty_acked' => 2,
                  'source_removed' => 2, 'destination_removed' => 2] as $column => $invalid
        ) {
            $this->assertRejected('UPDATE supla_suplan_peer_association SET ' . $column . '=? WHERE id=?', [$invalid, $associationId], 4025);
        }
        $this->assertRejected('UPDATE supla_suplan_peer_association SET lifecycle=4 WHERE id=?', [$associationId], 4025);
        $this->assertRejected('UPDATE supla_suplan_peer_association SET user_id=2147483647 WHERE id=?', [$associationId], 1452);
        $this->assertRejected('UPDATE supla_suplan_peer_association SET canonical_acl=NULL WHERE id=?', [$associationId], 1048);
        $this->assertRejected('UPDATE supla_suplan_peer_association SET canonical_acl=? WHERE id=?', [str_repeat('a', 535), $associationId], 1406);
        foreach ([1, 2, 3] as $lifecycle) {
            $connection->update(
                'supla_suplan_peer_association',
                ['lifecycle' => $lifecycle, 'canonical_acl' => str_repeat('a', 534)],
                ['id' => $associationId]
            );
        }
        $this->assertRejected(
            "INSERT INTO supla_suplan_peer_association (user_id,source_device_id,destination_device_id,canonical_acl) VALUES (?,?,?,X'')",
            [$userId, $sourceId, $destinationId],
            1062
        );
        $this->insertAssociation($userId, $destinationId, $sourceId); // The reverse direction is a distinct pair.
        $grant = ['association_id' => $associationId, 'resource_type' => 1, 'resource_id' => 1,
                  'origin_type' => 1, 'origin_id' => 4294967296, 'permissions' => 1];
        $connection->insert('supla_suplan_grant', $grant);
        $connection->update('supla_suplan_grant', ['permissions' => 7], ['association_id' => $associationId]);
        $connection->insert('supla_suplan_grant', array_replace($grant, ['resource_type' => 2]));
        foreach (['resource_type' => 0, 'resource_id' => 0, 'origin_type' => 0, 'origin_id' => 0, 'permissions' => 0] as $column => $invalid) {
            $this->assertRejected('UPDATE supla_suplan_grant SET ' . $column . '=? WHERE association_id=?', [$invalid, $associationId], 4025);
        }
        $this->assertRejected('UPDATE supla_suplan_grant SET resource_type=3 WHERE association_id=?', [$associationId], 4025);
        $this->assertRejected('UPDATE supla_suplan_grant SET permissions=8 WHERE association_id=?', [$associationId], 4025);
        $this->assertRejected('UPDATE supla_suplan_grant SET association_id=18446744073709551615 WHERE association_id=?', [$associationId], 1452);
        $this->assertRejected(
            'INSERT INTO supla_suplan_grant (association_id,resource_type,resource_id,origin_type,origin_id,permissions) VALUES (?,?,?,?,?,?)',
            array_values($grant),
            1062
        );
        $connection->insert('supla_suplan_grant', array_replace($grant, ['origin_id' => 4294967297]));
    }

    public function testDeviceDeletionRetainsSurvivingCleanupAndOwnershipCascades() {
        [$userId, $sourceId, $destinationId] = $this->createEndpoints();
        $connection = $this->getEntityManager()->getConnection();
        foreach ([$sourceId, $destinationId] as $deviceId) {
            $connection->insert(
                'supla_suplan_device_state',
                ['device_id' => $deviceId, 'user_id' => $userId, 'current_root_epoch' => 1, 'accepted_channels' => '']
            );
        }
        $associationId = $this->insertAssociation($userId, $sourceId, $destinationId);
        $connection->insert('supla_suplan_grant', ['association_id' => $associationId, 'resource_type' => 1, 'resource_id' => 1,
            'origin_type' => 1, 'origin_id' => 1, 'permissions' => 3]);
        $connection->executeStatement('DELETE FROM supla_iodevice WHERE id=?', [$sourceId]);
        $this->assertSame([$destinationId], array_map('intval', $connection->fetchFirstColumn('SELECT device_id FROM supla_suplan_device_state')));
        $this->assertSame((string)$sourceId, (string)$connection->fetchOne('SELECT source_device_id FROM supla_suplan_peer_association'));
        $this->assertSame('1', (string)$connection->fetchOne('SELECT COUNT(*) FROM supla_suplan_grant'));
        $connection->executeStatement('DELETE FROM supla_suplan_peer_association WHERE id=?', [$associationId]);
        $this->assertSame('0', (string)$connection->fetchOne('SELECT COUNT(*) FROM supla_suplan_grant'));
        // Deleted endpoint scalar identity can still be kept for technical lifecycle cleanup.
        $associationId = $this->insertAssociation($userId, $sourceId, $destinationId);
        $connection->insert('supla_suplan_grant', ['association_id' => $associationId, 'resource_type' => 2, 'resource_id' => $sourceId,
            'origin_type' => 1, 'origin_id' => 1, 'permissions' => 1]);
        $this->getEntityManager()->clear();
        $user = $this->getEntityManager()->find(User::class, $userId);
        self::$container->get(UserManager::class)->deleteAccount($user);
        foreach (self::TABLES as $table) {
            $this->assertSame('0', (string)$connection->fetchOne('SELECT COUNT(*) FROM ' . $table));
        }
    }

    private function migrateTo(string $version): void {
        $result = $this->executeCommand(['command' => 'doctrine:migrations:migrate', 'version' => $version]);
        $this->assertStringContainsString('Successfully migrated to version:', $result);
    }

    private function assertColumns(string $table, array $expected): void {
        $columns = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,EXTRA FROM information_schema.COLUMNS'
                . ' WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? ORDER BY ORDINAL_POSITION',
            [$table]
        );
        $actual = [];
        foreach ($columns as $column) {
            $this->assertSame('NO', $column['IS_NULLABLE']);
            $type = preg_replace('/(tinyint|smallint|int|bigint)\(\d+\)/', '$1', $column['COLUMN_TYPE']);
            $actual[$column['COLUMN_NAME']] = [$type, $column['COLUMN_DEFAULT'], $column['EXTRA']];
        }
        $this->assertSame($expected, $actual);
    }

    private function assertIndexes(string $table, array $expected): void {
        $indexes = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT INDEX_NAME,NON_UNIQUE,GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_in_order'
                . ' FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?'
                . ' GROUP BY INDEX_NAME,NON_UNIQUE ORDER BY INDEX_NAME',
            [$table]
        );
        $actual = [];
        foreach ($indexes as $index) {
            $actual[$index['INDEX_NAME']] = [(int)$index['NON_UNIQUE'], $index['columns_in_order']];
        }
        $this->assertSame($expected, $actual);
    }

    private function createEndpoints(): array {
        $this->initialize();
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        return [$user->getId(), $this->createDeviceSonoff($location)->getId(), $this->createDeviceSonoff($location)->getId()];
    }

    private function insertAssociation(int $userId, int $sourceId, int $destinationId): int {
        $connection = $this->getEntityManager()->getConnection();
        $connection->insert('supla_suplan_peer_association', ['user_id' => $userId, 'source_device_id' => $sourceId,
            'destination_device_id' => $destinationId, 'canonical_acl' => '']);
        return (int)$connection->lastInsertId();
    }

    private function assertRejected(string $sql, array $params, int $errorCode): void {
        try {
            $this->getEntityManager()->getConnection()->executeStatement($sql, $params);
            $this->fail('Database accepted invalid SupLAN state: ' . $sql);
        } catch (DriverException $exception) {
            $this->assertSame($errorCode, $exception->getPrevious()->getCode(), $sql);
        }
    }
}

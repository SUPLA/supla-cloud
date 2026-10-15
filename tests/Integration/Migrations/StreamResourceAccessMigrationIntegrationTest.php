<?php
namespace App\Tests\Integration\Migrations;

use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType;
use App\Tests\Integration\Traits\UserFixtures;
use Doctrine\DBAL\Exception\DriverException;

class StreamResourceAccessMigrationIntegrationTest extends DatabaseMigrationTestCase {
    use UserFixtures;

    public function testUpgradePreservesDecisionsPairsConfigAndProjectionAndRerun() {
        $this->executeCommand(['command' => 'doctrine:migrations:migrate',
            'version' => 'SuplaBundle\\Migrations\\Migration\\Version20261011120000']);
        $this->executeCommand('supla:initialize:create-sql-procedures-and-views');
        $this->executeCommand('supla:initialize:create-webapp-client');
        $user = $this->createConfirmedUser();
        $source = $this->createDevice($this->createLocation($user), [[ChannelType::THERMOMETER, CF::THERMOMETER]]);
        $destination = $this->createDevice($this->createLocation($user), [[ChannelType::HVAC, CF::HVAC_THERMOSTAT]]);
        $channel = $source->getChannels()[0]->getId();
        $config = ['mainThermometerChannelId' => $channel];
        $destination->getChannels()[0]->setUserConfig($config);
        $this->flush();
        $connection = $this->getEntityManager()->getConnection();
        $connection->insert('supla_resource_access_pair', ['user_id' => $user->getId(),
            'destination_device_id' => $destination->getId(), 'source_device_id' => $source->getId(), 'source_channel_id' => $channel]);
        $pair = $connection->lastInsertId();
        foreach (['ACCEPTED', 'REMOVED', 'REJECTED'] as $status) {
            $connection->insert('supla_resource_access_decision', ['pair_id' => $pair, 'status' => $status,
                'permissions' => 1, 'open_pair_id' => $status === 'REJECTED' ? $pair : null]);
        }
        $rejected = $connection->lastInsertId();
        $connection->insert('supla_suplan_peer_association', ['user_id' => $user->getId(),
            'source_device_id' => $source->getId(), 'destination_device_id' => $destination->getId(), 'canonical_acl' => pack('CNC', 1, $channel, 1)]);
        $association = $connection->lastInsertId();
        $connection->insert('supla_suplan_grant', ['association_id' => $association, 'resource_type' => 1,
            'resource_id' => $channel, 'origin_type' => 1, 'origin_id' => 123, 'permissions' => 1]);
        $before = $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id');
        $version = 'SuplaBundle\\Migrations\\Migration\\Version20261013215600';
        $output = $this->executeCommand(['command' => 'doctrine:migrations:migrate', 'version' => $version]);
        $this->assertStringContainsString('Successfully migrated to version:', $output);
        $after = $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id');
        foreach ($after as &$row) {
            $this->assertSame(1, (int)$row['requested_delivery_mode']);
            unset($row['requested_delivery_mode']);
        }
        unset($row);
        $this->assertSame($before, $after);
        $this->assertSame($config, json_decode($connection->fetchOne(
            'SELECT user_config FROM supla_dev_channel WHERE id=?',
            [$destination->getChannels()[0]->getId()]
        ), true));
        $this->assertSame(1, (int)$connection->fetchOne('SELECT COUNT(*) FROM supla_suplan_grant'));
        $this->assertSame(pack('CNC', 1, $channel, 1), $connection->fetchOne('SELECT canonical_acl FROM supla_suplan_peer_association'));
        // Transport metadata does not reopen a sticky rejection or create a second consent.
        $connection->update('supla_resource_access_decision', ['requested_delivery_mode' => 2], ['id' => $rejected]);
        $this->assertSame('REJECTED', $connection->fetchOne('SELECT status FROM supla_resource_access_decision WHERE id=?', [$rejected]));
        $this->assertSame(2, (int)$connection->fetchOne('SELECT revision FROM supla_resource_access_decision WHERE id=?', [$rejected]));
        $this->invalid(fn() => $connection->insert('supla_resource_access_pair', ['user_id' => $user->getId(),
            'destination_device_id' => $destination->getId(), 'source_device_id' => $source->getId(), 'source_channel_id' => $channel]));
        // Remove clears the sticky open slot; a new explicit demand remains Pending.
        $connection->update('supla_resource_access_decision', ['status' => 'REMOVED', 'open_pair_id' => null], ['id' => $rejected]);
        $connection->insert('supla_resource_access_decision', ['pair_id' => $pair, 'status' => 'PENDING', 'open_pair_id' => $pair,
            'permissions' => 1, 'requested_delivery_mode' => 2]);
        $pending = $connection->lastInsertId();
        $this->invalid(fn() => $connection->update('supla_resource_access_decision', ['permissions' => 2], ['id' => $pending]));
        $this->invalid(fn() => $connection->update('supla_resource_access_decision', ['requested_delivery_mode' => 0], ['id' => $pending]));
        $connection->update('supla_resource_access_decision', ['requested_delivery_mode' => 1], ['id' => $pending]);
        $this->assertSame(2, (int)$connection->fetchOne('SELECT revision FROM supla_resource_access_decision WHERE id=?', [$pending]));
        $connection->update('supla_resource_access_decision', ['requested_delivery_mode' => 1], ['id' => $pending]);
        $this->assertSame(2, (int)$connection->fetchOne('SELECT revision FROM supla_resource_access_decision WHERE id=?', [$pending]));
        $snapshot = $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id');
        $connection->delete('migration_versions', ['version' => $version]);
        self::ensureKernelShutdown();
        self::bootKernel();
        $this->application = new \Symfony\Bundle\FrameworkBundle\Console\Application(self::$kernel);
        $this->application->setAutoExit(false);
        $this->executeCommand(['command' => 'doctrine:migrations:execute', 'versions' => [$version], '--up' => true]);
        $connection = $this->getEntityManager()->getConnection();
        $this->assertSame($snapshot, $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id'));
        // Existing orphan policy is preserved: never cascade consent into another Source identity.
        $connection->delete('supla_dev_channel', ['id' => $channel]);
        $this->assertSame($channel, (int)$connection->fetchOne('SELECT source_channel_id FROM supla_resource_access_pair WHERE id=?', [$pair]));
        $this->getEntityManager()->clear();
        $user = $this->getEntityManager()->find(\App\Entity\Main\User::class, $user->getId());
        self::getContainer()->get(\App\Model\UserManager::class)->deleteAccount($user);
        $this->assertSame(0, (int)$connection->fetchOne('SELECT COUNT(*) FROM supla_resource_access_pair'));
        $this->assertSame(0, (int)$connection->fetchOne('SELECT COUNT(*) FROM supla_resource_access_decision'));
    }

    private function invalid(callable $write): void {
        try {
            $write();
            $this->fail('Expected the real MariaDB constraint/trigger to reject the write.');
        } catch (DriverException $e) {
            $this->assertNotEmpty($e->getSQLState());
        }
    }
}

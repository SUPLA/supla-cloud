<?php
namespace App\Tests\Integration\Migrations;

use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType;
use App\Tests\Integration\Traits\UserFixtures;
use Doctrine\DBAL\Exception\DriverException;

class ResourceAccessMigrationIntegrationTest extends DatabaseMigrationTestCase {
    use UserFixtures;

    public function testRealUpgradePreservesM2ProjectionM4ConfigAndDecisionRerun() {
        $this->executeCommand(['command' => 'doctrine:migrations:migrate',
            'version' => 'SuplaBundle\\Migrations\\Migration\\Version20261008160000']);
        $this->executeCommand('supla:initialize:create-sql-procedures-and-views');
        $this->executeCommand('supla:initialize:create-webapp-client');
        $user = $this->createConfirmedUser();
        $source = $this->createDevice($this->createLocation($user), [[ChannelType::THERMOMETER, CF::THERMOMETER]]);
        $destination = $this->createDevice($this->createLocation($user), [[ChannelType::HVAC, CF::HVAC_THERMOSTAT]]);
        $config = ['mainThermometerChannelId' => $source->getChannels()[0]->getId(), 'auxThermometerChannelId' => null];
        $destination->getChannels()[0]->setUserConfig($config);
        $this->flush();
        $connection = $this->getEntityManager()->getConnection();
        $connection->insert('supla_suplan_peer_association', ['user_id' => $user->getId(),
            'source_device_id' => $source->getId(), 'destination_device_id' => $destination->getId(), 'canonical_acl' => '']);
        $association = $connection->lastInsertId();
        $grant = ['association_id' => $association, 'resource_type' => 1, 'resource_id' => $source->getChannels()[0]->getId(),
            'origin_type' => 1, 'origin_id' => ($destination->getChannels()[0]->getId() << 8) | 1, 'permissions' => 1];
        $connection->insert('supla_suplan_grant', $grant);
        $version = 'SuplaBundle\\Migrations\\Migration\\Version20261011120000';
        $output = $this->executeCommand(['command' => 'doctrine:migrations:migrate', 'version' => $version]);
        $this->assertStringContainsString('Successfully migrated to version:', $output);
        $this->assertSame(1, (int)$connection->fetchOne('SELECT COUNT(*) FROM supla_suplan_grant'));
        $this->assertSame($config, json_decode($connection->fetchOne(
            'SELECT user_config FROM supla_dev_channel WHERE id=?',
            [$destination->getChannels()[0]->getId()]
        ), true));
        $connection->insert('supla_resource_access_pair', ['user_id' => $user->getId(),
            'destination_device_id' => $destination->getId(), 'source_device_id' => $source->getId(),
            'source_channel_id' => $source->getChannels()[0]->getId()]);
        $pair = $connection->lastInsertId();
        $connection->insert('supla_resource_access_decision', ['pair_id' => $pair, 'open_pair_id' => $pair,
            'status' => 'PENDING', 'permissions' => 1]);
        $decision = $connection->lastInsertId();
        $requestedAt = $connection->fetchOne('SELECT requested_at FROM supla_resource_access_decision WHERE id=?', [$decision]);
        $this->assertNotNull($requestedAt);
        $this->assertNull($connection->fetchOne('SELECT last_decision_at FROM supla_resource_access_decision WHERE id=?', [$decision]));
        // Simulate Core widening a pending request: not a user decision, creation time stays stable.
        $connection->update('supla_resource_access_decision', ['permissions' => 3], ['id' => $decision]);
        $this->assertSame($requestedAt, $connection->fetchOne('SELECT requested_at FROM supla_resource_access_decision WHERE id=?', [$decision]));
        $this->assertNull($connection->fetchOne('SELECT last_decision_at FROM supla_resource_access_decision WHERE id=?', [$decision]));
        $connection->update('supla_resource_access_decision', ['permissions' => 1], ['id' => $decision]);
        $connection->update('supla_resource_access_decision', ['status' => 'REJECTED'], ['id' => $decision]);
        $this->assertNotNull($connection->fetchOne('SELECT last_decision_at FROM supla_resource_access_decision WHERE id=?', [$decision]));
        // Records inserted before timestamp support have genuinely unknown history.
        $connection->executeStatement('DROP TRIGGER resource_access_decision_creation');
        $connection->insert('supla_resource_access_decision', ['pair_id' => $pair,
            'status' => 'ACCEPTED', 'permissions' => 1]);
        $historical = $connection->lastInsertId();
        $before = $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_decision');
        $connection->delete('migration_versions', ['version' => $version]);
        // Doctrine freezes each migration instance after execution; emulate a fresh deployment process.
        self::ensureKernelShutdown();
        self::bootKernel();
        $this->application = new \Symfony\Bundle\FrameworkBundle\Console\Application(self::$kernel);
        $this->application->setAutoExit(false);
        $connection = $this->getEntityManager()->getConnection();
        $this->executeCommand(['command' => 'doctrine:migrations:execute', 'versions' => [$version], '--up' => true]);
        $this->assertSame($before, $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_decision'));
        $this->assertNull($connection->fetchOne('SELECT requested_at FROM supla_resource_access_decision WHERE id=?', [$historical]));
        $this->assertNull($connection->fetchOne('SELECT last_decision_at FROM supla_resource_access_decision WHERE id=?', [$historical]));
        $this->assertSame(1, (int)$connection->fetchOne('SELECT COUNT(*) FROM supla_suplan_grant'));
        // Sticky rejection cannot be widened or replayed as a new pending snapshot.
        foreach ([['permissions' => 3], ['status' => 'PENDING']] as $changes) {
            try {
                $connection->update('supla_resource_access_decision', $changes, ['id' => $decision]);
                $this->fail('A rejected decision must remain immutable until reconsidered or removed.');
            } catch (DriverException $e) {
                $this->assertSame($before, $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_decision'));
            }
        }
        $connection->update('supla_resource_access_decision', ['revision' => 99], ['id' => $decision]);
        $this->assertSame(4, (int)$connection->fetchOne('SELECT revision FROM supla_resource_access_decision WHERE id=?', [$decision]));
        $migration = new \SuplaBundle\Migrations\Migration\Version20261011120000($connection, new \Psr\Log\NullLogger());
        try {
            $migration->down(new \Doctrine\DBAL\Schema\Schema());
            $this->fail('Rollback must not silently erase decisions or reuse grant identities.');
        } catch (\Doctrine\Migrations\Exception\AbortMigration $e) {
            $this->assertSame($before, $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_decision'));
        }
        $this->expectException(DriverException::class);
        // A rejected pair cannot gain a second open request.
        $connection->insert('supla_resource_access_decision', ['pair_id' => $pair, 'open_pair_id' => $pair,
            'status' => 'PENDING', 'permissions' => 3]);
    }
}

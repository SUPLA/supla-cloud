<?php
namespace App\Tests\Integration\Migrations;

use App\Entity\EntityUtils;
use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType as CT;
use App\Model\ResourceAccess\CloudResourceGrants;
use App\Model\ResourceAccess\ServerResourcePermissions;
use App\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\Console\Tester\CommandTester;

class CloudResourceGrantsMigrationIntegrationTest extends DatabaseMigrationTestCase {
    use SuplaApiHelper;

    private function fixture(): array {
        $this->executeCommand(['command' => 'doctrine:migrations:migrate',
            'version' => 'SuplaBundle\\Migrations\\Migration\\Version20261016120000']);
        $this->executeCommand('supla:initialize:create-sql-procedures-and-views');
        $this->executeCommand('supla:initialize:create-webapp-client');
        $this->getEntityManager()->getConnection()->close();
        $this->getEntityManager()->getEventManager()->addEventSubscriber(
            self::getContainer()->get(\App\Entity\Main\Listeners\CloudResourceGrantsListener::class)
        );
        $user = $this->createConfirmedUser();
        $source = $this->createDevice($this->createLocation($user), [
            [CT::THERMOMETER, CF::THERMOMETER], [CT::HUMIDITYANDTEMPSENSOR, CF::HUMIDITYANDTEMPERATURE],
            [CT::SENSORNO, CF::FLOOD_SENSOR], [CT::HVAC, CF::HVAC_THERMOSTAT],
            [CT::RELAY, CF::PUMPSWITCH], [CT::RELAY, CF::HEATORCOLDSOURCESWITCH], [CT::RELAY, CF::PUMPSWITCH],
        ]);
        $destination = $this->createDevice($this->createLocation($user), [
            [CT::HVAC, CF::HVAC_THERMOSTAT], [CT::CONTAINER, CF::CONTAINER], [CT::VALVEOPENCLOSE, CF::VALVEOPENCLOSE],
        ]);
        foreach ([$source, $destination] as $device) {
            EntityUtils::setField($device, 'flags', 524288);
            EntityUtils::setField($device, 'protocolVersion', 29);
        }
        $channels = $source->getChannels()->toArray();
        $consumers = $destination->getChannels()->toArray();
        $config = [];
        foreach (array_slice(CloudResourceGrants::slots(), 0, 6, true) as $field => $key) {
            $config[$key] = $channels[$field - 1]->getId();
        }
        $consumers[0]->setUserConfig($config);
        $consumers[1]->setUserConfig(['sensors' => array_fill(0, 10, ['channelId' => $channels[2]->getId(), 'value' => 7])]);
        $consumers[2]->setUserConfig(['floodSensorChannelIds' => array_fill(0, 20, $channels[2]->getId())]);
        $this->flush();
        $connection = $this->getEntityManager()->getConnection();
        foreach ([$source, $destination] as $device) {
            $ids = array_map(fn($ch) => $ch->getId(), $device->getChannels()->toArray());
            sort($ids);
            $connection->insert('supla_suplan_device_state', ['device_id' => $device->getId(), 'user_id' => $user->getId(),
                'current_root_epoch' => 1, 'accepted_channels' => pack('N*', ...$ids)]);
        }
        $association = (int)$connection->fetchOne(
            'SELECT id FROM supla_suplan_peer_association WHERE source_device_id=? AND destination_device_id=?',
            [$source->getId(), $destination->getId()]
        );
        $connection->update('supla_suplan_peer_association', ['peer_generation' => 9, 'acl_revision' => 17,
            'provisioned_root_epoch' => 123], ['id' => $association]);
        return [$user, $source, $destination, $channels, $consumers, $association];
    }

    private function call(string $name, array $parameters): void {
        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeQuery('CALL ' . $name . '(' . implode(',', array_fill(0, count($parameters), '?')) . ')', $parameters);
        $result->free();
    }

    public function testFinalSchemaAll36SlotsSourceFirstAndIdempotentProjection(): void {
        [$user, $source, $destination, $channels, $consumers, $association] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $this->assertSame(36, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant'));
        $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE origin_id=999'));
        $binding = $c->fetchAssociative('SELECT * FROM supla_resource_access_grant WHERE origin_id=?', [($consumers[0]->getId() << 8) | 5]);
        $this->assertSame($destination->getId(), (int)$binding['source_device_id']);
        $this->assertSame($source->getId(), (int)$binding['destination_device_id']);
        $this->assertSame($consumers[0]->getId(), (int)$binding['resource_id']);
        $before = $c->fetchAllAssociative('SELECT * FROM supla_suplan_peer_association ORDER BY id');
        $c->transactional(function () use ($c, $consumers) {
            foreach ($consumers as $consumer) {
                (new CloudResourceGrants($c))->configuration($consumer->getId(), $consumer->getUserConfig());
            }
        });
        $this->assertSame($before, $c->fetchAllAssociative('SELECT * FROM supla_suplan_peer_association ORDER BY id'));
        $this->assertSame(9, (int)$before[0]['peer_generation']);
        $this->assertSame(123, (int)$before[0]['provisioned_root_epoch']);
        $this->assertFalse($c->fetchOne("SELECT table_type FROM information_schema.tables
            WHERE table_schema=DATABASE() AND table_name IN ('supla_suplan_grant','supla_suplan_grant_retired')"));
        $permissions = self::getContainer()->get(ServerResourcePermissions::class)->list($user->getId(), [], fn() => true);
        $this->assertNotEmpty($permissions['items']);
        $this->assertSame('configuration', $permissions['items'][0]['originSummary']);
    }

    public function testConfigFlushAtomicRollbackAndDeletion(): void {
        [$user, $source, $destination, $channels, $consumers] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $before = $c->fetchAllAssociative('SELECT * FROM supla_resource_access_grant ORDER BY origin_id');
        $c->beginTransaction();
        $consumers[0]->setUserConfigValue('mainThermometerChannelId', null);
        $this->getEntityManager()->flush();
        $this->assertSame(35, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant'));
        $c->rollBack();
        $this->assertSame($before, $c->fetchAllAssociative('SELECT * FROM supla_resource_access_grant ORDER BY origin_id'));
        $this->getEntityManager()->clear();
        $channel = $this->getEntityManager()->find(\App\Entity\Main\IODeviceChannel::class, $channels[0]->getId());
        $this->getEntityManager()->remove($channel);
        $this->getEntityManager()->flush();
        $this->assertSame(35, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant'));
        $acl = $c->fetchOne('SELECT canonical_acl FROM supla_suplan_peer_association LIMIT 1');
        $this->assertStringNotContainsString(pack('CNC', 1, $channels[0]->getId(), 1), $acl);
    }

    public function testIndependentUnionNoopAndMarkerCompareAndClear(): void {
        [$user, $source, $destination, $channels, $consumers, $association] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $producer = new CloudResourceGrants($c);
        $origin = ($consumers[1]->getId() << 8) | 32;
        $initial = $c->fetchOne('SELECT acl_revision FROM supla_suplan_peer_association WHERE id=?', [$association]);
        $c->transactional(fn() => $producer->replace($user->getId(), 1, $origin, []));
        $this->assertSame($initial, $c->fetchOne('SELECT acl_revision FROM supla_suplan_peer_association WHERE id=?', [$association]));
        $token = $c->fetchOne('SELECT revision FROM supla_suplan_changed_pair WHERE association_id=?', [$association]);
        $c->transactional(fn() => $this->call('supla_suplan_changed_ack', [$user->getId(), $association, $token]));
        $this->assertFalse($c->fetchOne('SELECT revision FROM supla_suplan_changed_pair WHERE association_id=?', [$association]));
        $c->transactional(fn() => $producer->replace($user->getId(), 1, ($consumers[0]->getId() << 8) | 1, []));
        $new = $c->fetchOne('SELECT revision FROM supla_suplan_changed_pair WHERE association_id=?', [$association]);
        $this->assertGreaterThan((int)$token, (int)$new);
        $c->transactional(fn() => $this->call('supla_suplan_changed_ack', [$user->getId(), $association, $token]));
        $this->assertSame($new, $c->fetchOne('SELECT revision FROM supla_suplan_changed_pair WHERE association_id=?', [$association]));
    }

    public function testSourceFirstBindingDeletionAfterCascadePreservesOtherOriginThenDrains(): void {
        [$user, $source, $destination, $channels, $consumers] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $pair = $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association
            WHERE source_device_id=? AND destination_device_id=?', [$destination->getId(), $source->getId()]);
        $c->transactional(fn() => $this->call('supla_suplan_changed_ack', [$user->getId(), $pair['id'], $pair['change_revision']]));
        foreach ([4, 5] as $index) {
            $c->transactional(function () use ($c, $user, $channels, $index) {
                // Bypass dependency cleanup: the procedure must recover AFTER the binding FK cascaded.
                $c->delete('supla_dev_channel', ['id' => $channels[$index]->getId()]);
                $this->call('supla_suplan_resource_cleanup', [$user->getId(), $channels[$index]->getId()]);
            });
            $current = $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$pair['id']]);
            if ($index === 4) {
                $this->assertSame($pair, $current); // Independent HOCS origin still grants the same HVAC READ.
                $this->assertFalse($c->fetchOne('SELECT revision FROM supla_suplan_changed_pair WHERE association_id=?', [$pair['id']]));
            } else {
                $this->assertSame('', $current['canonical_acl']);
                $this->assertSame(2, (int)$current['lifecycle']);
                $this->assertGreaterThan((int)$pair['acl_revision'], (int)$current['acl_revision']);
                $this->assertSame($current['change_revision'], $c->fetchOne(
                    'SELECT revision FROM supla_suplan_changed_pair WHERE association_id=?',
                    [$pair['id']]
                ));
            }
        }
        $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant
            WHERE source_device_id=? AND destination_device_id=?', [$destination->getId(), $source->getId()]));
        $this->assertSame(34, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant'));
        // A repeated cleanup must not bump the empty ACL or its marker again.
        $c->transactional(fn() => $this->call('supla_suplan_resource_cleanup', [$user->getId(), $channels[5]->getId()]));
        $this->assertSame($current, $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$pair['id']]));
    }

    public function testSourceFirstOrmDeletionProjectsInsideBusinessTransaction(): void {
        [$user, $source, $destination, $channels, $consumers] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $consumers[0]->setUserConfigValue('heatOrColdSourceSwitchChannelId', null);
        $this->flush();
        $before = $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association
            WHERE source_device_id=? AND destination_device_id=?', [$destination->getId(), $source->getId()]);
        $pumpId = $channels[4]->getId();
        $c->beginTransaction();
        try {
            $this->getEntityManager()->remove($channels[4]);
            $this->getEntityManager()->flush();
            $this->assertSame('', $c->fetchOne('SELECT canonical_acl FROM supla_suplan_peer_association WHERE id=?', [$before['id']]));
            $this->assertSame(2, (int)$c->fetchOne('SELECT lifecycle FROM supla_suplan_peer_association WHERE id=?', [$before['id']]));
        } finally {
            $c->rollBack();
        }
        $this->assertSame($before, $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$before['id']]));
        $this->assertSame(1, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant
            WHERE binding_target_channel_id=?', [$pumpId]));
    }

    public function testVirtualAndLegacyManualStreamNoPeerAndDirectDeviceJsonHasNoAuthority(): void {
        [$user, $source, $destination, $channels] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $virtual = $this->createDevice($this->createLocation($user), [[CT::THERMOMETERDS18B20, CF::THERMOMETER]]);
        EntityUtils::setField($virtual, 'isVirtual', true);
        $this->flush();
        $channel = $virtual->getChannels()[0]->getId();
        $c->insert('supla_resource_access_pair', ['user_id' => $user->getId(), 'source_device_id' => $virtual->getId(),
            'destination_device_id' => $destination->getId(), 'source_channel_id' => $channel]);
        $pair = $c->lastInsertId();
        $c->insert('supla_resource_access_decision', ['pair_id' => $pair, 'status' => 'ACCEPTED', 'permissions' => 1,
            'requested_delivery_mode' => 2]);
        $id = (int)$c->lastInsertId();
        $producer = new CloudResourceGrants($c);
        $c->transactional(fn() => $producer->decision($id));
        $this->assertSame(1, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE origin_type=2 AND origin_id=?', [$id]));
        $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_suplan_peer_association WHERE source_device_id=?', [$virtual->getId()]));
        // The same canonical resource also works as a physical legacy Source, with no SupLAN flag/identity.
        EntityUtils::setField($virtual, 'isVirtual', false);
        $this->flush();
        $eligibility = $c->fetchOne('SELECT peer_eligible FROM supla_resource_access_grant WHERE origin_type=2 AND origin_id=?', [$id]);
        $this->assertSame(0, (int)$eligibility);
        $readable = $c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant_valid WHERE origin_type=2 AND origin_id=?', [$id]);
        $this->assertSame(1, (int)$readable);
        $before = $c->fetchAllAssociative('SELECT * FROM supla_resource_access_grant ORDER BY origin_type,origin_id');
        $c->update('supla_dev_channel', ['user_config' => json_encode(['mainThermometerChannelId' => $channels[0]->getId()])], ['id' => $channel]);
        $this->assertSame($before, $c->fetchAllAssociative('SELECT * FROM supla_resource_access_grant ORDER BY origin_type,origin_id'));
        $c->transactional(function () use ($c, $producer, $id) {
            $c->update('supla_resource_access_decision', ['status' => 'REMOVED'], ['id' => $id]);
            $producer->decision($id);
        });
        $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE origin_type=2'));
    }
    public function testFirstSourceAckPreservesConfirmedContextAndRejectsStaleAck(): void {
        [$user, $source, $destination, $channels, $consumers, $association] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $c->update('supla_suplan_peer_association', ['provisioned_root_epoch' => 0], ['id' => $association]);
        $c->transactional(fn() => $this->call('supla_suplan_accept_identity', [$user->getId(), $source->getId(), 77,
            pack('N*', ...array_map(fn($ch) => $ch->getId(), $channels))]));
        $before = $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$association]);
        $parameters = [$user->getId(), $association, $before['peer_generation'], $before['acl_revision'], 77];
        $c->transactional(fn() => $this->call('supla_suplan_peer_prepare', $parameters));
        $this->assertSame($before, $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$association]));
        $ack = [$user->getId(), $association, 77, $before['peer_generation'], $before['acl_revision'], 1, 0];
        $c->transactional(fn() => $this->call('supla_suplan_peer_ack', $ack));
        $after = $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$association]);
        $this->assertSame(77, (int)$after['provisioned_root_epoch']);
        $this->assertSame($before['peer_generation'], $after['provisioned_peer_generation']);
        $this->assertSame($before['peer_generation'], $after['peer_generation']);
        $this->assertSame($before['acl_revision'], $after['acl_revision']);
        $ack[4]--;
        try {
            $c->transactional(fn() => $this->call('supla_suplan_peer_ack', $ack));
            $this->fail('Stale ACK must fail.');
        } catch (\Doctrine\DBAL\Exception\DriverException $e) {
            $this->assertStringContainsString('Stale SupLAN ACK', $e->getMessage());
        }
        $this->assertSame($after, $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$association]));
    }

    public function testSourceFirstExactTargetAndRestrictedCorePrivileges(): void {
        [$user, $source, $destination, $channels, $consumers] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $binding = $c->fetchAssociative('SELECT * FROM supla_resource_access_grant WHERE origin_id=?', [($consumers[0]->getId() << 8) | 5]);
        $this->assertSame($channels[4]->getId(), (int)$binding['binding_target_channel_id']);
        foreach ([$source, $destination] as $device) {
            $ids = array_map(fn($ch) => $ch->getId(), $device->getChannels()->toArray());
            sort($ids);
            $c->transactional(fn() => $this->call('supla_suplan_accept_identity', [$user->getId(), $device->getId(), 1, pack('N*', ...$ids)]));
        }
        $account = 'grants_test_' . bin2hex(random_bytes(4));
        $c->executeStatement("CREATE USER '$account'@'%' IDENTIFIED BY 'disposable-only'");
        $database = $c->quoteIdentifier($c->getDatabase());
        $c->executeStatement("GRANT SELECT ON $database.* TO '$account'@'%'");
        $this->executeCommand('supla:initialize:grant-suplan-core-procedures ' . $account . ' %');
        $params = $c->getParams();
        $params['user'] = $account;
        $params['password'] = 'disposable-only';
        $restricted = \Doctrine\DBAL\DriverManager::getConnection($params);
        try {
            $restricted->transactional(fn() => $restricted->executeQuery(
                'CALL supla_suplan_share_demand(?,?,?,?)',
                [$user->getId(), $source->getId(), $consumers[0]->getId(), $channels[4]->getId()]
            )->free());
            $this->assertTrue(true);
            foreach (
                [
                'UPDATE supla_resource_access_grant SET permissions=7',
                "CALL supla_resource_access_replace(1,1,1,'[]')",
                ] as $sql
            ) {
                try {
                    $restricted->executeStatement($sql);
                    $this->fail('Restricted Core must not author business grants.');
                } catch (\Doctrine\DBAL\Exception\DriverException $e) {
                    $this->assertStringContainsString('denied', $e->getMessage());
                }
            }
            try {
                $restricted->transactional(fn() => $restricted->executeQuery(
                    'CALL supla_suplan_share_demand(?,?,?,?)',
                    [$user->getId(), $source->getId(), $consumers[0]->getId(), $channels[6]->getId()]
                )->free());
                $this->fail('Another channel on the same Device is not the exact binding.');
            } catch (\Doctrine\DBAL\Exception\DriverException $e) {
                $this->assertStringContainsString('No exact Cloud Source-first binding', $e->getMessage());
            }
        } finally {
            $restricted->close();
            $c->executeStatement("DROP USER '$account'@'%'");
        }
    }

    public function testRestrictedCoreSetupRejectsBusinessExecuteBeforeGrantingTechnicalPrivileges(): void {
        $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $account = 'grants_test_' . bin2hex(random_bytes(4));
        $c->executeStatement("CREATE USER '$account'@'%' IDENTIFIED BY 'disposable-only'");
        $database = $c->quoteIdentifier($c->getDatabase());
        try {
            $c->executeStatement("GRANT SELECT ON $database.* TO '$account'@'%'");
            $c->executeStatement("GRANT EXECUTE ON PROCEDURE $database.supla_resource_access_replace TO '$account'@'%'");
            $before = $c->fetchFirstColumn("SHOW GRANTS FOR '$account'@'%'");
            $command = new CommandTester($this->application->find('supla:initialize:grant-suplan-core-procedures'));
            try {
                $command->execute(['user' => $account, 'host' => '%']);
                $this->fail('Business EXECUTE must prevent technical privilege setup.');
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('Restrict Core DML', $e->getMessage());
            }
            $this->assertSame($before, $c->fetchFirstColumn("SHOW GRANTS FOR '$account'@'%'"));
        } finally {
            $c->executeStatement("DROP USER '$account'@'%'");
        }
    }

    public function testManualDecisionAtomicOriginsStickyRejectionAndRemoval(): void {
        [$user, $source, $destination, $channels] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $parameters = [$user->getId(), $destination->getId(), $channels[6]->getId(), 1, 1, 0];
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_decision'));
        $parameters[5] = 1;
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $service = self::getContainer()->get(\App\Model\ResourceAccess\ResourceAccessDecisions::class);
        $row = $c->fetchAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id DESC LIMIT 1');
        $snapshot = fn($r) => ['status' => $r['status'], 'permissions' => (int)$r['permissions'], 'revision' => (int)$r['revision']];
        $service->change($user->getId(), (int)$row['id'], $snapshot($row), 'reject', fn() => true);
        $parameters[3] = 3;
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $row = $c->fetchAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id DESC LIMIT 1');
        $this->assertSame('REJECTED', $row['status']);
        $this->assertSame(1, (int)$row['permissions']);
        $service->change($user->getId(), (int)$row['id'], $snapshot($row), 'approve', fn() => true);
        $this->assertSame(1, (int)$c->fetchOne('SELECT permissions FROM supla_resource_access_grant WHERE origin_type=2 AND origin_id=?', [$row['id']]));
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $extension = $c->fetchAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id DESC LIMIT 1');
        $this->assertSame(2, (int)$extension['permissions']);
        $service->change($user->getId(), (int)$extension['id'], $snapshot($extension), 'approve', fn() => true);
        $this->assertSame(3, (int)$c->fetchOne('SELECT BIT_OR(permissions) FROM supla_resource_access_grant WHERE origin_type=2'));
        $extension = $c->fetchAssociative('SELECT * FROM supla_resource_access_decision WHERE id=?', [$extension['id']]);
        $service->change($user->getId(), (int)$extension['id'], $snapshot($extension), 'remove', fn() => true);
        $this->assertSame(1, (int)$c->fetchOne('SELECT BIT_OR(permissions) FROM supla_resource_access_grant WHERE origin_type=2'));
        $this->assertSame(36, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE origin_type=1'));
    }

    public function testSqlProjectionFailureRollsBackBusinessConfig(): void {
        [$user, $source, $destination, $channels, $consumers] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $before = $c->fetchOne('SELECT user_config FROM supla_dev_channel WHERE id=?', [$consumers[0]->getId()]);
        $grants = $c->fetchAllAssociative('SELECT * FROM supla_resource_access_grant ORDER BY origin_id');
        $c->executeStatement("CREATE TRIGGER test_projection_failure BEFORE UPDATE ON supla_suplan_peer_association
            FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected projection failure'");
        try {
            $consumers[0]->setUserConfigValue('mainThermometerChannelId', null);
            $this->getEntityManager()->flush();
            $this->fail('Projection failure must fail config flush.');
        } catch (\Doctrine\DBAL\Exception\DriverException $e) {
            $this->assertStringContainsString('Injected projection failure', $e->getMessage());
        } finally {
            $c->executeStatement('DROP TRIGGER test_projection_failure');
        }
        $this->assertSame($before, $c->fetchOne('SELECT user_config FROM supla_dev_channel WHERE id=?', [$consumers[0]->getId()]));
        $this->assertSame($grants, $c->fetchAllAssociative('SELECT * FROM supla_resource_access_grant ORDER BY origin_id'));
    }

    public function testMetadataInvalidationKeepsCompatibleOriginsAndNeverResurrects(): void {
        [$user, $source, $destination, $channels, $consumers] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $channels[0]->setFunction(CF::NONE());
        $this->getEntityManager()->flush();
        $this->assertSame(35, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant'));
        $channels[0]->setFunction(CF::THERMOMETER());
        $this->getEntityManager()->flush();
        $this->assertSame(35, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant'));
        $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE resource_id=?', [$channels[0]->getId()]));
        $consumers[0]->setUserConfigValue('unrelatedSetting', 5);
        $this->getEntityManager()->flush();
        $this->assertSame(35, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant'));
        $channels[2]->setFunction(CF::OPENINGSENSOR_WINDOW());
        $this->getEntityManager()->flush();
        $this->assertSame(35, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant'));
    }

    public function testDrainingAckParityOrdinaryDormantVersusRemovedEndpointDelete(): void {
        [$user, $source, $destination, $channels, $consumers, $association] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        foreach ([false, true] as $removed) {
            $c->update('supla_suplan_peer_association', ['canonical_acl' => '', 'lifecycle' => 2,
                'source_empty_acked' => 0, 'destination_empty_acked' => 0, 'source_removed' => (int)$removed,
                'provisioned_root_epoch' => 123], ['id' => $association]);
            $a = $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$association]);
            foreach ([1, 2] as $side) {
                $c->transactional(fn() => $this->call('supla_suplan_peer_ack', [$user->getId(), $association, 123,
                    $a['peer_generation'], $a['acl_revision'], $side, 1]));
            }
            $row = $c->fetchAssociative('SELECT * FROM supla_suplan_peer_association WHERE id=?', [$association]);
            if ($removed) {
                $this->assertFalse($row);
            } else {
                $this->assertSame(3, (int)$row['lifecycle']);
            }
        }
    }
    public function testNormalizedPermissionsApiAccountIsolationAndOriginDetail(): void {
        [$user, $source, $destination, $channels, $consumers] = $this->fixture();
        $foreign = $this->createConfirmedUser('grants-foreign@supla.org');
        $client = $this->createAuthenticatedClient($user);
        $client->apiRequestV3('GET', '/api/resource-access/permissions?limit=1');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $page = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $page['items']);
        $this->assertNotNull($page['nextCursor']);
        $entry = $page['items'][0];
        $path = '/api/resource-access/permissions/' . $entry['resourceKey'] . '/1/' . $entry['resourceId'];
        $client->apiRequestV3('GET', $path);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $detail = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($detail['origins']);
        $this->assertSame('configuration', $detail['origins'][0]['kind']);
        $client->apiRequestV3('GET', '/api/resource-access/permissions?limit=1&cursor=' . $page['nextCursor']);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $next = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotSame($entry['resourceId'], $next['items'][0]['resourceId']);
        $client = $this->createAuthenticatedClient($foreign);
        $client->apiRequestV3('GET', $path);
        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $client->apiRequestV3('GET', '/api/resource-access/permissions');
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true)['items']);
    }

    public function testConcurrentRevokeCannotBeUndoneByStaleDecisionWorker(): void {
        [$user, $source, $destination, $channels] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $c->insert('supla_resource_access_pair', ['user_id' => $user->getId(), 'source_device_id' => $source->getId(),
            'destination_device_id' => $destination->getId(), 'source_channel_id' => $channels[6]->getId()]);
        $pair = $c->lastInsertId();
        $c->insert('supla_resource_access_decision', ['pair_id' => $pair, 'status' => 'ACCEPTED', 'permissions' => 1]);
        $id = (int)$c->lastInsertId();
        $c->transactional(fn() => (new CloudResourceGrants($c))->decision($id));
        $other = \Doctrine\DBAL\DriverManager::getConnection($c->getParams());
        $other->executeStatement('SET innodb_lock_wait_timeout=1');
        try {
            $c->beginTransaction();
            $c->fetchOne('SELECT id FROM supla_user WHERE id=? FOR UPDATE', [$user->getId()]);
            $c->update('supla_resource_access_decision', ['status' => 'REMOVED'], ['id' => $id]);
            try {
                $other->transactional(fn() => (new CloudResourceGrants($other))->decision($id));
                $this->fail('Worker must serialize against the uncommitted decision.');
            } catch (\Doctrine\DBAL\Exception\LockWaitTimeoutException $e) {
                $this->assertNotEmpty($e->getSQLState());
            }
            (new CloudResourceGrants($c))->decision($id);
            $c->commit();
            $other->transactional(fn() => (new CloudResourceGrants($other))->decision($id));
            $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE origin_type=2 AND origin_id=?', [$id]));
        } finally {
            if ($c->isTransactionActive()) {
                $c->rollBack();
            }
            $other->close();
        }
    }
    public function testOlderRepeatableReadSnapshotCannotLoseAnotherCommittedOrigin(): void {
        [$user, $source, $destination, $channels, $consumers, $association] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $other = \Doctrine\DBAL\DriverManager::getConnection($c->getParams());
        try {
            $c->beginTransaction();
            $c->fetchAllAssociative('SELECT * FROM supla_resource_access_grant'); // Deliberately establish an older RR snapshot.
            $other->transactional(function () use ($other, $user, $source, $destination, $channels) {
                $other->insert('supla_resource_access_pair', ['user_id' => $user->getId(), 'source_device_id' => $source->getId(),
                    'destination_device_id' => $destination->getId(), 'source_channel_id' => $channels[6]->getId()]);
                $pair = $other->lastInsertId();
                $other->insert('supla_resource_access_decision', ['pair_id' => $pair, 'status' => 'ACCEPTED', 'permissions' => 1]);
                (new CloudResourceGrants($other))->decision((int)$other->lastInsertId());
            });
            $consumers[0]->setUserConfigValue('mainThermometerChannelId', null);
            $this->getEntityManager()->flush();
            $c->commit();
            $acl = $c->fetchOne('SELECT canonical_acl FROM supla_suplan_peer_association WHERE id=?', [$association]);
            $this->assertStringContainsString(pack('CNC', 1, $channels[6]->getId(), 1), $acl);
            $this->assertSame(1, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE origin_type=2'));
        } finally {
            if ($c->isTransactionActive()) {
                $c->rollBack();
            }
            $other->close();
        }
    }


    public function testDeviceJsonAloneCannotRetargetAnOriginDuringUnrelatedCloudEdit(): void {
        [$user, $source, $destination, $channels, $consumers] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $id = $consumers[0]->getId();
        $config = $consumers[0]->getUserConfig();
        $config['mainThermometerChannelId'] = $channels[1]->getId();
        // Stronger than the protected Core merge: simulate arbitrary raw JSON already present in the DB.
        $c->update('supla_dev_channel', ['user_config' => json_encode($config)], ['id' => $id]);
        $old = $c->fetchOne('SELECT resource_id FROM supla_resource_access_grant WHERE origin_type=1 AND origin_id=?', [($id << 8) | 1]);
        $this->assertSame($channels[0]->getId(), (int)$old);
        $this->getEntityManager()->clear();
        $consumer = $this->getEntityManager()->find(\App\Entity\Main\IODeviceChannel::class, $id);
        $consumer->setUserConfigValue('unrelatedCloudSetting', 1);
        $this->getEntityManager()->flush();
        $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE origin_type=1 AND origin_id=?', [($id << 8) | 1]));
        $this->assertSame(1, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE origin_type=1 AND origin_id=?', [($id << 8) | 2]));
    }
    public function testFirstPeerApprovalCreatesAssociationAndMarkerWithoutAnotherEnsure(): void {
        [$user, $source, $destination, $channels] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $requester = $this->createDevice($this->createLocation($user), [[CT::RELAY, CF::LIGHTSWITCH]]);
        EntityUtils::setField($requester, 'flags', 524288);
        EntityUtils::setField($requester, 'protocolVersion', 29);
        $this->flush();
        $c->transactional(fn() => $this->call('supla_suplan_accept_identity', [$user->getId(), $requester->getId(), 1,
            pack('N', $requester->getChannels()[0]->getId())]));
        $c->transactional(fn() => $this->call('supla_resource_access_pending', [$user->getId(), $requester->getId(), $channels[0]->getId(), 1, 1, 1]));
        $row = $c->fetchAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id DESC LIMIT 1');
        $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_suplan_peer_association WHERE destination_device_id=?', [$requester->getId()]));
        $service = self::getContainer()->get(\App\Model\ResourceAccess\ResourceAccessDecisions::class);
        $service->change($user->getId(), (int)$row['id'], ['revision' => (int)$row['revision'],
            'permissions' => (int)$row['permissions'], 'status' => $row['status']], 'approve', fn() => true);
        $association = $c->fetchAssociative(
            'SELECT * FROM supla_suplan_peer_association WHERE source_device_id=? AND destination_device_id=?',
            [$source->getId(), $requester->getId()]
        );
        $this->assertNotFalse($association);
        $this->assertSame(pack('CNC', 1, $channels[0]->getId(), 1), $association['canonical_acl']);
        $this->assertSame(1, (int)$association['lifecycle']);
        $this->assertSame(1, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_suplan_changed_pair WHERE association_id=?', [$association['id']]));
    }
    public function testPendingReadToControlWideningIsCanonicalAndInvalidatesOldSnapshot(): void {
        [$user, $source, $destination, $channels] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $parameters = [$user->getId(), $destination->getId(), $channels[6]->getId(), 1, 1, 1];
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $before = $c->fetchAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id DESC LIMIT 1');
        $parameters[3] = 3;
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $after = $c->fetchAssociative('SELECT * FROM supla_resource_access_decision WHERE id=?', [$before['id']]);
        $this->assertSame(2, (int)$after['permissions']);
        $this->assertSame((int)$before['revision'] + 1, (int)$after['revision']);
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $this->assertSame($after, $c->fetchAssociative('SELECT * FROM supla_resource_access_decision WHERE id=?', [$before['id']]));
        try {
            self::getContainer()->get(\App\Model\ResourceAccess\ResourceAccessDecisions::class)->change(
                $user->getId(),
                (int)$before['id'],
                ['revision' => (int)$before['revision'], 'permissions' => (int)$before['permissions'], 'status' => $before['status']],
                'approve',
                fn() => true
            );
            $this->fail('The displayed READ snapshot must not authorize widened CONTROL.');
        } catch (\App\Exception\ApiException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $this->assertSame(0, (int)$c->fetchOne('SELECT COUNT(*) FROM supla_resource_access_grant WHERE origin_type=2'));
    }

    public function testActualStreamEnsureRecordsAcceptedModeWithFlagsZeroWithoutConsentOrGrantChanges(): void {
        [$user, $source, $destination, $channels] = $this->fixture();
        $c = $this->getEntityManager()->getConnection();
        $c->insert('supla_resource_access_pair', ['user_id' => $user->getId(), 'source_device_id' => $source->getId(),
            'destination_device_id' => $destination->getId(), 'source_channel_id' => $channels[6]->getId()]);
        $pair = $c->lastInsertId();
        foreach ([1, 2] as $permissions) {
            $c->insert('supla_resource_access_decision', ['pair_id' => $pair, 'status' => 'ACCEPTED',
                'permissions' => $permissions, 'requested_delivery_mode' => 1]);
            $id = (int)$c->lastInsertId();
            $c->transactional(fn() => (new CloudResourceGrants($c))->decision($id));
        }
        $before = $c->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id');
        $grants = $c->fetchAllAssociative('SELECT * FROM supla_resource_access_grant ORDER BY origin_type,origin_id');
        $peers = $c->fetchAllAssociative('SELECT * FROM supla_suplan_peer_association ORDER BY id');
        $parameters = [$user->getId(), $destination->getId(), $channels[6]->getId(), 1, 2, 0];
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $after = $c->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id');
        $this->assertCount(2, $after);
        foreach ($after as $i => $row) {
            $this->assertSame(2, (int)$row['requested_delivery_mode']);
            $this->assertSame((int)$before[$i]['revision'] + 1, (int)$row['revision']);
            $row['requested_delivery_mode'] = $before[$i]['requested_delivery_mode'];
            $row['revision'] = $before[$i]['revision'];
            $this->assertSame($before[$i], $row);
        }
        $this->assertSame($grants, $c->fetchAllAssociative('SELECT * FROM supla_resource_access_grant ORDER BY origin_type,origin_id'));
        $this->assertSame($peers, $c->fetchAllAssociative('SELECT * FROM supla_suplan_peer_association ORDER BY id'));
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $this->assertSame($after, $c->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id'));
        // Read-only publish/quiet authorization checks do not CALL the ENSURE bookkeeping procedure.
        $c->fetchOne(
            'SELECT BIT_OR(permissions) FROM supla_resource_access_grant_valid WHERE user_id=? AND resource_id=?',
            [$user->getId(), $channels[6]->getId()]
        );
        $this->assertSame($after, $c->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id'));
        // Revocation racing ENSURE must not turn Flags0 into another Pending.
        $c->transactional(function () use ($c, $pair) {
            $c->executeStatement("UPDATE supla_resource_access_decision SET status='REMOVED' WHERE pair_id=?", [$pair]);
            foreach ($c->fetchFirstColumn('SELECT id FROM supla_resource_access_decision WHERE pair_id=?', [$pair]) as $id) {
                (new CloudResourceGrants($c))->decision((int)$id);
            }
        });
        $removed = $c->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id');
        $c->transactional(fn() => $this->call('supla_resource_access_pending', $parameters));
        $this->assertSame($removed, $c->fetchAllAssociative('SELECT * FROM supla_resource_access_decision ORDER BY id'));
    }
}

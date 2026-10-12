<?php
namespace App\Tests\Integration\Controller;

use App\Entity\EntityUtils;
use App\Entity\Main\User;
use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType;
use App\Enums\IoDeviceFlags;
use App\Supla\SuplaServerMock;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;

class ResourceAccessControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    private ?User $user = null;
    private ?array $deviceIds = null;
    private ?array $channelIds = null;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->deviceIds = [];
        $this->channelIds = [];
        foreach (range(0, 1) as $i) {
            $device = $this->createDevice($this->createLocation($this->freshEntity($this->user)), [
                [ChannelType::RELAY, CF::LIGHTSWITCH], [ChannelType::THERMOMETER, CF::THERMOMETER],
                [ChannelType::ACTION_TRIGGER, CF::ACTION_TRIGGER],
            ]);
            EntityUtils::setField($device, 'flags', IoDeviceFlags::SUPLAN_SUPPORTED);
            EntityUtils::setField($device, 'protocolVersion', 29);
            EntityUtils::setField($device, 'lastConnected', null);
            EntityUtils::setField($device->getChannels()[2], 'properties', json_encode(['actionTriggerCapabilities' => ['TURN_ON', 'TURN_OFF']]));
            $this->deviceIds[] = $device->getId();
            $this->channelIds[] = array_map(fn($channel) => $channel->getId(), $device->getChannels()->toArray());
        }
        $this->flush();
        // Preserve an existing independent M2 CHANNEL_CONFIG origin in all mutation tests.
        $this->executeCommand(['command' => 'doctrine:migrations:execute',
            'versions' => ['SuplaBundle\\Migrations\\Migration\\Version20261007104248'], '--up' => true]);
        $connection = $this->getEntityManager()->getConnection();
        $connection->insert('supla_suplan_peer_association', ['user_id' => $this->user->getId(),
            'source_device_id' => $this->deviceIds[0], 'destination_device_id' => $this->deviceIds[1], 'canonical_acl' => '']);
        $association = $connection->lastInsertId();
        $connection->insert('supla_suplan_grant', ['association_id' => $association, 'resource_type' => 1,
            'resource_id' => $this->channelIds[0][0], 'origin_type' => 1, 'origin_id' => 123, 'permissions' => 1]);
        // Execute the actual versioned migration on the isolated framework database.
        $this->executeCommand(['command' => 'doctrine:migrations:execute',
            'versions' => ['SuplaBundle\\Migrations\\Migration\\Version20261011120000'], '--up' => true]);
    }

    private function decision(int $permissions = 1, string $status = 'PENDING', int $sourceIndex = 0): int {
        $connection = $this->getEntityManager()->getConnection();
        $pair = $connection->fetchOne('SELECT id FROM supla_resource_access_pair WHERE source_channel_id=?', [$this->channelIds[0][$sourceIndex]]);
        if (!$pair) {
            $connection->insert('supla_resource_access_pair', ['user_id' => $this->user->getId(),
                'destination_device_id' => $this->deviceIds[1], 'source_device_id' => $this->deviceIds[0],
                'source_channel_id' => $this->channelIds[0][$sourceIndex]]);
            $pair = $connection->lastInsertId();
        }
        $connection->insert('supla_resource_access_decision', ['pair_id' => $pair,
            'open_pair_id' => in_array($status, ['PENDING', 'REJECTED']) ? $pair : null,
            'status' => $status, 'permissions' => $permissions, 'revision' => 1]);
        return (int)$connection->lastInsertId();
    }

    private function row(int $id): array {
        return $this->getEntityManager()->getConnection()->fetchAssociative('SELECT * FROM supla_resource_access_decision WHERE id=?', [$id]);
    }

    private function mutate(int $id, string $action, ?array $snapshot = null, int $status = 204, ?User $user = null): void {
        $row = $snapshot ?? $this->row($id);
        $client = $this->createAuthenticatedClient($user ?? $this->user);
        $client->apiRequestV3('POST', '/api/resource-access/' . $id . '/' . $action, [
            'revision' => (int)$row['revision'], 'status' => $row['status'], 'permissions' => (int)$row['permissions'],
        ]);
        $this->assertStatusCode($status, $client->getResponse());
    }

    public function testExactApproveRejectReconsiderAndDuplicateSnapshot() {
        $id = $this->decision(3);
        $before = $this->row($id);
        $this->mutate($id, 'reject');
        $this->assertSame('REJECTED', $this->row($id)['status']);
        $this->mutate($id, 'approve', $before, 409);
        $this->mutate($id, 'approve'); // No fresh ENSURE or device configuration required.
        $this->assertSame('ACCEPTED', $this->row($id)['status']);
        $this->assertSame(3, (int)$this->row($id)['permissions']);
        $this->mutate($id, 'approve', $before, 409);
        $command = 'USER-ON-CHANNEL-CONFIG-CHANGED:' . implode(',', [
            $this->user->getId(), $this->deviceIds[0], $this->channelIds[0][0], ChannelType::RELAY, CF::LIGHTSWITCH, 256,
        ]);
        $this->assertSame([$command, $command], SuplaServerMock::$executedCommands);
    }

    public function testWidenedPendingInvalidatesBothApproveAndRejectForms() {
        $id = $this->decision();
        $before = $this->row($id);
        $this->getEntityManager()->getConnection()->update('supla_resource_access_decision', ['permissions' => 3], ['id' => $id]);
        $this->assertSame(2, (int)$this->row($id)['revision']);
        $this->mutate($id, 'approve', $before, 409);
        $this->mutate($id, 'reject', $before, 409);
        $this->assertSame('PENDING', $this->row($id)['status']);
        $this->mutate($id, 'approve');
    }

    public static function states(): array {
        return [['PENDING'], ['REJECTED'], ['ACCEPTED']];
    }

    /** @dataProvider states */
    public function testRemoveResetsOnlySelectedOriginAndFreshRequestIsPending(string $status) {
        $accepted = $this->decision(1, 'ACCEPTED');
        $selected = $this->decision(2, $status);
        $before = $this->row($selected);
        $this->mutate($selected, 'remove');
        $this->assertSame('REMOVED', $this->row($selected)['status']);
        $this->assertSame('ACCEPTED', $this->row($accepted)['status']);
        $this->mutate($selected, 'approve', $before, 409);
        $new = $this->decision(2); // Emulate the next legitimate Core request, not Cloud-originated demand.
        $this->assertSame('PENDING', $this->row($new)['status']);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/resource-access');
        $this->assertStatusCode(200, $client->getResponse());
        $entries = $client->getResponseBody();
        $this->assertCount(2, $entries);
        $this->assertSame(1, $entries[0]['acceptedManualPermissions']);
        $this->assertSame(1, $entries[0]['independentPermissions']);
        $this->assertSame(1, (int)$this->getEntityManager()->getConnection()->fetchOne('SELECT permissions FROM supla_suplan_grant'));
        $this->assertSame('NOT_CONFIRMED', $entries[0]['provisioningStatus']);
    }

    public function testForeignAccountCannotListReadOrMutateDecision() {
        $id = $this->decision();
        $foreign = $this->createConfirmedUser('foreign-resource@supla.org');
        $client = $this->createAuthenticatedClient($foreign);
        $client->apiRequestV3('GET', '/api/resource-access');
        $this->assertSame([], $client->getResponseBody());
        $client->apiRequestV3('GET', '/api/resource-access/' . $id);
        $this->assertStatusCode(404, $client->getResponse());
        $this->mutate($id, 'approve', null, 404, $foreign);
        $this->assertSame('PENDING', $this->row($id)['status']);
    }

    public function testActionRequiresSupportedAtAndNoEventMaskSelection() {
        $invalid = $this->decision(4);
        $this->mutate($invalid, 'approve', null, 409);
        $valid = $this->decision(4, 'PENDING', 2);
        $this->mutate($valid, 'approve');
        $this->assertSame(4, (int)$this->row($valid)['permissions']);
        $unsupportedControl = $this->decision(2, 'PENDING', 1);
        $this->mutate($unsupportedControl, 'approve', null, 409);
    }

    public function testFunctionNoneAndDeletedSourceCannotApproveButCanRemove() {
        $id = $this->decision();
        $connection = $this->getEntityManager()->getConnection();
        $connection->update('supla_dev_channel', ['func' => CF::NONE], ['id' => $this->channelIds[0][0]]);
        $this->mutate($id, 'approve', null, 409);
        $connection->delete('supla_dev_channel', ['id' => $this->channelIds[0][0]]);
        $this->mutate($id, 'approve', null, 409);
        $this->mutate($id, 'remove');
    }

    public function testReadOnlyTokenAndCookieWithoutBearerCannotChangeDecision() {
        $id = $this->decision();
        $oauth = self::getContainer()->get(\App\Auth\SuplaOAuth2::class);
        $token = $oauth->createPersonalAccessToken(
            $this->freshEntity($this->user),
            'Resource read',
            new \App\Auth\OAuthScope(['account_r', 'iodevices_r', 'channels_r'])
        );
        $this->persist($token);
        self::ensureKernelShutdown();
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token->getToken(), 'HTTPS' => true]);
        $client->apiRequestV3('GET', '/api/resource-access');
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertFalse($client->getResponseBody()[0]['canManage']);
        $this->assertFalse($client->getResponseBody()[0]['canApprove']);
        $client->apiRequestV3('POST', '/api/resource-access/' . $id . '/approve', ['revision' => 1, 'status' => 'PENDING', 'permissions' => 1]);
        $this->assertStatusCode(403, $client->getResponse());
        self::ensureKernelShutdown();
        $client = self::createClient(['debug' => false], ['HTTPS' => true, 'HTTP_COOKIE' => 'PHPSESSID=forged']);
        $client->apiRequestV3('POST', '/api/resource-access/' . $id . '/approve', ['revision' => 1, 'status' => 'PENDING', 'permissions' => 1]);
        $this->assertStatusCode(401, $client->getResponse());
        $this->assertSame('PENDING', $this->row($id)['status']);
    }

    public function testAccessIdMustCoverBothSourceAndDestination() {
        $id = $this->decision();
        $destination = $this->getEntityManager()->find(\App\Entity\Main\IODevice::class, $this->deviceIds[1]);
        $accessId = new \App\Entity\Main\AccessID($this->freshEntity($this->user));
        $accessId->setPassword('abcd');
        $accessId->getLocations()->add($destination->getLocation());
        $this->persist($accessId);
        $token = $this->getEntityManager()->getRepository(\App\Entity\Main\OAuth\AccessToken::class)
            ->findOneBy(['token' => base64_encode($this->user->getUsername())]);
        EntityUtils::setField($token, 'accessId', $this->freshEntity($accessId));
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/resource-access');
        $this->assertSame([], $client->getResponseBody());
        $this->mutate($id, 'approve', null, 403);
        $this->assertSame('PENDING', $this->row($id)['status']);
    }

    public function testMovedSourceAndLostSupLanCapabilityFailWithoutNotification() {
        $id = $this->decision();
        $connection = $this->getEntityManager()->getConnection();
        $connection->update('supla_iodevice', ['flags' => 0], ['id' => $this->deviceIds[1]]);
        SuplaServerMock::$executedCommands = [];
        $this->mutate($id, 'approve', null, 409);
        $this->assertSame([], SuplaServerMock::$executedCommands);
        $connection->update('supla_iodevice', ['flags' => IoDeviceFlags::SUPLAN_SUPPORTED], ['id' => $this->deviceIds[1]]);
        $connection->update('supla_dev_channel', ['iodevice_id' => $this->deviceIds[1], 'channel_number' => 99], ['id' => $this->channelIds[0][0]]);
        $this->mutate($id, 'approve', null, 409);
        $this->assertSame('PENDING', $this->row($id)['status']);
    }

    public function testDatabaseFenceRejectsConcurrentPairLockWithoutAuthorizing() {
        $id = $this->decision();
        $row = $this->row($id);
        $connection = $this->getEntityManager()->getConnection();
        $other = \Doctrine\DBAL\DriverManager::getConnection($connection->getParams());
        $other->beginTransaction();
        $other->fetchOne('SELECT id FROM supla_resource_access_pair WHERE id=? FOR UPDATE', [$row['pair_id']]);
        try {
            $client = $this->createAuthenticatedClient($this->user);
            self::getContainer()->get('doctrine')->getManager()->getConnection()->executeStatement('SET SESSION innodb_lock_wait_timeout=1');
            $client->apiRequestV3('POST', '/api/resource-access/' . $id . '/approve', ['revision' => 1, 'status' => 'PENDING', 'permissions' => 1]);
            $this->assertStatusCode(409, $client->getResponse());
        } finally {
            $other->rollBack();
            $other->close();
        }
        $this->assertSame('PENDING', $this->row($id)['status']);
        $this->mutate($id, 'approve');
    }

    public function testUnsupportedActionAndExtraEventMaskCannotWidenGrant() {
        $id = $this->decision(4, 'PENDING', 2);
        $connection = $this->getEntityManager()->getConnection();
        $connection->update('supla_dev_channel', ['properties' => '{}'], ['id' => $this->channelIds[0][2]]);
        $this->mutate($id, 'approve', null, 409);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3(
            'POST',
            '/api/resource-access/' . $id . '/approve',
            ['revision' => 1, 'status' => 'PENDING', 'permissions' => 4, 'eventMask' => 1]
        );
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame('PENDING', $this->row($id)['status']);
    }

    public function testExistingIndependentDeviceScopeGrantIsDisplayedAndPreserved() {
        $id = $this->decision();
        $connection = $this->getEntityManager()->getConnection();
        $association = $connection->fetchOne('SELECT id FROM supla_suplan_peer_association');
        $connection->insert('supla_suplan_grant', ['association_id' => $association, 'resource_type' => 2,
            'resource_id' => $this->deviceIds[0], 'origin_type' => 99, 'origin_id' => 456, 'permissions' => 2]);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/resource-access/' . $id);
        $this->assertSame(3, $client->getResponseBody()['independentPermissions']);
        $this->mutate($id, 'remove');
        $this->assertSame(2, (int)$connection->fetchOne('SELECT COUNT(*) FROM supla_suplan_grant'));
    }

    public function testTransferredSourceMetadataIsHiddenAndCannotBeApproved() {
        $id = $this->decision();
        $foreign = $this->createConfirmedUser('transferred-resource@supla.org');
        $connection = $this->getEntityManager()->getConnection();
        $connection->update(
            'supla_dev_channel',
            ['user_id' => $foreign->getId(), 'caption' => 'Foreign private caption'],
            ['id' => $this->channelIds[0][0]]
        );
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/resource-access/' . $id);
        $this->assertStatusCode(200, $client->getResponse());
        $entry = $client->getResponseBody();
        $this->assertNull($entry['sourceCaption']);
        $this->assertNull($entry['sourceFunction']);
        $this->assertFalse($entry['canApprove']);
        $this->mutate($id, 'approve', null, 409);
        SuplaServerMock::$executedCommands = [];
        $this->mutate($id, 'remove');
        $this->assertSame([], SuplaServerMock::$executedCommands);
    }

    public function testRequestAndDecisionTimestampsAreUtcAndOnlyAdvanceOnDecision() {
        $id = $this->decision();
        $requestedAt = $this->row($id)['requested_at'];
        $this->assertNotNull($requestedAt);
        $this->assertNull($this->row($id)['last_decision_at']);
        $this->mutate($id, 'reject');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/resource-access/' . $id);
        $entry = $client->getResponseBody();
        $this->assertSame($requestedAt . '+00:00', str_replace('T', ' ', $entry['requestedAt']));
        $this->assertStringEndsWith('+00:00', $entry['lastDecisionAt']);
        $this->assertNotNull($entry['lastDecisionAt']);
        $before = $this->row($id);
        $this->mutate($id, 'approve');
        $this->assertSame($requestedAt, $this->row($id)['requested_at']);
        $this->assertGreaterThanOrEqual($before['last_decision_at'], $this->row($id)['last_decision_at']);
        $this->mutate($id, 'remove');
        $removed = $this->row($id);
        $this->assertNotNull($removed['last_decision_at']);
        $this->mutate($id, 'approve', $before, 409);
        $this->assertSame($removed, $this->row($id));
    }

    public function testDeviceFilterIncludesBothRolesAndPreservesAccountScope() {
        $outbound = $this->decision();
        $connection = $this->getEntityManager()->getConnection();
        $insert = function (int $source, int $destination, int $channel) use ($connection): int {
            $connection->insert('supla_resource_access_pair', ['user_id' => $this->user->getId(),
                'source_device_id' => $source, 'destination_device_id' => $destination, 'source_channel_id' => $channel]);
            $pair = $connection->lastInsertId();
            $connection->insert('supla_resource_access_decision', ['pair_id' => $pair, 'open_pair_id' => $pair,
                'status' => 'PENDING', 'permissions' => 1]);
            return (int)$connection->lastInsertId();
        };
        $inbound = $insert($this->deviceIds[1], $this->deviceIds[0], $this->channelIds[1][0]);
        $other = $this->createDevice($this->createLocation($this->freshEntity($this->user)), [[ChannelType::THERMOMETER, CF::THERMOMETER]]);
        $unrelated = $insert($other->getId(), $this->deviceIds[1], $other->getChannels()[0]->getId());
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/resource-access?deviceId=' . $this->deviceIds[0]);
        $this->assertStatusCode(200, $client->getResponse());
        $ids = array_column($client->getResponseBody(), 'id');
        $this->assertEqualsCanonicalizing([$outbound, $inbound], $ids);
        $this->assertNotContains($unrelated, $ids);
        $client->apiRequestV3('GET', '/api/resource-access?destinationDeviceId=' . $this->deviceIds[0]);
        $this->assertSame([$inbound], array_column($client->getResponseBody(), 'id'));
        $foreign = $this->createConfirmedUser('foreign-device-filter@supla.org');
        $client = $this->createAuthenticatedClient($foreign);
        $client->apiRequestV3('GET', '/api/resource-access?deviceId=' . $this->deviceIds[0]);
        $this->assertSame([], $client->getResponseBody());
        $client->apiRequestV3('GET', '/api/resource-access?deviceId=invalid');
        $this->assertStatusCode(404, $client->getResponse());
    }
}

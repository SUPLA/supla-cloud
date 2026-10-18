<?php
namespace App\Tests\Integration\Controller;

use App\Entity\EntityUtils;
use App\Entity\Main\User;
use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType as CT;
use App\Supla\SuplaServerMock;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;

class ServerResourcePermissionsControllerIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    private ?User $user = null;
    private ?array $devices = [];
    private ?array $channels = [];
    private ?string $association = '';

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        foreach (range(0, 1) as $i) {
            $device = $this->createDevice($this->createLocation($this->freshEntity($this->user)), [
                [CT::THERMOMETER, CF::THERMOMETER], [CT::HVAC, CF::HVAC_THERMOSTAT],
                [CT::CONTAINER, CF::CONTAINER], [CT::VALVEOPENCLOSE, CF::VALVEOPENCLOSE],
                [CT::RELAY, CF::LIGHTSWITCH], [CT::ACTION_TRIGGER, CF::ACTION_TRIGGER],
                [CT::SENSORNO, CF::OPENINGSENSOR_GATE], [CT::RELAY, CF::PUMPSWITCH], [CT::RELAY, CF::HEATORCOLDSOURCESWITCH],
            ]);
            EntityUtils::setField($device, 'lastConnected', null);
            EntityUtils::setField($device, 'flags', 524288);
            EntityUtils::setField($device, 'protocolVersion', 29);
            EntityUtils::setField($device->getChannels()[5], 'properties', json_encode(['actionTriggerCapabilities' => ['TURN_ON']]));
            $this->devices[$i] = $device->getId();
            $this->channels[$i] = array_map(fn($channel) => $channel->getId(), $device->getChannels()->toArray());
        }
        $this->flush();
        $connection = $this->getEntityManager()->getConnection();
        $connection->insert('supla_suplan_peer_association', ['user_id' => $this->user->getId(),
            'source_device_id' => $this->devices[0], 'destination_device_id' => $this->devices[1],
            'lifecycle' => 1, 'canonical_acl' => '']);
        $this->association = (string)$connection->lastInsertId();
    }

    private function project(array $entries): void {
        $blob = '';
        foreach ($entries as [$type, $id, $permissions]) {
            $blob .= pack('CNC', $type, $id, $permissions);
        }
        // Fixture of Core's atomic committed projection, not execution of Core reconciliation.
        $this->getEntityManager()->getConnection()->update('supla_suplan_peer_association', ['canonical_acl' => $blob], ['id' => $this->association]);
    }

    private function grant(int $resource, int $originType, int|string $originId, int $permissions = 1, int $resourceType = 1): void {
        $c = $this->getEntityManager()->getConnection();
        $channel = $c->fetchAssociative('SELECT * FROM supla_dev_channel WHERE id=?', [$resource]);
        $c->insert('supla_resource_access_grant', ['user_id' => $this->user->getId(),
            'source_device_id' => $this->devices[0], 'destination_device_id' => $this->devices[1],
            'resource_type' => $resourceType, 'resource_id' => $resource, 'origin_type' => $originType,
            'origin_id' => $originId, 'permissions' => $permissions, 'source_type' => $channel['type'],
            'source_function' => $channel['func'], 'peer_eligible' => 1, 'requires_peer' => 0,
            'binding_target_channel_id' => $originType === 1 && in_array((int)$originId & 255, [5, 6], true)
                ? $this->channels[1][((int)$originId & 255) === 5 ? 7 : 8] : null]);
    }

    private function get(string $suffix = '', int $status = 200, ?User $user = null): array {
        $client = $this->createAuthenticatedClient($user ?? $this->user);
        $client->apiRequestV3('GET', '/api/resource-access/permissions' . $suffix);
        $this->assertStatusCode($status, $client->getResponse());
        return $client->getResponseBody();
    }

    private function detail(int $resource, int $type = 1, string $suffix = '', int $status = 200, ?User $user = null): array {
        return $this->get('/logical-' . $this->devices[1] . '/' . $type . '/' . $resource . $suffix, $status, $user);
    }

    public static function configurationFields(): array {
        return [[1, 1, 'Main thermometer', null], [2, 1, 'Auxiliary thermometer', null], [3, 1, 'Binary sensor', null],
            [4, 1, 'Master thermostat', null], [5, 1, 'Pump switch', null], [6, 1, 'Heat or cold source switch', null],
            [32, 2, 'Container sensor', 0], [41, 2, 'Container sensor', 9],
            [64, 3, 'Valve flood sensor', 0], [83, 3, 'Valve flood sensor', 19]];
    }

    /** @dataProvider configurationFields */
    public function testConfigOnlyFieldsAndSourceFirstDirection(int $code, int $consumerIndex, string $label, ?int $slot) {
        $binding = in_array($code, [5, 6], true);
        $consumer = $this->channels[$binding ? 0 : 1][$consumerIndex];
        $sourceIndex = $code === 4 ? 1 : ($code === 3 || $code >= 32 ? 6 : 0);
        $resource = $binding ? $consumer : $this->channels[0][$sourceIndex];
        $origin = ($consumer << 8) | $code;
        $reference = $binding ? $this->channels[1][$code === 5 ? 7 : 8] : $resource;
        if ($code <= 6) {
            $keys = [1 => 'mainThermometerChannelId', 2 => 'auxThermometerChannelId', 3 => 'binarySensorChannelId',
                4 => 'masterThermostatChannelId', 5 => 'pumpSwitchChannelId', 6 => 'heatOrColdSourceSwitchChannelId'];
            $config = [$keys[$code] => $reference];
        } elseif ($code < 64) {
            $sensors = array_fill(0, $slot + 1, null);
            $sensors[$slot] = ['channelId' => $reference, 'fillLevel' => 75];
            $config = ['sensors' => $sensors];
        } else {
            $sensors = array_fill(0, $slot + 1, null);
            $sensors[$slot] = $reference;
            $config = ['floodSensorChannelIds' => $sensors];
        }
        $this->getEntityManager()->getConnection()->update('supla_dev_channel', ['user_config' => json_encode($config)], ['id' => $consumer]);
        $this->grant($resource, 1, $origin);
        $this->project([[1, $resource, 1]]);
        $list = $this->get();
        $this->assertCount(1, $list['items']);
        $this->assertSame('configuration', $list['items'][0]['originSummary']);
        $this->assertSame($this->devices[0], $list['items'][0]['sourceDeviceId']);
        $this->assertSame($this->devices[1], $list['items'][0]['destinationDeviceId']);
        $detail = $this->detail($resource);
        $this->assertSame(['label' => $label, 'slot' => $slot], $detail['origins'][0]['field']);
        $this->assertSame($consumer, $detail['origins'][0]['configuringChannel']['id']);
        $this->assertSame((string)$origin, $detail['origins'][0]['originId']);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/resource-access');
        $this->assertSame([], $client->getResponseBody());
    }

    private function decision(int $resource, string $status, int $mask): int {
        $connection = $this->getEntityManager()->getConnection();
        $pair = $connection->fetchOne('SELECT id FROM supla_resource_access_pair WHERE source_channel_id=?', [$resource]);
        if (!$pair) {
            $connection->insert('supla_resource_access_pair', ['user_id' => $this->user->getId(),
                'source_device_id' => $this->devices[0], 'destination_device_id' => $this->devices[1], 'source_channel_id' => $resource]);
            $pair = $connection->lastInsertId();
        }
        $connection->insert('supla_resource_access_decision', ['pair_id' => $pair, 'status' => $status, 'permissions' => $mask,
            'open_pair_id' => $status === 'ACCEPTED' ? null : $pair]);
        return (int)$connection->lastInsertId();
    }

    public function testManualProjectionAndPartialExtensionsAreDistinctFromDecisions() {
        $read = $this->channels[0][0];
        $control = $this->channels[0][4];
        $action = $this->channels[0][5];
        foreach ([[$read, 1], [$control, 2], [$action, 4]] as [$resource, $mask]) {
            $id = $this->decision($resource, 'ACCEPTED', $mask);
            $this->grant($resource, 2, $id, $mask);
        }
        $pending = $this->decision($read, 'PENDING', 2);
        $rejected = $this->decision($control, 'REJECTED', 4);
        $this->assertCount(3, $this->get()['items']); // Logical grants are visible without peer projection.
        $this->project([[1, $read, 1], [1, $control, 2], [1, $action, 4]]);
        $items = $this->get()['items'];
        $this->assertSame([1, 3, 4], array_column($items, 'permissions'));
        foreach ($items as $item) {
            $detail = $this->detail($item['resourceId']);
            $this->assertNotNull($detail['origins'][0]['decisionId']);
            $this->assertNotContains($detail['origins'][0]['decisionId'], [(string)$pending, (string)$rejected]);
        }
    }

    public function testOverlappingOriginsAndReplacementPreserveProjectedRights() {
        $resource = $this->channels[0][4];
        $config = ($this->channels[1][1] << 8) | 1;
        $manual = $this->decision($resource, 'ACCEPTED', 1);
        $this->grant($resource, 1, $config);
        $this->grant($resource, 2, $manual);
        $this->project([[1, $resource, 1]]);
        $this->assertSame('mixed', $this->get()['items'][0]['originSummary']);
        $this->assertCount(2, $this->detail($resource)['origins']);
        $connection = $this->getEntityManager()->getConnection();
        $connection->update('supla_resource_access_grant', ['permissions' => 2], ['origin_type' => 2, 'origin_id' => $manual]);
        $this->project([[1, $resource, 2]]);
        $this->assertSame(3, $this->get()['items'][0]['permissions']);
        $connection->delete('supla_resource_access_grant', ['origin_type' => 2, 'origin_id' => $manual]);
        $this->project([[1, $resource, 1]]);
        $this->assertSame(1, $this->get()['items'][0]['permissions']);
        $this->assertCount(1, $this->detail($resource)['origins']);
        $connection->delete('supla_resource_access_grant', ['origin_type' => 1, 'origin_id' => $config]);
        $this->grant($resource, 2, $manual);
        $this->assertSame('user-approved', $this->get()['items'][0]['originSummary']);
    }

    public function testDeviceScopeFiltersPaginationIndependentOriginsAndReadHasNoWrites() {
        $channel = $this->channels[0][0];
        $this->grant($channel, 1, ($this->channels[1][1] << 8) | 1);
        $this->grant($this->channels[0][4], 2, '18446744073709551615', 2);
        $this->project([[1, $channel, 1], [1, $this->channels[0][4], 2]]);
        $connection = $this->getEntityManager()->getConnection();
        $before = $connection->fetchAllAssociative('SELECT * FROM supla_suplan_peer_association');
        $grants = $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_grant');
        $page = $this->get('?limit=1');
        $this->assertCount(1, $page['items']);
        $this->assertNotNull($page['nextCursor']);
        $second = $this->get('?limit=1&cursor=' . $page['nextCursor']);
        $this->assertSame(1, $second['items'][0]['resourceType']);
        $this->assertNull($second['nextCursor']);
        $this->assertSame([], $this->get('?sourceDeviceId=' . $this->devices[1])['items']);
        $this->assertCount(2, $this->get('?deviceId=' . $this->devices[1] . '&permission=1')['items']);
        $this->assertCount(1, $this->get('?origin=configuration')['items']);
        $this->assertCount(1, $this->get('?origin=user-approved&permission=2')['items']);
        $detail = $this->detail($this->channels[0][4]);
        $this->assertSame('18446744073709551615', $detail['origins'][0]['originId']);
        $this->assertNull($detail['origins'][0]['configuringChannel']);
        $this->assertSame($before, $connection->fetchAllAssociative('SELECT * FROM supla_suplan_peer_association'));
        $this->assertSame($grants, $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_grant'));
        $this->assertSame([], SuplaServerMock::$executedCommands);
        foreach (['limit=51', 'permission=3', 'origin=invalid', 'cursor=bad', 'sourceDeviceId=-1'] as $query) {
            $this->get('?' . $query, 400);
        }
    }

    public function testEmptyLifecycleDeletedAndMovedResourcesNeverAppear() {
        $resource = $this->channels[0][0];
        $this->grant($resource, 1, 999);
        $this->project([[1, $resource, 1]]);
        $connection = $this->getEntityManager()->getConnection();
        foreach ([2, 3] as $lifecycle) {
            $connection->update('supla_suplan_peer_association', ['lifecycle' => $lifecycle], ['id' => $this->association]);
            $this->assertCount(1, $this->get()['items']); // Provisioning lifecycle is not logical authorization.
            $this->detail($resource);
        }
        $connection->update('supla_suplan_peer_association', ['lifecycle' => 1], ['id' => $this->association]);
        $connection->update('supla_dev_channel', ['func' => 0], ['id' => $resource]);
        $this->assertSame([], $this->get()['items']);
        $connection->update('supla_dev_channel', ['func' => CF::THERMOMETER, 'iodevice_id' => $this->devices[1],
            'channel_number' => 127], ['id' => $resource]);
        $this->assertSame([], $this->get()['items']);
        $connection->delete('supla_dev_channel', ['id' => $resource]);
        $this->assertSame([], $this->get()['items']);
        $this->project([]);
        $this->assertSame([], $this->get()['items']);
    }

    public function testForeignAccountReadOnlyRoleAndNoMutationEndpoint() {
        $resource = $this->channels[0][0];
        $this->grant($resource, 1, ($this->channels[1][1] << 8) | 1);
        $this->project([[1, $resource, 1]]);
        $foreign = $this->createConfirmedUser('foreign-permissions@supla.org');
        $this->assertSame([], $this->get('', 200, $foreign)['items']);
        $this->detail($resource, 1, '', 404, $foreign);
        $oauth = self::getContainer()->get(\App\Auth\SuplaOAuth2::class);
        $token = $oauth->createPersonalAccessToken(
            $this->freshEntity($this->user),
            'Permissions read',
            new \App\Auth\OAuthScope(['account_r', 'iodevices_r', 'channels_r'])
        );
        $this->persist($token);
        self::ensureKernelShutdown();
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token->getToken(), 'HTTPS' => true]);
        $client->apiRequestV3('GET', '/api/resource-access/permissions');
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV3('POST', '/api/resource-access/permissions');
        $this->assertStatusCode(405, $client->getResponse());
        self::ensureKernelShutdown();
        $client = self::createClient(['debug' => false], ['HTTPS' => true]);
        $client->apiRequestV3('GET', '/api/resource-access/permissions');
        $this->assertStatusCode(401, $client->getResponse());
    }

    public function testOriginsArePagedWithoutLosingIndependentContributions() {
        $resource = $this->channels[0][0];
        for ($id = 1; $id <= 102; $id++) {
            $this->grant($resource, 2, $id);
        }
        $this->project([[1, $resource, 1]]);
        $first = $this->detail($resource);
        $this->assertCount(100, $first['origins']);
        $second = $this->detail($resource, 1, '?cursor=' . $first['nextOriginCursor']);
        $this->assertCount(2, $second['origins']);
        $this->assertSame(['101', '102'], array_column($second['origins'], 'originId'));
        $this->assertNull($second['nextOriginCursor']);
    }

    public function testForeignConfigMetadataAndTransferredEndpointAreHidden() {
        $foreign = $this->createConfirmedUser('foreign-origin@supla.org');
        $device = $this->createDevice($this->createLocation($foreign), [[CT::HVAC, CF::HVAC_THERMOSTAT]]);
        $consumer = $device->getChannels()[0]->getId();
        $resource = $this->channels[0][0];
        $this->grant($resource, 1, ($consumer << 8) | 1);
        $this->project([[1, $resource, 1]]);
        $detail = $this->detail($resource);
        $this->assertNull($detail['origins'][0]['configuringChannel']);
        $connection = $this->getEntityManager()->getConnection();
        $connection->update('supla_iodevice', ['user_id' => $foreign->getId()], ['id' => $this->devices[0]]);
        $this->assertSame([], $this->get()['items']);
        $this->detail($resource, 1, '', 404);
    }

    public function testProjectionQueryCountDoesNotGrowWithPageRows() {
        $entries = [];
        foreach ($this->channels[0] as $resource) {
            $entries[] = [1, $resource, 1];
            $this->grant($resource, 2, 1);
        }
        $this->project($entries);
        $connection = $this->getEntityManager()->getConnection();
        $logger = new \Doctrine\DBAL\Logging\DebugStack();
        $configuration = $connection->getConfiguration();
        $previous = $configuration->getSQLLogger();
        $service = self::getContainer()->get(\App\Model\ResourceAccess\ServerResourcePermissions::class);
        try {
            $configuration->setSQLLogger($logger);
            $one = $service->list($this->user->getId(), ['limit' => 1], fn($row) => true);
            $oneCount = count($logger->queries);
            $logger->queries = [];
            $many = $service->list($this->user->getId(), ['limit' => 50], fn($row) => true);
            $this->assertCount(1, $one['items']);
            $this->assertCount(9, $many['items']);
            $this->assertLessThanOrEqual(5, count($logger->queries));
            $this->assertSame($oneCount, count($logger->queries));
        } finally {
            $configuration->setSQLLogger($previous);
        }
    }
}

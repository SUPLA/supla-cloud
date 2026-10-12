<?php
namespace App\Model\ResourceAccess;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelConfigChangeScope;
use App\Enums\ChannelFunction as CF;
use App\Enums\IoDeviceFlags;
use App\Exception\ApiException;
use App\Supla\SuplaServer;
use Doctrine\ORM\EntityManagerInterface;

/** Shared SQL business state. Core must lock the pair before ENSURE/decision writes. */
class ResourceAccessDecisions {
    public function __construct(private EntityManagerInterface $entityManager, private SuplaServer $server) {
    }

    public static function normalize(int $permissions): int {
        return $permissions & 2 ? $permissions | 1 : $permissions;
    }

    public function list(
        int $userId,
        callable $authorize,
        ?int $destination = null,
        ?int $device = null,
        ?int $before = null,
        int $limit = 50,
        ?string $status = null
    ): array {
        $filter = '';
        $parameters = [$userId];
        if ($destination !== null) {
            $filter .= ' AND p.destination_device_id=?';
            $parameters[] = $destination;
        }
        if ($device !== null) {
            $filter .= ' AND (p.destination_device_id=? OR p.source_device_id=?)';
            array_push($parameters, $device, $device);
        }
        if ($before !== null) {
            $filter .= ' AND d.id<?';
            $parameters[] = $before;
        }
        if ($status !== null) {
            $filter .= ' AND d.status=?';
            $parameters[] = $status;
        }
        return $this->read($parameters, $filter, $authorize, min(50, max(1, $limit)));
    }

    public function detail(int $userId, int $id, callable $authorize): ?array {
        return $this->read([$userId, $id], ' AND d.id=?', $authorize, 1)['items'][0] ?? null;
    }

    private function tables(): array {
        // Schema-managed Core data is deliberately excluded from Doctrine's schema filter.
        return $this->entityManager->getConnection()->fetchFirstColumn("SELECT table_name FROM information_schema.tables
            WHERE table_schema=DATABASE() AND table_name IN ('supla_suplan_device_state','supla_suplan_grant','supla_suplan_peer_association')");
    }

    private function read(array $parameters, string $filter, callable $authorize, int $limit): array {
        return $this->entityManager->getConnection()->transactional(function ($connection) use ($parameters, $filter, $authorize, $limit) {
            $tables = $this->tables();
            $select = 'd.*,p.user_id,p.destination_device_id,p.source_device_id,p.source_channel_id';
            foreach (['src' => 's', 'dst' => 't'] as $prefix => $alias) {
                foreach (['id', 'user_id', 'name', 'flags', 'protocol_version'] as $field) {
                    $select .= ",$alias.$field {$prefix}_$field";
                }
            }
            foreach (['id', 'user_id', 'iodevice_id', 'type', 'func', 'caption', 'properties'] as $field) {
                $select .= ",c.$field channel_$field";
            }
            $hasIdentity = in_array('supla_suplan_device_state', $tables, true);
            $select .= $hasIdentity ? ',i.current_root_epoch,i.accepted_channels' : ',NULL current_root_epoch,NULL accepted_channels';
            $rows = $connection->fetchAllAssociative('SELECT ' . $select . '
                FROM supla_resource_access_decision d JOIN supla_resource_access_pair p ON p.id=d.pair_id
                LEFT JOIN supla_iodevice s ON s.id=p.source_device_id AND s.user_id=p.user_id
                LEFT JOIN supla_iodevice t ON t.id=p.destination_device_id AND t.user_id=p.user_id
                LEFT JOIN supla_dev_channel c ON c.id=p.source_channel_id AND c.user_id=p.user_id '
                . ($hasIdentity ? 'LEFT JOIN supla_suplan_device_state i ON i.device_id=p.source_device_id AND i.user_id=p.user_id ' : '')
                . 'WHERE p.user_id=? AND d.status<>\'REMOVED\'' . $filter . ' ORDER BY d.id DESC LIMIT ' . ($limit + 1), $parameters);
            $more = count($rows) > $limit;
            $rows = array_slice($rows, 0, $limit);
            $items = [];
            if ($rows) {
                $pairIds = array_unique(array_column($rows, 'pair_id'));
                $placeholders = implode(',', array_fill(0, count($pairIds), '?'));
                $manual = $connection->fetchAllKeyValue('SELECT pair_id,BIT_OR(permissions) FROM supla_resource_access_decision
                    WHERE status=\'ACCEPTED\' AND pair_id IN (' . $placeholders . ') GROUP BY pair_id', array_values($pairIds));
                $independent = [];
                if (in_array('supla_suplan_grant', $tables, true) && in_array('supla_suplan_peer_association', $tables, true)) {
                    $independent = $connection->fetchAllKeyValue('SELECT p.id,BIT_OR(g.permissions) FROM supla_resource_access_pair p
                        JOIN supla_suplan_peer_association a ON a.user_id=p.user_id AND a.source_device_id=p.source_device_id
                            AND a.destination_device_id=p.destination_device_id
                        JOIN supla_suplan_grant g ON g.association_id=a.id AND g.origin_type<>2
                            AND ((g.resource_type=1 AND g.resource_id=p.source_channel_id)
                                OR (g.resource_type=2 AND g.resource_id=p.source_device_id))
                        WHERE p.id IN (' . $placeholders . ') GROUP BY p.id', array_values($pairIds));
                }
                // The normal voter uses these managed subjects; hydrate each type once per page.
                $deviceIds = array_unique([...array_column($rows, 'source_device_id'), ...array_column($rows, 'destination_device_id')]);
                $this->entityManager->getRepository(IODevice::class)->findBy(['id' => $deviceIds]);
                $this->entityManager->getRepository(IODeviceChannel::class)->findBy(['id' => array_unique(array_column($rows, 'source_channel_id'))]);
                foreach ($rows as $row) {
                    $devices = [];
                    foreach (['src' => $row['source_device_id'], 'dst' => $row['destination_device_id']] as $prefix => $id) {
                        $devices[(int)$id] = $row[$prefix . '_id'] === null ? false : $this->unprefix($row, $prefix);
                    }
                    $context = ['source' => $row['channel_id'] === null ? false : $this->unprefix($row, 'channel'),
                        'devices' => $devices,
                        'identity' => ['current_root_epoch' => $row['current_root_epoch'], 'accepted_channels' => $row['accepted_channels']]];
                    if ($authorize($row, $context)) {
                        $items[] = $this->view(
                            $row,
                            $context,
                            self::normalize((int)($manual[$row['pair_id']] ?? 0)),
                            self::normalize((int)($independent[$row['pair_id']] ?? 0))
                        );
                    }
                }
            }
            $last = end($rows);
            return ['items' => $items, 'nextCursor' => $more ? (string)$last['id'] : null];
        });
    }

    private function unprefix(array $row, string $prefix): array {
        $result = [];
        foreach ($row as $key => $value) {
            if (str_starts_with($key, $prefix . '_')) {
                $result[substr($key, strlen($prefix) + 1)] = $value;
            }
        }
        return $result;
    }

    public function change(int $userId, int $id, array $snapshot, string $action, callable $authorize): void {
        $connection = $this->entityManager->getConnection();
        // Notify only after the outermost transaction commits; never send a rolled-back decision.
        ApiException::throwIf($connection->isTransactionActive(), 'An access decision requires its own transaction.', [], 409);
        try {
            $sourceId = $connection->transactional(function () use ($connection, $userId, $id, $snapshot, $action, $authorize) {
                $pairId = $connection->fetchOne(
                    'SELECT p.id FROM supla_resource_access_pair p JOIN supla_resource_access_decision d ON d.pair_id=p.id '
                    . 'WHERE d.id=? AND p.user_id=?',
                    [$id, $userId]
                );
                ApiException::throwIf(!$pairId, 'Access decision not found.', [], 404);
                $pair = $connection->fetchAssociative('SELECT * FROM supla_resource_access_pair WHERE id=? FOR UPDATE', [$pairId]);
                $decision = $connection->fetchAssociative('SELECT * FROM supla_resource_access_decision WHERE id=? FOR UPDATE', [$id]);
                ApiException::throwIf(!$pair || !$decision, 'Access decision changed. Refresh and try again.', [], 409);
                $row = $decision + $pair;
                foreach (['revision', 'permissions', 'status'] as $key) {
                    $current = $key === 'status' ? $row[$key] : (int)$row[$key];
                    ApiException::throwIf(($snapshot[$key] ?? null) !== $current, 'Access decision changed. Refresh and try again.', [], 409);
                }
                $context = $this->context($row, true);
                ApiException::throwIf(!$authorize($row, $context), 'Access denied.', [], 403);
                ApiException::throwIf(!in_array($action, ['approve', 'reject', 'remove'], true), 'Invalid access decision action.');
                ApiException::throwIf($row['status'] === 'REMOVED', 'Access decision changed. Refresh and try again.', [], 409);
                if ($action === 'approve') {
                    ApiException::throwIf(!in_array($row['status'], ['PENDING', 'REJECTED'], true), 'Access decision changed. Refresh and try again.', [], 409);
                    ApiException::throwIf(!$this->compatible($row, $context), 'The source or destination is no longer compatible.', [], 409);
                    $status = 'ACCEPTED';
                } elseif ($action === 'reject') {
                    ApiException::throwIf($row['status'] !== 'PENDING', 'Access decision changed. Refresh and try again.', [], 409);
                    $status = 'REJECTED';
                } else {
                    $status = 'REMOVED';
                }
                $connection->update('supla_resource_access_decision', [
                'status' => $status,
                'open_pair_id' => $status === 'REJECTED' ? (int)$pairId : null,
                'revision' => (int)$row['revision'] + 1,
                ], ['id' => $id]);
                return (int)$row['source_channel_id'];
            });
        } catch (\Doctrine\DBAL\Exception\RetryableException $e) {
            throw new ApiException('Access decision changed. Refresh and try again.', 409, $e);
        }
        $source = $this->entityManager->find(IODeviceChannel::class, $sourceId);
        if ($source && $source->belongsToUser($this->entityManager->find(\App\Entity\Main\User::class, $userId))) {
            // Existing M2 channel; Core must reconcile manual business origins on this scope.
            $this->server->channelConfigChanged($source, ChannelConfigChangeScope::RELATIONS);
            $this->server->flushPostponedCommands();
        }
    }

    private function context(array $row, bool $lock): array {
        $connection = $this->entityManager->getConnection();
        $suffix = $lock ? ' FOR UPDATE' : '';
        // Lock IDs in stable order to serialize Source changes and multiple users/tabs.
        $devices = [];
        $ids = array_unique([(int)$row['source_device_id'], (int)$row['destination_device_id']]);
        sort($ids);
        foreach ($ids as $id) {
            $devices[$id] = $connection->fetchAssociative('SELECT id,user_id,name,flags,protocol_version FROM supla_iodevice WHERE id=?' . $suffix, [$id]);
        }
        $source = $connection->fetchAssociative(
            'SELECT id,user_id,iodevice_id,type,func,caption,properties FROM supla_dev_channel WHERE id=?' . $suffix,
            [$row['source_channel_id']]
        );
        $identity = false;
        if (in_array('supla_suplan_device_state', $this->tables(), true)) {
            // Same order as Core approval_context: pair, decision, sorted Devices, Source Channel, identity.
            $identity = $connection->fetchAssociative('SELECT current_root_epoch,accepted_channels FROM supla_suplan_device_state
                WHERE device_id=? AND user_id=?' . $suffix, [$row['source_device_id'], $row['user_id']]);
        }
        return ['source' => $source, 'devices' => $devices, 'identity' => $identity];
    }

    private function compatible(array $row, array $context): bool {
        $source = $context['source'];
        $permissions = (int)$row['permissions'];
        if (!$source || $permissions < 1 || $permissions > 7 || (int)$source['iodevice_id'] !== (int)$row['source_device_id']) {
            return false;
        }
        foreach ($context['devices'] as $device) {
            if (
                !$device || (int)$device['user_id'] !== (int)$row['user_id']
                || (int)$device['protocol_version'] < 29 || !((int)$device['flags'] & IoDeviceFlags::SUPLAN_SUPPORTED)
            ) {
                return false;
            }
        }
        if ((int)$source['user_id'] !== (int)$row['user_id'] || (int)$row['source_device_id'] === (int)$row['destination_device_id']) {
            return false;
        }
        if (!self::acceptedIdentity($context['identity'], (int)$row['source_channel_id'])) {
            return false;
        }
        $properties = json_decode($source['properties'] ?: '{}', true);
        return ResourceAccessPermissions::supported(
            (int)$source['type'],
            (int)$source['func'],
            $permissions,
            is_array($properties) ? $properties : []
        );
    }

    private function view(array $row, array $context, int $manual, int $independent): array {
        $source = $context['source'];
        return [
            'id' => (int)$row['id'], 'revision' => (int)$row['revision'], 'status' => $row['status'],
            'permissions' => (int)$row['permissions'], 'origin' => 'USER_APPROVAL',
            'requestedAt' => $this->timestamp($row['requested_at'] ?? null),
            'lastDecisionAt' => $this->timestamp($row['last_decision_at'] ?? null),
            'destinationDeviceId' => (int)$row['destination_device_id'],
            'destinationName' => $context['devices'][(int)$row['destination_device_id']]['name'] ?? null,
            'sourceDeviceId' => (int)$row['source_device_id'],
            'sourceDeviceName' => $context['devices'][(int)$row['source_device_id']]['name'] ?? null,
            'sourceChannelId' => (int)$row['source_channel_id'], 'sourceCaption' => $source['caption'] ?? null,
            'sourceFunction' => $source && CF::isValid((int)$source['func']) ? CF::fromString((int)$source['func'])->getName() : null,
            'sourceFunctionId' => $source ? (int)$source['func'] : null,
            'canApprove' => $this->compatible($row, $context) && in_array($row['status'], ['PENDING', 'REJECTED'], true),
            'acceptedManualPermissions' => $manual, 'independentPermissions' => $independent,
            'provisioningStatus' => 'NOT_CONFIRMED',
        ];
    }

    private static function acceptedIdentity(array|false $identity, int $channel): bool {
        if (!$identity || (int)$identity['current_root_epoch'] < 1 || (int)$identity['current_root_epoch'] >= 4294967295) {
            return false;
        }
        $blob = $identity['accepted_channels'];
        if (!is_string($blob) || strlen($blob) > 512 || strlen($blob) % 4 !== 0) {
            return false;
        }
        // Core persists sorted, unique, positive signed ChannelIds as big-endian u32s.
        $previous = 0;
        $member = false;
        for ($offset = 0; $offset < strlen($blob); $offset += 4) {
            $id = unpack('N', substr($blob, $offset, 4))[1];
            if ($id <= $previous || $id > 2147483647) {
                return false;
            }
            $member = $member || $id === $channel;
            $previous = $id;
        }
        return $member;
    }

    private function timestamp(?string $value): ?string {
        return $value === null ? null : (new \DateTimeImmutable($value, new \DateTimeZone('UTC')))->format(DATE_ATOM);
    }
}

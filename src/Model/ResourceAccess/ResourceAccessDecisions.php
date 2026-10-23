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
    public function __construct(private EntityManagerInterface $entityManager, private SuplaServer $server, private CloudResourceGrants $grants) {
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

    private function read(array $parameters, string $filter, callable $authorize, int $limit): array {
        $connection = $this->entityManager->getConnection();
        return $connection->transactional(function ($connection) use ($parameters, $filter, $authorize, $limit) {
            $select = 'd.*,p.user_id,p.destination_device_id,p.source_device_id,p.source_channel_id';
            foreach (['src' => 's', 'dst' => 't'] as $prefix => $alias) {
                foreach (['id', 'user_id', 'name', 'flags', 'protocol_version', 'is_virtual'] as $field) {
                    $select .= ",$alias.$field {$prefix}_$field";
                }
            }
            foreach (['id', 'user_id', 'iodevice_id', 'type', 'func', 'caption', 'properties', 'is_virtual'] as $field) {
                $select .= ",c.$field channel_$field";
            }
            $select .= ',i.current_root_epoch,i.accepted_channels,j.current_root_epoch dst_root_epoch,j.accepted_channels dst_channels';
            $rows = $connection->fetchAllAssociative('SELECT ' . $select . '
                FROM supla_resource_access_decision d JOIN supla_resource_access_pair p ON p.id=d.pair_id
                LEFT JOIN supla_iodevice s ON s.id=p.source_device_id AND s.user_id=p.user_id
                LEFT JOIN supla_iodevice t ON t.id=p.destination_device_id AND t.user_id=p.user_id
                LEFT JOIN supla_dev_channel c ON c.id=p.source_channel_id AND c.user_id=p.user_id '
                . 'LEFT JOIN supla_suplan_device_state i ON i.device_id=p.source_device_id AND i.user_id=p.user_id '
                . 'LEFT JOIN supla_suplan_device_state j ON j.device_id=p.destination_device_id AND j.user_id=p.user_id '
                . 'WHERE p.user_id=? AND d.status<>\'REMOVED\'' . $filter
                . ' ORDER BY d.id DESC LIMIT ' . ($limit + 1), $parameters);
            $more = count($rows) > $limit;
            $rows = array_slice($rows, 0, $limit);
            $items = [];
            if ($rows) {
                $pairIds = array_unique(array_column($rows, 'pair_id'));
                $placeholders = implode(',', array_fill(0, count($pairIds), '?'));
                $manual = $connection->fetchAllKeyValue('SELECT p.id,BIT_OR(g.permissions) FROM supla_resource_access_pair p
                    JOIN supla_resource_access_grant_valid g ON g.user_id=p.user_id AND g.destination_device_id=p.destination_device_id
                        AND g.resource_id=p.source_channel_id AND g.origin_type=2
                    WHERE p.id IN (' . $placeholders . ') GROUP BY p.id', array_values($pairIds));
                $independent = $connection->fetchAllKeyValue('SELECT p.id,BIT_OR(g.permissions) FROM supla_resource_access_pair p
                    JOIN supla_resource_access_grant_valid g ON g.user_id=p.user_id AND g.destination_device_id=p.destination_device_id
                        AND g.resource_id=p.source_channel_id AND g.origin_type<>2
                    WHERE p.id IN (' . $placeholders . ') GROUP BY p.id', array_values($pairIds));
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
                        'identity' => ['current_root_epoch' => $row['current_root_epoch'], 'accepted_channels' => $row['accepted_channels']],
                        'destinationIdentity' => ['current_root_epoch' => $row['dst_root_epoch'], 'accepted_channels' => $row['dst_channels']]];
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
                $connection->fetchOne('SELECT id FROM supla_user WHERE id=? FOR UPDATE', [$userId]);
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
                $this->grants->decision($id);
                return (int)$row['source_channel_id'];
            });
        } catch (\Doctrine\DBAL\Exception\RetryableException $e) {
            throw new ApiException('Access decision changed. Refresh and try again.', 409, $e);
        }
        $source = $this->entityManager->find(IODeviceChannel::class, $sourceId);
        if ($source && $source->belongsToUser($this->entityManager->find(\App\Entity\Main\User::class, $userId))) {
            // Existing M2 channel; Core rereads committed Cloud grants; this IPC is only a latency hint.
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
            $devices[$id] = $connection->fetchAssociative(
                'SELECT id,user_id,name,flags,protocol_version,is_virtual FROM supla_iodevice WHERE id=?' . $suffix,
                [$id]
            );
        }
        $source = $connection->fetchAssociative(
            'SELECT id,user_id,iodevice_id,type,func,caption,properties,is_virtual FROM supla_dev_channel WHERE id=?' . $suffix,
            [$row['source_channel_id']]
        );
        $identity = $destinationIdentity = false;
        // Same order as Core approval_context: pair, decision, sorted Devices, Source Channel, identity.
        foreach ($ids as $id) {
            $state = $connection->fetchAssociative('SELECT current_root_epoch,accepted_channels FROM supla_suplan_device_state
                WHERE device_id=? AND user_id=?' . $suffix, [$id, $row['user_id']]);
            if ($id === (int)$row['source_device_id']) {
                $identity = $state;
            }
            if ($id === (int)$row['destination_device_id']) {
                $destinationIdentity = $state;
            }
        }
        return ['source' => $source, 'devices' => $devices, 'identity' => $identity, 'destinationIdentity' => $destinationIdentity];
    }

    public function compatible(array $row, array $context): bool {
        $source = $context['source'];
        $permissions = (int)$row['permissions'];
        if (!$source || $permissions < 1 || $permissions > 7 || (int)$source['iodevice_id'] !== (int)$row['source_device_id']) {
            return false;
        }
        $mode = (int)($row['requested_delivery_mode'] ?? 1);
        $destination = $context['devices'][(int)$row['destination_device_id']] ?? false;
        $sourceDevice = $context['devices'][(int)$row['source_device_id']] ?? false;
        if (
            !$destination || !$sourceDevice || (int)$source['user_id'] !== (int)$row['user_id']
            || (int)$sourceDevice['user_id'] !== (int)$row['user_id'] || (int)$destination['user_id'] !== (int)$row['user_id']
            || (int)$row['source_device_id'] === (int)$row['destination_device_id']
            || (int)$destination['protocol_version'] < 29 || (bool)$destination['is_virtual']
            || !((int)$destination['flags'] & IoDeviceFlags::SUPLAN_SUPPORTED)
        ) {
            return false;
        }
        if ($mode === 2) {
            // STREAM needs Destination bootstrap, never Source peer identity/reachability.
            return $permissions === 1 && self::acceptedIdentity($context['destinationIdentity'], null)
                && ResourceAccessPermissions::streamReadSupported((int)$source['type'], (int)$source['func']);
        }
        if (
            $mode !== 1 || (bool)$sourceDevice['is_virtual'] || (bool)$source['is_virtual']
            || (int)$sourceDevice['protocol_version'] < 29 || !((int)$sourceDevice['flags'] & IoDeviceFlags::SUPLAN_SUPPORTED)
            || !self::acceptedIdentity($context['identity'], (int)$row['source_channel_id'])
        ) {
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
            'id' => (int)$row['id'], 'pairId' => (int)$row['pair_id'], 'revision' => (int)$row['revision'], 'status' => $row['status'],
            'requestedDeliveryMode' => (int)($row['requested_delivery_mode'] ?? 1),
            'sourceIsVirtual' => (bool)($source['is_virtual'] ?? false)
                || (bool)($context['devices'][(int)$row['source_device_id']]['is_virtual'] ?? false),
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

    private static function acceptedIdentity(array|false $identity, ?int $channel): bool {
        if (!$identity || (int)$identity['current_root_epoch'] < 1 || (int)$identity['current_root_epoch'] >= 4294967295) {
            return false;
        }
        $blob = $identity['accepted_channels'];
        if (!is_string($blob) || strlen($blob) > 512 || strlen($blob) % 4 !== 0) {
            return false;
        }
        // Core persists sorted, unique, positive signed ChannelIds as big-endian u32s.
        $previous = 0;
        $member = $channel === null;
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

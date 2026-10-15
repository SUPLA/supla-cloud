<?php
namespace App\Model\ResourceAccess;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction as CF;
use Doctrine\ORM\EntityManagerInterface;

/** Read-only view of Core's committed canonical ACL; never derives ACL from business decisions. */
class ServerResourcePermissions {
    public function __construct(private EntityManagerInterface $entityManager, private ResourceAccessDecisions $decisions) {
    }

    private function projection(): string {
        // peer_service.cpp encode_acl: ResourceType u8, ResourceId u32 big-endian, Permissions u8.
        // Core bounds the canonical ACL to 89 entries (534 bytes).
        return 'WITH RECURSIVE slots(n) AS (SELECT 0 UNION ALL SELECT n+1 FROM slots WHERE n<88), acl AS (
            SELECT a.id association_id,a.source_device_id,a.destination_device_id,slots.n slot,
                ORD(SUBSTRING(a.canonical_acl,slots.n*6+1,1)) resource_type,
                CAST(CONV(HEX(SUBSTRING(a.canonical_acl,slots.n*6+2,4)),16,10) AS UNSIGNED) resource_id,
                ORD(SUBSTRING(a.canonical_acl,slots.n*6+6,1)) permissions
            FROM supla_suplan_peer_association a JOIN slots ON slots.n*6<LENGTH(a.canonical_acl)
            WHERE a.user_id=:user AND a.lifecycle=1 AND a.source_removed=0 AND a.destination_removed=0
                AND LENGTH(a.canonical_acl)%6=0 AND LENGTH(a.canonical_acl)<=534
        ) ';
    }

    private function manualReadOrigin(): string {
        return "SELECT 1 FROM supla_resource_access_decision md JOIN supla_resource_access_pair mp ON mp.id=md.pair_id
            WHERE mp.user_id=:user AND mp.source_device_id=e.source_device_id AND mp.destination_device_id=e.destination_device_id
                AND mp.source_channel_id=e.resource_id AND e.resource_type=1 AND (e.permissions & 3)<>0 AND md.status='ACCEPTED' AND (md.permissions & 3)<>0
                AND EXISTS(SELECT 1 FROM supla_resource_access_decision demand
                    WHERE demand.pair_id=mp.id AND demand.requested_delivery_mode=2)";
    }

    private function resources(): string {
        return ' FROM acl e
            JOIN supla_iodevice s ON s.id=e.source_device_id AND s.user_id=:user
            JOIN supla_iodevice d ON d.id=e.destination_device_id AND d.user_id=:user
            LEFT JOIN supla_dev_channel c ON e.resource_type=1 AND c.id=e.resource_id
                AND c.user_id=:user AND c.iodevice_id=s.id
            WHERE e.resource_type IN (1,2) AND e.resource_id>0 AND e.permissions IN (1,2,4,5,6)
                AND ((e.resource_type=1 AND c.id IS NOT NULL AND c.func<>0)
                    OR (e.resource_type=2 AND e.resource_id=s.id)) ';
    }

    public function list(int $userId, array $filters, callable $authorize): array {
        return $this->entityManager->getConnection()->transactional(function ($connection) use ($userId, $filters, $authorize) {
            $authorizeRead = fn($row) => $authorize(['source_device_id' => $row['source_device_id'],
                'destination_device_id' => $row['destination_device_id'], 'resource_type' => 1, 'resource_id' => $row['source_channel_id']]);
            if (str_starts_with($filters['cursor'] ?? '', 'read-')) {
                $page = $this->decisions->readAuthorizations($userId, $filters, $authorizeRead, $this->projection());
                $page['items'] = array_map($this->presentRead(...), $page['items']);
                return $page;
            }
            $parameters = ['user' => $userId];
            $where = '';
            foreach (['sourceDeviceId' => 'source_device_id', 'destinationDeviceId' => 'destination_device_id'] as $key => $column) {
                if (isset($filters[$key])) {
                    $where .= " AND e.$column=:$key";
                    $parameters[$key] = $filters[$key];
                }
            }
            if (isset($filters['deviceId'])) {
                $where .= ' AND (e.source_device_id=:deviceId OR e.destination_device_id=:deviceId)';
                $parameters['deviceId'] = $filters['deviceId'];
            }
            if (isset($filters['permission'])) {
                $where .= $filters['permission'] === 1 ? ' AND (e.permissions & 3)<>0' : ' AND (e.permissions & :permission)<>0';
                if ($filters['permission'] !== 1) {
                    $parameters['permission'] = $filters['permission'];
                }
            }
            if (isset($filters['origin'])) {
                $where .= ' AND (EXISTS(SELECT 1 FROM supla_suplan_grant g WHERE g.association_id=e.association_id
                    AND g.resource_type=e.resource_type AND g.resource_id=e.resource_id AND '
                    . ($filters['origin'] === 'other' ? 'g.origin_type NOT IN (1,2))' : 'g.origin_type=:origin)')
                    . ($filters['origin'] === 'user-approved' ? ' OR EXISTS(' . $this->manualReadOrigin() . ')' : '') . ')';
                if ($filters['origin'] !== 'other') {
                    $parameters['origin'] = $filters['origin'] === 'configuration' ? 1 : 2;
                }
            }
            if (!empty($filters['cursor'])) {
                [$parameters['after'], $parameters['slot']] = explode(':', $filters['cursor']);
                $where .= ' AND (e.association_id>:after OR (e.association_id=:after AND e.slot>:slot))';
            }
            $limit = $filters['limit'] ?? 25;
            $rows = $connection->fetchAllAssociative($this->projection() . 'SELECT e.*,s.name source_name,d.name destination_name,
                c.caption,c.func,s.is_virtual source_is_virtual,c.is_virtual channel_is_virtual,
                (SELECT MAX(x.id) FROM supla_resource_access_decision x JOIN supla_resource_access_pair p ON p.id=x.pair_id
                    WHERE p.user_id=:user AND x.requested_delivery_mode=2) has_read,
                EXISTS(' . $this->manualReadOrigin() . ') has_manual_read,
                (SELECT COUNT(DISTINCT g.origin_type) FROM supla_suplan_grant g WHERE g.association_id=e.association_id
                    AND g.resource_type=e.resource_type AND g.resource_id=e.resource_id) origin_types,
                (SELECT MIN(g.origin_type) FROM supla_suplan_grant g WHERE g.association_id=e.association_id
                    AND g.resource_type=e.resource_type AND g.resource_id=e.resource_id) origin_type'
                . $this->resources() . $where . ' ORDER BY e.association_id,e.slot LIMIT ' . ($limit + 1), $parameters);
            $more = count($rows) > $limit;
            $rows = array_slice($rows, 0, $limit);
            $this->warmSubjects($rows);
            $items = [];
            foreach ($rows as $row) {
                if ($authorize($row)) {
                    $items[] = $this->present($row);
                }
            }
            $last = end($rows);
            if ($more) {
                return ['items' => $items, 'nextCursor' => $last['association_id'] . ':' . $last['slot']];
            }
            if ($rows && !$rows[0]['has_read']) {
                return ['items' => $items, 'nextCursor' => null];
            }
            $remaining = $limit - count($rows);
            $readFilters = array_replace($filters, ['cursor' => 'read-0:0', 'limit' => max(1, $remaining)]);
            $page = $this->decisions->readAuthorizations($userId, $readFilters, $authorizeRead, $this->projection());
            if (!$remaining) {
                return ['items' => $items, 'nextCursor' => $page['items'] || $page['nextCursor'] ? 'read-0:0' : null];
            }
            return ['items' => [...$items, ...array_map($this->presentRead(...), $page['items'])], 'nextCursor' => $page['nextCursor']];
        });
    }

    public function detail(int $userId, string $association, int $type, int $resource, callable $authorize, ?string $cursor = null): ?array {
        return $this->entityManager->getConnection()->transactional(function ($connection) use ($userId, $association, $type, $resource, $authorize, $cursor) {
            if (str_starts_with($association, 'read-')) {
                $entry = $this->decisions->readAuthorization(
                    $userId,
                    (int)substr($association, 5),
                    fn($row) => $authorize(['source_device_id' => $row['source_device_id'],
                    'destination_device_id' => $row['destination_device_id'],
                    'resource_type' => 1,
                    'resource_id' => $row['source_channel_id']])
                );
                if (!$entry || $type !== 1 || $entry['sourceChannelId'] !== $resource) {
                    return null;
                }
                return $this->presentRead($entry) + $this->decisions->readOrigins($userId, $entry['pairId'], $cursor);
            }
            $row = $connection->fetchAssociative(
                $this->projection() . 'SELECT e.*,s.name source_name,d.name destination_name,
                    c.caption,c.func,s.is_virtual source_is_virtual,c.is_virtual channel_is_virtual'
                . $this->resources() . ' AND e.association_id=:association AND e.resource_type=:type AND e.resource_id=:resource',
                ['user' => $userId, 'association' => $association, 'type' => $type, 'resource' => $resource]
            );
            if (!$row) {
                return null;
            }
            $this->warmSubjects([$row]);
            if (!$authorize($row)) {
                return null;
            }
            $parameters = [$association, $type, $resource, $userId, $row['source_device_id'], $row['destination_device_id'],
                $resource, $type === 1 && ((int)$row['permissions'] & 3) ? 1 : 0, $association, $type, $resource];
            $after = '';
            if ($cursor !== null) {
                [$originType, $originId] = explode(':', $cursor);
                $after = ' AND (g.origin_type>? OR (g.origin_type=? AND g.origin_id>?))';
                array_push($parameters, $originType, $originType, $originId);
            }
            $grants = $connection->fetchAllAssociative("SELECT g.* FROM (
                SELECT g.origin_type,g.origin_id,g.permissions FROM supla_suplan_grant g
                    WHERE g.association_id=? AND g.resource_type=? AND g.resource_id=?
                UNION ALL
                SELECT 2,d.id,1 FROM supla_resource_access_decision d JOIN supla_resource_access_pair p ON p.id=d.pair_id
                    WHERE p.user_id=? AND p.source_device_id=? AND p.destination_device_id=? AND p.source_channel_id=? AND ?=1
                        AND d.status='ACCEPTED' AND (d.permissions & 3)<>0
                        AND EXISTS(SELECT 1 FROM supla_resource_access_decision demand
                            WHERE demand.pair_id=p.id AND demand.requested_delivery_mode=2)
                        AND NOT EXISTS(SELECT 1 FROM supla_suplan_grant existing WHERE existing.association_id=?
                            AND existing.resource_type=? AND existing.resource_id=? AND existing.origin_type=2 AND existing.origin_id=d.id)
                ) g WHERE 1=1" . $after . ' ORDER BY g.origin_type,g.origin_id LIMIT 101', $parameters);
            $more = count($grants) > 100;
            $grants = array_slice($grants, 0, 100);
            $configIds = [];
            $manualIds = [];
            foreach ($grants as $grant) {
                if ((int)$grant['origin_type'] === 1 && (int)$grant['origin_id'] <= 549755813887) {
                    $configIds[] = (int)$grant['origin_id'] >> 8;
                } elseif ((int)$grant['origin_type'] === 2) {
                    $manualIds[] = $grant['origin_id'];
                }
            }
            $channels = [];
            if ($configIds) {
                $configChannels = $this->entityManager->getRepository(IODeviceChannel::class)->findBy(['id' => array_unique($configIds), 'user' => $userId]);
                $this->warmSubjects(array_map(fn($channel) => ['source_device_id' => $channel->getIoDevice()->getId(),
                    'destination_device_id' => $row['destination_device_id'], 'resource_type' => 1, 'resource_id' => $channel->getId()], $configChannels));
                foreach ($configChannels as $channel) {
                    $configRow = ['source_device_id' => $channel->getIoDevice()->getId(),
                        'destination_device_id' => $row['destination_device_id'], 'resource_type' => 1, 'resource_id' => $channel->getId()];
                    if ($authorize($configRow)) {
                        $channels[$channel->getId()] = ['id' => $channel->getId(), 'caption' => $channel->getCaption()];
                    }
                }
            }
            $decisions = [];
            if ($manualIds) {
                $placeholders = implode(',', array_fill(0, count($manualIds), '?'));
                foreach (
                    $connection->fetchFirstColumn(
                        'SELECT d.id FROM supla_resource_access_decision d
                    JOIN supla_resource_access_pair p ON p.id=d.pair_id WHERE p.user_id=? AND p.source_device_id=?
                    AND p.destination_device_id=? AND p.source_channel_id=? AND d.status=\'ACCEPTED\' AND d.id IN (' . $placeholders . ')',
                        [$userId, $row['source_device_id'], $row['destination_device_id'], $resource, ...$manualIds]
                    ) as $id
                ) {
                    $decisions[(string)$id] = true;
                }
            }
            $origins = [];
            foreach ($grants as $grant) {
                $originType = (int)$grant['origin_type'];
                $originId = (string)$grant['origin_id'];
                $config = $originType === 1 && (int)$originId <= 549755813887;
                $field = $config ? self::field((int)$originId & 255) : null;
                $channel = $config ? ($channels[(int)$originId >> 8] ?? null) : null;
                $origins[] = ['originType' => $originType, 'originId' => $originId, 'permissions' => (int)$grant['permissions'],
                    'kind' => $originType === 1 ? 'configuration' : ($originType === 2 ? 'user-approved' : 'other'),
                    'field' => $field, 'configuringChannel' => $field ? $channel : null,
                    'decisionId' => $originType === 2 && isset($decisions[$originId]) ? $originId : null];
            }
            $last = end($grants);
            return $this->present($row) + ['origins' => $origins,
                'nextOriginCursor' => $more ? $last['origin_type'] . ':' . $last['origin_id'] : null];
        });
    }

    public static function field(int $code): ?array {
        $fields = [1 => 'Main thermometer', 2 => 'Auxiliary thermometer', 3 => 'Binary sensor',
            4 => 'Master thermostat', 5 => 'Pump switch', 6 => 'Heat or cold source switch'];
        if (isset($fields[$code])) {
            return ['label' => $fields[$code], 'slot' => null];
        }
        if ($code >= 32 && $code < 42) {
            return ['label' => 'Container sensor', 'slot' => $code - 32];
        }
        if ($code >= 64 && $code < 84) {
            return ['label' => 'Valve flood sensor', 'slot' => $code - 64];
        }
        return null;
    }

    private function warmSubjects(array $rows): void {
        $devices = $channels = [];
        foreach ($rows as $row) {
            array_push($devices, $row['source_device_id'], $row['destination_device_id']);
            if ((int)$row['resource_type'] === 1) {
                $channels[] = $row['resource_id'];
            }
        }
        if ($devices) {
            $this->entityManager->getRepository(IODevice::class)->findBy(['id' => array_unique($devices)]);
        }
        if ($channels) {
            $this->entityManager->getRepository(IODeviceChannel::class)->findBy(['id' => array_unique($channels)]);
        }
    }

    private function presentRead(array $entry): array {
        return ['associationId' => null, 'resourceKey' => 'read-' . $entry['pairId'], 'authority' => 'SERVER',
            'authorizationKind' => 'SERVER_READ', 'sourceDeviceId' => $entry['sourceDeviceId'],
            'sourceDeviceName' => $entry['sourceDeviceName'], 'sourceIsVirtual' => $entry['sourceIsVirtual'],
            'destinationDeviceId' => $entry['destinationDeviceId'], 'destinationName' => $entry['destinationName'],
            'resourceType' => 1, 'resourceId' => $entry['sourceChannelId'], 'sourceCaption' => $entry['sourceCaption'],
            'sourceFunction' => $entry['sourceFunction'], 'permissions' => 1, 'originSummary' => 'user-approved'];
    }

    private function present(array $row): array {
        $originType = (int)($row['origin_type'] ?? 0);
        $manual = !empty($row['has_manual_read']);
        return ['associationId' => (string)$row['association_id'], 'authority' => 'SERVER',
            'sourceIsVirtual' => (bool)$row['source_is_virtual'] || (bool)$row['channel_is_virtual'],
            'sourceDeviceId' => (int)$row['source_device_id'], 'sourceDeviceName' => $row['source_name'],
            'destinationDeviceId' => (int)$row['destination_device_id'], 'destinationName' => $row['destination_name'],
            'resourceType' => (int)$row['resource_type'], 'resourceId' => (int)$row['resource_id'],
            'sourceCaption' => $row['caption'], 'sourceFunction' => isset($row['func']) && CF::isValid((int)$row['func'])
                ? CF::fromString((int)$row['func'])->getName() : null,
            'permissions' => (int)$row['permissions'],
            'originSummary' => (int)($row['origin_types'] ?? 0) > 1 || ($manual && $originType && $originType !== 2) ? 'mixed'
                : ($originType === 1 ? 'configuration' : ($originType === 2 || $manual ? 'user-approved' : 'other'))];
    }
}

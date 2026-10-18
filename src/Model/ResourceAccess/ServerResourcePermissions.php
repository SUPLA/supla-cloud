<?php
namespace App\Model\ResourceAccess;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction as CF;
use Doctrine\ORM\EntityManagerInterface;

/** Read-only account-scoped view of Cloud's authoritative logical grants. */
class ServerResourcePermissions {
    public function __construct(private EntityManagerInterface $entityManager) {
    }

    private function logicalSql(): string {
        return "SELECT e.*,s.name source_name,d.name destination_name,c.caption,c.func,
            s.is_virtual source_is_virtual,c.is_virtual channel_is_virtual,a.id association_id
            FROM (SELECT user_id,source_device_id,destination_device_id,resource_type,resource_id,
                BIT_OR(permissions) permissions,COUNT(DISTINCT origin_type) origin_types,MIN(origin_type) origin_type
                FROM supla_resource_access_grant_valid WHERE user_id=:user
                GROUP BY user_id,source_device_id,destination_device_id,resource_type,resource_id) e
            JOIN supla_iodevice s ON s.id=e.source_device_id AND s.user_id=e.user_id
            JOIN supla_iodevice d ON d.id=e.destination_device_id AND d.user_id=e.user_id
            JOIN supla_dev_channel c ON c.id=e.resource_id AND c.user_id=e.user_id
            LEFT JOIN supla_suplan_peer_association a ON a.user_id=e.user_id AND a.source_device_id=e.source_device_id
                AND a.destination_device_id=e.destination_device_id AND a.lifecycle=1
            WHERE 1=1 ";
    }

    private function logicalPresent(array $row): array {
        $item = $this->present($row);
        $item['associationId'] = $row['association_id'] === null ? null : (string)$row['association_id'];
        $item['resourceKey'] = 'logical-' . $row['destination_device_id'];
        $item['permissions'] = ResourceAccessDecisions::normalize((int)$row['permissions']);
        $item['authorizationKind'] = $item['associationId'] === null && $item['permissions'] === 1 ? 'SERVER_READ' : 'LOGICAL_GRANT';
        return $item;
    }

    public function list(int $user, array $filters, callable $authorize): array {
        $parameters = ['user' => $user];
        $where = '';
        foreach (['sourceDeviceId' => 'source_device_id', 'destinationDeviceId' => 'destination_device_id'] as $key => $column) {
            if (isset($filters[$key])) {
                $where .= " AND e.$column=:$key";
                $parameters[$key] = $filters[$key];
            }
        }
        if (isset($filters['deviceId'])) {
            $where .= ' AND (e.source_device_id=:device OR e.destination_device_id=:device)';
            $parameters['device'] = $filters['deviceId'];
        }
        if (isset($filters['permission'])) {
            $where .= ' AND (e.permissions & :permission)<>0';
            $parameters['permission'] = $filters['permission'] === 1 ? 3 : $filters['permission']; // CONTROL implies READ.
        }
        if (isset($filters['origin'])) {
            $where .= ' AND EXISTS(SELECT 1 FROM supla_resource_access_grant_valid g WHERE g.user_id=e.user_id
                AND g.destination_device_id=e.destination_device_id AND g.resource_id=e.resource_id AND g.origin_type=:origin)';
            $parameters['origin'] = ['configuration' => 1, 'user-approved' => 2, 'other' => 0][$filters['origin']];
        }
        if (str_starts_with($filters['cursor'] ?? '', 'logical-')) {
            [$destination, $resource] = explode(':', substr($filters['cursor'], 8));
            $where .= ' AND (e.destination_device_id>:after OR (e.destination_device_id=:after AND e.resource_id>:resource))';
            $parameters += ['after' => $destination, 'resource' => $resource];
        }
        $limit = $filters['limit'] ?? 25;
        $rows = $this->entityManager->getConnection()->fetchAllAssociative($this->logicalSql() . $where
            . ' ORDER BY e.destination_device_id,e.resource_id LIMIT ' . ($limit + 1), $parameters);
        $more = count($rows) > $limit;
        $rows = array_slice($rows, 0, $limit);
        $this->warmSubjects($rows);
        $items = [];
        foreach ($rows as $row) {
            if ($authorize($row)) {
                $items[] = $this->logicalPresent($row);
            }
        }
        $last = end($rows);
        return ['items' => $items, 'nextCursor' => $more ? 'logical-' . $last['destination_device_id'] . ':' . $last['resource_id'] : null];
    }

    public function detail(int $user, string $key, int $type, int $resource, callable $authorize, ?string $cursor): ?array {
        if ($type !== 1) {
            return null;
        }
        $connection = $this->entityManager->getConnection();
        if (!preg_match('/^logical-([1-9][0-9]*)$/D', $key, $match)) {
            return null;
        }
        $destination = (int)$match[1];
        if (!$destination) {
            return null;
        }
        $row = $connection->fetchAssociative(
            $this->logicalSql() . ' AND e.destination_device_id=:destination AND e.resource_id=:resource',
            ['user' => $user, 'destination' => $destination, 'resource' => $resource]
        );
        if (!$row || !$authorize($row)) {
            return null;
        }
        $parameters = [$user, $destination, $resource];
        $after = '';
        if ($cursor !== null) {
            [$originType, $originId] = explode(':', $cursor);
            $after = ' AND (origin_type>? OR (origin_type=? AND origin_id>?))';
            array_push($parameters, $originType, $originType, $originId);
        }
        $grants = $connection->fetchAllAssociative('SELECT * FROM supla_resource_access_grant_valid
            WHERE user_id=? AND destination_device_id=? AND resource_id=?' . $after . ' ORDER BY origin_type,origin_id LIMIT 101', $parameters);
        $more = count($grants) > 100;
        $grants = array_slice($grants, 0, 100);
        $origins = [];
        foreach ($grants as $grant) {
            $config = (int)$grant['origin_type'] === 1;
            $channel = $config ? $this->entityManager->find(IODeviceChannel::class, (int)$grant['origin_id'] >> 8) : null;
            $allowed = $channel && $channel->getUser()->getId() === $user && $authorize([
                'source_device_id' => $channel->getIoDevice()->getId(), 'destination_device_id' => $destination,
                'resource_type' => 1, 'resource_id' => $channel->getId()]);
            $origins[] = ['originType' => (int)$grant['origin_type'], 'originId' => (string)$grant['origin_id'],
                'permissions' => (int)$grant['permissions'], 'kind' => $config ? 'configuration' : 'user-approved',
                'field' => $config ? self::field((int)$grant['origin_id'] & 255) : null,
                'configuringChannel' => $allowed ? ['id' => $channel->getId(), 'caption' => $channel->getCaption()] : null,
                'decisionId' => $config ? null : (string)$grant['origin_id']];
        }
        $last = end($grants);
        return $this->logicalPresent($row) + ['origins' => $origins,
            'nextOriginCursor' => $more ? $last['origin_type'] . ':' . $last['origin_id'] : null];
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

    private function present(array $row): array {
        $originType = (int)($row['origin_type'] ?? 0);
        return ['associationId' => (string)$row['association_id'], 'authority' => 'SERVER',
            'sourceIsVirtual' => (bool)$row['source_is_virtual'] || (bool)$row['channel_is_virtual'],
            'sourceDeviceId' => (int)$row['source_device_id'], 'sourceDeviceName' => $row['source_name'],
            'destinationDeviceId' => (int)$row['destination_device_id'], 'destinationName' => $row['destination_name'],
            'resourceType' => (int)$row['resource_type'], 'resourceId' => (int)$row['resource_id'],
            'sourceCaption' => $row['caption'], 'sourceFunction' => isset($row['func']) && CF::isValid((int)$row['func'])
                ? CF::fromString((int)$row['func'])->getName() : null,
            'permissions' => (int)$row['permissions'],
            'originSummary' => (int)($row['origin_types'] ?? 0) > 1 ? 'mixed'
                : ($originType === 1 ? 'configuration' : ($originType === 2 ? 'user-approved' : 'other'))];
    }
}

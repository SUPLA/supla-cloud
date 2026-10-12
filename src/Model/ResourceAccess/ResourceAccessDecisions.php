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

    public function list(int $userId, callable $authorize, ?int $destination = null, ?int $device = null): array {
        $connection = $this->entityManager->getConnection();
        $parameters = [$userId];
        $filter = '';
        if ($destination !== null) {
            $filter .= 'AND p.destination_device_id=? ';
            $parameters[] = $destination;
        }
        if ($device !== null) {
            $filter .= 'AND (p.destination_device_id=? OR p.source_device_id=?) ';
            array_push($parameters, $device, $device);
        }
        $rows = $connection->fetchAllAssociative(
            'SELECT d.*, p.user_id, p.destination_device_id, p.source_channel_id, p.source_device_id '
            . 'FROM supla_resource_access_decision d JOIN supla_resource_access_pair p ON p.id=d.pair_id '
            . 'WHERE p.user_id=? AND d.status<>\'REMOVED\' '
            . $filter . 'ORDER BY d.id DESC',
            $parameters
        );
        $result = [];
        foreach ($rows as $row) {
            $context = $this->context($row, false);
            if (!$authorize($row, $context)) {
                continue;
            }
            $result[] = $this->view($row, $context);
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
        return ['source' => $source, 'devices' => $devices];
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
        $properties = json_decode($source['properties'] ?: '{}', true);
        return ResourceAccessPermissions::supported(
            (int)$source['type'],
            (int)$source['func'],
            $permissions,
            is_array($properties) ? $properties : []
        );
    }

    private function view(array $row, array $context): array {
        $source = $context['source'];
        if ($source && (int)$source['user_id'] !== (int)$row['user_id']) {
            $source = null;
        }
        foreach ($context['devices'] as &$device) {
            if ($device && (int)$device['user_id'] !== (int)$row['user_id']) {
                $device = null;
            }
        }
        unset($device);
        $connection = $this->entityManager->getConnection();
        $accepted = $connection->fetchFirstColumn(
            'SELECT permissions FROM supla_resource_access_decision WHERE pair_id=? AND status=\'ACCEPTED\'',
            [$row['pair_id']]
        );
        $manual = array_reduce($accepted, fn($mask, $permissions) => $mask | self::normalize((int)$permissions), 0);
        // Observe independent projection without editing its grants, ACL or keys.
        $independent = 0;
        // Doctrine's schema filter deliberately hides the Core-owned tables.
        if (
            (int)$connection->fetchOne("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE()
            AND table_name IN ('supla_suplan_grant','supla_suplan_peer_association')") === 2
        ) {
            $grants = $connection->fetchAllAssociative(
                'SELECT g.permissions,g.origin_type FROM supla_suplan_grant g JOIN supla_suplan_peer_association a ON a.id=g.association_id '
                . 'WHERE a.user_id=? AND a.source_device_id=? AND a.destination_device_id=? '
                . 'AND ((g.resource_type=1 AND g.resource_id=?) OR (g.resource_type=2 AND g.resource_id=?))',
                [$row['user_id'], $row['source_device_id'], $row['destination_device_id'], $row['source_channel_id'], $row['source_device_id']]
            );
            foreach ($grants as $grant) {
                if ((int)$grant['origin_type'] !== 2) {
                    $independent |= self::normalize((int)$grant['permissions']);
                }
            }
        }
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

    private function timestamp(?string $value): ?string {
        return $value === null ? null : (new \DateTimeImmutable($value, new \DateTimeZone('UTC')))->format(DATE_ATOM);
    }
}

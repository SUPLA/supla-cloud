<?php
namespace App\Model\ResourceAccess;

use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType as CT;
use App\Model\UserConfigTranslator\HvacChannelReferences;
use Doctrine\DBAL\Connection;

/** Cloud-only producer. Called inside the SAME transaction as ORM config/deletion or manual decision writes. */
class CloudResourceGrants {
    public function __construct(private Connection $connection) {
    }

    private function transaction(): void {
        if (!$this->connection->isTransactionActive()) {
            throw new \LogicException('Cloud grant changes require the business transaction.');
        }
    }

    private function channel(int $id): array|false {
        return $this->connection->fetchAssociative('SELECT c.*,d.flags device_flags,d.protocol_version,d.is_virtual device_virtual
            FROM supla_dev_channel c JOIN supla_iodevice d ON d.id=c.iodevice_id AND d.user_id=c.user_id WHERE c.id=? FOR UPDATE', [$id]);
    }

    private static function peer(array $source, array $destination): bool {
        foreach ([$source, $destination] as $channel) {
            if (
                !((int)$channel['device_flags'] & 524288) || $channel['device_virtual'] || $channel['is_virtual']
                || (int)$channel['protocol_version'] < 29
            ) {
                return false;
            }
        }
        return true;
    }

    private static function config(array $channel): array {
        return json_decode($channel['user_config'] ?: '{}', true) ?: [];
    }

    public static function slots(): array {
        $slots = [];
        foreach (HvacChannelReferences::FIELDS as $i => $name) {
            $slots[$i + 1] = $name . 'ChannelId';
        }
        for ($i = 0; $i < 10; $i++) {
            $slots[32 + $i] = $i;
        }
        for ($i = 0; $i < 20; $i++) {
            $slots[64 + $i] = $i;
        }
        return $slots;
    }

    private static function hvac(array $channel): bool {
        return (int)$channel['type'] === CT::HVAC && in_array(
            (int)$channel['func'],
            [CF::HVAC_THERMOSTAT, CF::HVAC_THERMOSTAT_HEAT_COOL, CF::HVAC_THERMOSTAT_DIFFERENTIAL, CF::HVAC_DOMESTIC_HOT_WATER],
            true
        );
    }

    private function configGrant(array $consumer, int $field, string|int $key): array {
        $config = self::config($consumer);
        $reference = $field < 32 ? ($config[$key] ?? null)
            : ($field < 64 ? ($config['sensors'][$key]['channelId'] ?? null) : ($config['floodSensorChannelIds'][$key] ?? null));
        if (
            !is_int($reference) || $reference < 1 || !($target = $this->channel($reference))
            || $target['user_id'] !== $consumer['user_id'] || $target['iodevice_id'] === $consumer['iodevice_id']
        ) {
            return [];
        }
        $type = (int)$target['type'];
        $function = (int)$target['func'];
        $binary = $type === CT::SENSORNO && in_array($function, array_map(fn(CF $fn) => $fn->getId(), CT::functions()[CT::SENSORNO]), true);
        if ($field <= 6) {
            $compatible = self::hvac($consumer) && match ($field) {
                1, 2 => in_array($type, [CT::THERMOMETER, CT::HUMIDITYANDTEMPSENSOR], true)
                    && in_array($function, [CF::THERMOMETER, CF::HUMIDITYANDTEMPERATURE], true),
                3 => $binary,
                4 => self::hvac($target) && empty(self::config($target)['masterThermostatChannelId'])
                    && !$this->connection->fetchOne("SELECT id FROM supla_dev_channel WHERE user_id=?
                        AND JSON_VALUE(user_config,'$.masterThermostatChannelId')=? LIMIT 1", [$consumer['user_id'], $consumer['id']]),
                5, 6 => $type === CT::RELAY && $function === ($field === 5 ? CF::PUMPSWITCH : CF::HEATORCOLDSOURCESWITCH),
            };
        } elseif ($field < 64) {
            $compatible = (int)$consumer['type'] === CT::CONTAINER
                && in_array((int)$consumer['func'], [CF::CONTAINER, CF::SEPTIC_TANK, CF::WATER_TANK], true) && $binary;
        } else {
            $compatible = in_array((int)$consumer['type'], [CT::VALVEOPENCLOSE, CT::VALVEPERCENTAGE], true)
                && in_array((int)$consumer['func'], [CF::VALVEOPENCLOSE, CF::VALVEPERCENTAGE], true) && $binary;
        }
        if (!$compatible || !self::peer($consumer, $target)) {
            return [];
        }
        // Source-first Pump/HOCS reads the HVAC resource on behalf of the referenced relay Device.
        [$source, $destination] = in_array($field, [5, 6], true) ? [$consumer, $target] : [$target, $consumer];
        return [$this->grant($source, (int)$destination['iodevice_id'], 1, true, true)
            + ['bindingTarget' => in_array($field, [5, 6], true) ? (int)$target['id'] : null]];
    }

    private function grant(array $source, int $destination, int $permissions, bool $peer, bool $requiresPeer): array {
        return ['source' => (int)$source['iodevice_id'], 'destination' => $destination, 'resource' => (int)$source['id'],
            'permissions' => $permissions, 'type' => (int)$source['type'], 'function' => (int)$source['func'],
            'peerEligible' => (int)$peer, 'requiresPeer' => (int)$requiresPeer];
    }

    public function replace(int $user, int $type, int|string $origin, array $grants): void {
        $this->transaction();
        // Consume every result set: CALL must not leave a pending PDO result on this transaction's connection.
        $result = $this->connection->executeQuery(
            'CALL supla_resource_access_replace(?,?,?,?)',
            [$user, $type, $origin, json_encode($grants, JSON_THROW_ON_ERROR)]
        );
        $result->free();
    }

    private function existingTarget(int $user, int|string $origin, array $desired): array {
        $rows = $this->connection->fetchAllAssociative('SELECT source_device_id,destination_device_id,resource_id,binding_target_channel_id
            FROM supla_resource_access_grant WHERE user_id=? AND origin_type=1 AND origin_id=? FOR UPDATE', [$user, $origin]);
        return array_values(array_filter($desired, function ($grant) use ($rows) {
            foreach ($rows as $row) {
                if (
                    (int)$row['source_device_id'] === $grant['source'] && (int)$row['destination_device_id'] === $grant['destination']
                    && (int)$row['resource_id'] === $grant['resource']
                    && ($row['binding_target_channel_id'] === null ? null : (int)$row['binding_target_channel_id']) === ($grant['bindingTarget'] ?? null)
                ) {
                    return true;
                }
            }
            return false;
        }));
    }

    public function configuration(int $id, ?array $previous = null): void {
        $this->transaction();
        if (!($consumer = $this->channel($id))) {
            return;
        }
        $this->connection->fetchOne('SELECT id FROM supla_user WHERE id=? FOR UPDATE', [$consumer['user_id']]);
        $existing = $this->connection->fetchFirstColumn('SELECT origin_id FROM supla_resource_access_grant
            WHERE user_id=? AND origin_type=1 AND (origin_id >> 8)=?', [$consumer['user_id'], $id]);
        foreach (self::slots() as $field => $key) {
            $current = self::config($consumer);
            $oldReference = $field < 32 ? ($previous[$key] ?? null)
                : ($field < 64 ? ($previous['sensors'][$key]['channelId'] ?? null) : ($previous['floodSensorChannelIds'][$key] ?? null));
            $newReference = $field < 32 ? ($current[$key] ?? null)
                : ($field < 64 ? ($current['sensors'][$key]['channelId'] ?? null) : ($current['floodSensorChannelIds'][$key] ?? null));
            if ($previous !== null && $oldReference === $newReference && !in_array(($id << 8) | $field, $existing)) {
                continue; // An unrelated Cloud edit cannot resurrect a previously invalidated origin.
            }
            $grants = $this->configGrant($consumer, $field, $key);
            if ($previous !== null && $oldReference === $newReference) {
                $grants = $this->existingTarget((int)$consumer['user_id'], ($id << 8) | $field, $grants);
            }
            if (!$grants && !in_array(($id << 8) | $field, $existing)) {
                continue;
            }
            $this->replace((int)$consumer['user_id'], 1, ($id << 8) | $field, $grants);
        }
    }

    public function decision(int $id): void {
        $this->transaction();
        $row = $this->connection->fetchAssociative('SELECT d.*,p.user_id,p.source_channel_id,p.source_device_id,p.destination_device_id
            FROM supla_resource_access_decision d JOIN supla_resource_access_pair p ON p.id=d.pair_id WHERE d.id=? FOR UPDATE', [$id]);
        if (!$row) {
            return;
        }
        $grants = [];
        $source = $this->channel((int)$row['source_channel_id']);
        $destination = $this->connection->fetchAssociative(
            'SELECT *,flags device_flags,is_virtual device_virtual FROM supla_iodevice WHERE id=? FOR UPDATE',
            [$row['destination_device_id']]
        );
        if (
            $row['status'] === 'ACCEPTED' && $source && $destination && $source['user_id'] === $row['user_id']
            && $destination['user_id'] === $row['user_id'] && $source['iodevice_id'] === $row['source_device_id']
            && $source['iodevice_id'] !== $row['destination_device_id'] && !$destination['is_virtual']
            && ((int)$destination['flags'] & 524288) && (int)$destination['protocol_version'] >= 29
        ) {
            $permissions = (int)$row['permissions'];
            $properties = json_decode($source['properties'] ?: '{}', true) ?: [];
            // READ consent is mode-neutral. Unsupported CONTROL/ACTION cannot survive a Source function change.
            $readOnly = $permissions === 1;
            $supported = $readOnly ? ResourceAccessPermissions::streamReadSupported((int)$source['type'], (int)$source['func'])
                : ResourceAccessPermissions::supported((int)$source['type'], (int)$source['func'], $permissions, $properties);
            if ($supported) {
                $peer = self::peer($source, $destination + ['is_virtual' => 0])
                    && ResourceAccessPermissions::supported((int)$source['type'], (int)$source['func'], $permissions, $properties);
                $requiresPeer = $peer && (int)$row['requested_delivery_mode'] === 1;
                $grants[] = $this->grant($source, (int)$row['destination_device_id'], $permissions, $peer, $requiresPeer);
            }
        }
        $this->replace((int)$row['user_id'], 2, $id, $grants);
    }

    /** Revalidate only existing origins: a capability/function change cannot resurrect a removed grant. */
    public function metadata(int $user, int $device, ?int $channel = null): void {
        $this->transaction();
        $this->connection->fetchOne('SELECT id FROM supla_user WHERE id=? FOR UPDATE', [$user]);
        $origins = $this->connection->fetchAllAssociative('SELECT DISTINCT origin_type,origin_id FROM supla_resource_access_grant
            WHERE user_id=? AND (source_device_id=? OR destination_device_id=? OR (origin_type=1 AND (origin_id >> 8)=?))
            ORDER BY origin_type,origin_id FOR UPDATE', [$user, $device, $device, $channel ?? 0]);
        foreach ($origins as $origin) {
            if ((int)$origin['origin_type'] === 2) {
                $this->decision((int)$origin['origin_id']);
            } else {
                $id = (int)$origin['origin_id'] >> 8;
                $field = (int)$origin['origin_id'] & 255;
                $consumer = $this->channel($id);
                $desired = $consumer ? $this->configGrant($consumer, $field, self::slots()[$field]) : [];
                $this->replace($user, 1, $origin['origin_id'], $this->existingTarget($user, $origin['origin_id'], $desired));
            }
        }
    }

    public function deletedChannel(int $user, int $device, int $channel): void {
        $this->transaction();
        $origins = $this->connection->fetchFirstColumn('SELECT DISTINCT origin_id FROM supla_resource_access_grant
            WHERE user_id=? AND origin_type=1 AND (origin_id >> 8)=? ORDER BY origin_id', [$user, $channel]);
        foreach ($origins as $origin) {
            $this->replace($user, 1, $origin, []);
        }
        $this->connection->executeQuery('CALL supla_suplan_resource_cleanup(?,?)', [$user, $channel])->free();
    }

    public function projectDevice(int $user, int $device): void {
        foreach (
            $this->connection->fetchAllAssociative(
                'SELECT source_device_id,destination_device_id FROM supla_suplan_peer_association
            WHERE user_id=? AND (source_device_id=? OR destination_device_id=?) ORDER BY source_device_id,destination_device_id',
                [$user, $device, $device]
            ) as $pair
        ) {
            $result = $this->connection->executeQuery(
                'CALL supla_suplan_project(?,?,?,FALSE)',
                [$user, $pair['source_device_id'], $pair['destination_device_id']]
            );
            $result->free();
        }
    }
}

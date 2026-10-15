<?php

namespace SuplaBundle\Migrations\Migration;

use App\Migrations\NoWayBackMigration;
use stdClass;

/** Historical Container/Valve canonicalization; requires coordinated Core DB_VERSION. */
class Version20261008160000 extends NoWayBackMigration {
    public function migrate() {
        $lastId = 0;
        do {
            $rows = $this->getConnection()->fetchAllAssociative(
                'SELECT id, iodevice_id, user_id, func, user_config, properties FROM supla_dev_channel '
                . 'WHERE id > ? AND func IN (500,980,981,982) ORDER BY id LIMIT 100',
                [$lastId]
            );
            foreach ($rows as $row) {
                $lastId = (int)$row['id'];
                $changes = [];
                $config = json_decode($row['user_config'] ?: '{}');
                if ($config instanceof stdClass) {
                    $before = json_encode($config, JSON_PRESERVE_ZERO_FRACTION);
                    if ((int)$row['func'] === 500 && property_exists($config, 'sensorChannelNumbers')) {
                        if (!property_exists($config, 'floodSensorChannelIds')) {
                            if (is_array($config->sensorChannelNumbers)) {
                                $config->floodSensorChannelIds = array_map(
                                    fn($number) => $this->resolve($row, $number),
                                    $config->sensorChannelNumbers
                                );
                            } else {
                                $this->log('Malformed Valve sensor array left unset.', ['channelId' => $lastId]);
                                $config->floodSensorChannelIds = [];
                            }
                        }
                        unset($config->sensorChannelNumbers);
                    } elseif ((int)$row['func'] !== 500 && isset($config->sensors) && is_array($config->sensors)) {
                        foreach ($config->sensors as $index => $sensor) {
                            if ($sensor instanceof stdClass) {
                                if (!property_exists($sensor, 'channelId')) {
                                    $sensor->channelId = $this->resolve($row, $sensor->channelNo ?? null);
                                }
                                unset($sensor->channelNo);
                            } elseif ($sensor !== null) {
                                // Preserve the slot and its diagnostic payload; never coerce it to number zero.
                                $this->log('Malformed Container sensor slot preserved.', ['channelId' => $lastId, 'slot' => $index]);
                            }
                        }
                    }
                    $after = json_encode($config, JSON_PRESERVE_ZERO_FRACTION);
                    if ($before !== $after) {
                        $changes['user_config'] = $after;
                    }
                } else {
                    $this->log('Malformed Container/Valve user_config preserved.', ['channelId' => $lastId]);
                }
                $properties = json_decode($row['properties'] ?: '{}');
                if ($properties instanceof stdClass) {
                    $before = json_encode($properties, JSON_PRESERVE_ZERO_FRACTION);
                    foreach (['hiddenConfigFields', 'readOnlyConfigFields'] as $key) {
                        if (isset($properties->$key) && is_array($properties->$key)) {
                            foreach ($properties->$key as &$name) {
                                if ((int)$row['func'] === 500 && $name === 'sensorChannelNumbers') {
                                    $name = 'floodSensorChannelIds';
                                } elseif ((int)$row['func'] !== 500 && $name === 'sensors.channelNo') {
                                    $name = 'sensors.channelId';
                                }
                            }
                            unset($name);
                        }
                    }
                    $after = json_encode($properties, JSON_PRESERVE_ZERO_FRACTION);
                    if ($before !== $after) {
                        $changes['properties'] = $after;
                    }
                }
                if ($changes) {
                    $assignments = array_map(fn($key) => $key . '=:' . $key, array_keys($changes));
                    $this->addSql(
                        'UPDATE supla_dev_channel SET ' . implode(',', $assignments) . ' WHERE id=:id',
                        array_merge($changes, ['id' => $lastId])
                    );
                }
            }
        } while (count($rows) === 100);
    }

    private function resolve(array $row, $number): ?int {
        if ($number === null) {
            return null;
        }
        if (is_int($number) && $number >= 0 && $number < 128) {
            $matches = $this->getConnection()->fetchFirstColumn(
                'SELECT id FROM supla_dev_channel WHERE iodevice_id=? AND user_id=? AND channel_number=?',
                [$row['iodevice_id'], $row['user_id'], $number]
            );
            if (count($matches) === 1) {
                return (int)$matches[0];
            }
        }
        $this->log('Stale or invalid Container/Valve reference cleared.', ['channelId' => (int)$row['id'], 'channelNo' => $number]);
        return null;
    }
}

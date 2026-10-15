<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Migrations\Migration;

use App\Migrations\NoWayBackMigration;
use stdClass;

/** Canonicalize historical HVAC references before starting the canonical Core. */
class Version20261008120000 extends NoWayBackMigration {
    private const FIELDS = [
        'mainThermometer', 'auxThermometer', 'binarySensor',
        'masterThermostat', 'pumpSwitch', 'heatOrColdSourceSwitch',
    ];

    public function migrate() {
        $lastId = 0;
        do {
            $channels = $this->getConnection()->fetchAllAssociative(
                'SELECT id, iodevice_id, user_id, user_config, properties FROM supla_dev_channel '
                . 'WHERE id > ? AND func IN (420,422,425,426) ORDER BY id LIMIT 100',
                [$lastId]
            );
            foreach ($channels as $channel) {
                $lastId = (int)$channel['id'];
                $changes = [];
                // Objects preserve nested JSON objects (including empty objects) and unrelated keys.
                $config = json_decode($channel['user_config'] ?: '{}');
                if ($config instanceof stdClass) {
                    $original = json_encode($config, JSON_PRESERVE_ZERO_FRACTION);
                    foreach (self::FIELDS as $field) {
                        $legacy = $field . 'ChannelNo';
                        $canonical = $field . 'ChannelId';
                        if (!property_exists($config, $legacy)) {
                            continue;
                        }
                        if (!property_exists($config, $canonical)) {
                            $number = $config->$legacy;
                            $id = null;
                            // JSON null and negative sentinels are unset; zero is a valid channel number.
                            if (is_int($number) && $number >= 0) {
                                $found = $this->getConnection()->fetchOne(
                                    'SELECT id FROM supla_dev_channel WHERE iodevice_id=? AND user_id=? AND channel_number=?',
                                    [$channel['iodevice_id'], $channel['user_id'], $number]
                                );
                                if ($found !== false && ($field !== 'mainThermometer' || (int)$found !== $lastId)) {
                                    $id = (int)$found;
                                }
                            }
                            $config->$canonical = $id;
                        }
                        unset($config->$legacy);
                    }
                    $json = json_encode($config, JSON_PRESERVE_ZERO_FRACTION);
                    if ($json !== $original) {
                        $changes['user_config'] = $json;
                    }
                } else {
                    $this->log('Skipping malformed HVAC user_config.', ['channelId' => $lastId]);
                }
                $properties = json_decode($channel['properties'] ?: '{}');
                if ($properties instanceof stdClass) {
                    $original = json_encode($properties, JSON_PRESERVE_ZERO_FRACTION);
                    foreach (['hiddenConfigFields', 'readOnlyConfigFields'] as $key) {
                        if (!isset($properties->$key) || !is_array($properties->$key)) {
                            continue;
                        }
                        foreach ($properties->$key as &$name) {
                            foreach (self::FIELDS as $field) {
                                if ($name === $field . 'ChannelNo') {
                                    $name = $field . 'ChannelId';
                                    break;
                                }
                            }
                        }
                        unset($name);
                    }
                    $json = json_encode($properties, JSON_PRESERVE_ZERO_FRACTION);
                    if ($json !== $original) {
                        $changes['properties'] = $json;
                    }
                } else {
                    $this->log('Skipping malformed HVAC properties.', ['channelId' => $lastId]);
                }
                if ($changes) {
                    $assignments = array_map(fn($column) => $column . '=:' . $column, array_keys($changes));
                    $this->addSql(
                        'UPDATE supla_dev_channel SET ' . implode(',', $assignments) . ' WHERE id=:id',
                        array_merge($changes, ['id' => $lastId])
                    );
                }
            }
        } while (count($channels) === 100);
    }
}

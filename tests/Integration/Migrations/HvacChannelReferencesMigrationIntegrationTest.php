<?php

namespace App\Tests\Integration\Migrations;

use App\Entity\Main\IODevice;
use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType;
use App\Tests\Integration\Traits\UserFixtures;

class HvacChannelReferencesMigrationIntegrationTest extends DatabaseMigrationTestCase {
    use UserFixtures;

    private const VERSION = 'SuplaBundle\\Migrations\\Migration\\Version20261008120000';
    private const FIELDS = [
        'mainThermometer', 'auxThermometer', 'binarySensor',
        'masterThermostat', 'pumpSwitch', 'heatOrColdSourceSwitch',
    ];

    private function endpoints(): array {
        $this->executeCommand(['command' => 'doctrine:migrations:migrate',
            'version' => 'SuplaBundle\\Migrations\\Migration\\Version20261007104248']);
        $this->executeCommand('supla:initialize:create-sql-procedures-and-views');
        $this->executeCommand('supla:initialize:create-webapp-client');
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $channels = array_fill(0, 6, [ChannelType::THERMOMETERDS18B20, CF::THERMOMETER]);
        $channels[] = [ChannelType::HVAC, CF::HVAC_THERMOSTAT];
        $first = $this->createDevice($location, $channels);
        $second = $this->createDevice($this->createLocation($user), $channels);
        $first->getChannels()[0]->setLocation($second->getLocation());
        $this->flush();
        return [$first, $second];
    }

    private function store(IODevice $device, string $config, ?string $properties = null): int {
        $id = $device->getChannels()[6]->getId();
        $this->getEntityManager()->getConnection()->update('supla_dev_channel', [
            'user_config' => $config, 'properties' => $properties,
        ], ['id' => $id]);
        return $id;
    }

    private function row(int $id): array {
        return $this->getEntityManager()->getConnection()->fetchAssociative(
            'SELECT user_config, properties FROM supla_dev_channel WHERE id=?',
            [$id]
        );
    }

    private function migrateReferences(): void {
        $output = $this->executeCommand(['command' => 'doctrine:migrations:migrate', 'version' => self::VERSION]);
        $this->assertStringContainsString('Successfully migrated to version:', $output);
    }

    public function testAllSixReferencesUseOwnDeviceRegardlessOfLocationAndPreserveJsonAndMetadata() {
        [$first, $second] = $this->endpoints();
        $config = ['unrelated' => (object)['empty' => new \stdClass(), 'array' => [], 'decimal' => 1.0], 'subfunction' => 'HEAT'];
        $properties = ['hiddenConfigFields' => ['pumpSwitchChannelNo', 'otherChannelNo'],
            'readOnlyConfigFields' => [], 'unrelated' => new \stdClass()];
        foreach (self::FIELDS as $number => $field) {
            $config[$field . 'ChannelNo'] = $number;
            $properties['readOnlyConfigFields'][] = $field . 'ChannelNo';
        }
        foreach ([$first, $second] as $device) {
            $this->store($device, json_encode($config, JSON_PRESERVE_ZERO_FRACTION), json_encode($properties));
        }
        $this->migrateReferences();
        foreach ([$first, $second] as $device) {
            $row = $this->row($device->getChannels()[6]->getId());
            $actual = json_decode($row['user_config']);
            foreach (self::FIELDS as $number => $field) {
                $this->assertSame($device->getChannels()[$number]->getId(), $actual->{$field . 'ChannelId'});
                $this->assertFalse(property_exists($actual, $field . 'ChannelNo'));
            }
            $this->assertEquals($config['unrelated'], $actual->unrelated);
            $this->assertSame('HEAT', $actual->subfunction);
            $metadata = json_decode($row['properties'], true);
            $this->assertSame(['pumpSwitchChannelId', 'otherChannelNo'], $metadata['hiddenConfigFields']);
            $this->assertSame(array_map(fn($field) => $field . 'ChannelId', self::FIELDS), $metadata['readOnlyConfigFields']);
            $this->assertInstanceOf(\stdClass::class, json_decode($row['properties'])->unrelated);
        }
        $before = $this->row($first->getChannels()[6]->getId());
        // Reset only this test database's version marker, preserving the canonical data.
        // Doctrine otherwise rejects explicit re-execution at its unique history key.
        $this->getEntityManager()->getConnection()->delete('migration_versions', ['version' => self::VERSION]);
        // Repeat the actual version's SQL transformation, not just a helper.
        $this->executeCommand(['command' => 'doctrine:migrations:execute', 'versions' => [self::VERSION], '--up' => true]);
        $this->assertSame($before, $this->row($first->getChannels()[6]->getId()));
    }

    public function testCanonicalValuesIncludingNullWinAndInvalidReferencesBecomeUnset() {
        [$first, $second] = $this->endpoints();
        // Number 100 exists only on the other device: it must never be borrowed.
        $this->getEntityManager()->getConnection()->update(
            'supla_dev_channel',
            ['channel_number' => 100],
            ['id' => $second->getChannels()[0]->getId()]
        );
        $id = $this->store($first, json_encode([
            'mainThermometerChannelId' => $second->getChannels()[1]->getId(), 'mainThermometerChannelNo' => 0,
            'auxThermometerChannelId' => null, 'auxThermometerChannelNo' => 1,
            'binarySensorChannelNo' => 100, 'masterThermostatChannelNo' => 999,
            'pumpSwitchChannelNo' => -1, 'heatOrColdSourceSwitchChannelNo' => null, 'unrelated' => false,
        ]));
        $this->migrateReferences();
        $this->assertSame([
            'mainThermometerChannelId' => $second->getChannels()[1]->getId(), 'auxThermometerChannelId' => null,
            'unrelated' => false, 'binarySensorChannelId' => null, 'masterThermostatChannelId' => null,
            'pumpSwitchChannelId' => null, 'heatOrColdSourceSwitchChannelId' => null,
        ], json_decode($this->row($id)['user_config'], true));
    }

    public function testAbsentReferencesAndCanonicalOnlyRowsStayByteIdenticalAndMetadataAloneMigrates() {
        [$first, $second] = $this->endpoints();
        $empty = $this->store($first, '{ "other": {} }', '{"hiddenConfigFields":["mainThermometerChannelNo"]}');
        $canonical = $this->store($second, '{ "mainThermometerChannelId": null, "other": [] }');
        $before = $this->row($canonical);
        $this->migrateReferences();
        $this->assertSame('{ "other": {} }', $this->row($empty)['user_config']);
        $this->assertSame(['mainThermometerChannelId'], json_decode($this->row($empty)['properties'], true)['hiddenConfigFields']);
        $this->assertSame($before, $this->row($canonical));
    }

    public function testNullAndSentinelsDoNotSelectChannelZeroButExplicitZeroDoes() {
        [$first, $second] = $this->endpoints();
        $id = $this->store($first, '{"mainThermometerChannelNo":6,"auxThermometerChannelNo":null,"binarySensorChannelNo":-1,'
            . '"masterThermostatChannelNo":0,"pumpSwitchChannelNo":255,"heatOrColdSourceSwitchChannelNo":"0"}');
        $this->migrateReferences();
        $config = json_decode($this->row($id)['user_config'], true);
        foreach (['mainThermometer', 'auxThermometer', 'binarySensor', 'pumpSwitch', 'heatOrColdSourceSwitch'] as $field) {
            $this->assertNull($config[$field . 'ChannelId']);
        }
        $this->assertSame($first->getChannels()[0]->getId(), $config['masterThermostatChannelId']);
    }

    public function testBatchBoundaryAndAllHvacFunctionsAndNonHvacExclusion() {
        [$first, $second] = $this->endpoints();
        $user = $first->getUser();
        $types = [];
        for ($i = 0; $i < 103; $i++) {
            $types[] = [ChannelType::HVAC, [CF::HVAC_THERMOSTAT, CF::HVAC_THERMOSTAT_HEAT_COOL,
                CF::HVAC_THERMOSTAT_DIFFERENTIAL, CF::HVAC_DOMESTIC_HOT_WATER][$i % 4]];
        }
        $device = $this->createDevice($this->createLocation($user), $types);
        foreach ($device->getChannels() as $channel) {
            $this->getEntityManager()->getConnection()->update(
                'supla_dev_channel',
                ['user_config' => '{"auxThermometerChannelNo":0}'],
                ['id' => $channel->getId()]
            );
        }
        $nonHvac = $first->getChannels()[0]->getId();
        $this->getEntityManager()->getConnection()->update(
            'supla_dev_channel',
            ['user_config' => '{"mainThermometerChannelNo":1}'],
            ['id' => $nonHvac]
        );
        $this->migrateReferences();
        foreach ($device->getChannels() as $channel) {
            $this->assertSame(
                ['auxThermometerChannelId' => $device->getChannels()[0]->getId()],
                json_decode($this->row($channel->getId())['user_config'], true)
            );
        }
        $this->assertSame('{"mainThermometerChannelNo":1}', $this->row($nonHvac)['user_config']);
    }

    public function testMalformedJsonIsPreservedInsteadOfDiscardingUnrelatedData() {
        [$first, $second] = $this->endpoints();
        $invalidConfig = $this->store($first, '{broken', '{"hiddenConfigFields":["binarySensorChannelNo"]}');
        $invalidProperties = $this->store($second, '{"mainThermometerChannelNo":0}', '{broken');
        $this->migrateReferences();
        $this->assertSame('{broken', $this->row($invalidConfig)['user_config']);
        $this->assertSame(['binarySensorChannelId'], json_decode($this->row($invalidConfig)['properties'], true)['hiddenConfigFields']);
        $this->assertSame('{broken', $this->row($invalidProperties)['properties']);
        $this->assertSame(
            ['mainThermometerChannelId' => $second->getChannels()[0]->getId()],
            json_decode($this->row($invalidProperties)['user_config'], true)
        );
    }
}

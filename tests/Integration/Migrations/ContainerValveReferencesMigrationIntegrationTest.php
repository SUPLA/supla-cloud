<?php

namespace App\Tests\Integration\Migrations;

use App\Entity\Main\IODevice;
use App\Entity\EntityUtils;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFlags;
use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType;
use App\Model\Dependencies\ChannelDependencies;
use App\Model\UserConfigTranslator\SubjectConfigTranslator;
use App\Tests\Integration\Traits\UserFixtures;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;

class ContainerValveReferencesMigrationIntegrationTest extends DatabaseMigrationTestCase {
    use UserFixtures;
    use ResponseAssertions;
    use SuplaApiHelper;

    private const VERSION = 'SuplaBundle\\Migrations\\Migration\\Version20261008160000';

    private function devices(): array {
        $this->executeCommand(['command' => 'doctrine:migrations:migrate',
            'version' => 'SuplaBundle\\Migrations\\Migration\\Version20261008120000']);
        $this->executeCommand('supla:initialize:create-sql-procedures-and-views');
        $this->executeCommand('supla:initialize:create-webapp-client');
        $user = $this->createConfirmedUser();
        $types = array_fill(0, 20, [ChannelType::SENSORNO, CF::FLOOD_SENSOR]);
        $types[] = [ChannelType::CONTAINER, CF::CONTAINER];
        $types[] = [ChannelType::VALVEOPENCLOSE, CF::VALVEOPENCLOSE];
        $devices = [$this->createDevice($this->createLocation($user), $types),
            $this->createDevice($this->createLocation($user), $types)];
        foreach ($devices as $device) {
            EntityUtils::setField($device->getChannels()[21], 'flags', ChannelFlags::FLOOD_SENSORS_SUPPORTED);
        }
        $devices[0]->getChannels()[0]->setLocation($devices[1]->getLocation());
        $this->flush();
        return $devices;
    }

    private function store(IODevice $device, int $index, $config, ?string $properties = null): int {
        $id = $device->getChannels()[$index]->getId();
        $this->getEntityManager()->getConnection()->update('supla_dev_channel', [
            'user_config' => is_string($config) ? $config : json_encode($config, JSON_PRESERVE_ZERO_FRACTION),
            'properties' => $properties,
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

    public function testTenAndTwentySlotsPerDevicePreserveOrderSettingsMetadataAndActualIdempotency() {
        $devices = $this->devices();
        $settings = ['warningAboveLevel' => 80, 'alarmAboveLevel' => 90, 'warningBelowLevel' => 20,
            'alarmBelowLevel' => 10, 'muteAlarmSoundWithoutAdditionalAuth' => true,
            'extra' => (object)['empty' => new \stdClass(), 'fraction' => 1.0]];
        $numbers = [8, 0, 7, 2, 9, 1, 6, 3, 5, 4];
        foreach ($devices as $index => $device) {
            EntityUtils::setField($device->getChannels()[20], 'function', $index ? CF::SEPTIC_TANK : CF::WATER_TANK);
            $this->flush();
            $this->store($device, 20, $settings + ['sensors' => array_map(
                fn($number, $index) => ['channelNo' => $number, 'fillLevel' => ($index + 1) * 10, 'extra' => 'slot-' . $index],
                $numbers,
                range(0, 9)
            )], '{"hiddenConfigFields":["sensors.channelNo","other"],"readOnlyConfigFields":["sensors.channelNo"],"extra":{}}');
            $this->store(
                $device,
                21,
                ['sensorChannelNumbers' => range(19, 0), 'closeValveOnFloodType' => 'ON_CHANGE', 'extra' => false],
                '{"hiddenConfigFields":["sensorChannelNumbers"],"readOnlyConfigFields":["sensorChannelNumbers","other"]}'
            );
        }
        $this->migrateReferences();
        $before = [];
        foreach ($devices as $device) {
            $tank = json_decode($this->row($device->getChannels()[20]->getId())['user_config'], true);
            $this->assertCount(10, $tank['sensors']);
            foreach ($numbers as $index => $number) {
                $this->assertSame(['fillLevel' => ($index + 1) * 10, 'extra' => 'slot-' . $index,
                    'channelId' => $device->getChannels()[$number]->getId()], $tank['sensors'][$index]);
            }
            foreach ($settings as $key => $value) {
                $this->assertEquals($value, json_decode($this->row($device->getChannels()[20]->getId())['user_config'])->$key);
            }
            $valve = json_decode($this->row($device->getChannels()[21]->getId())['user_config'], true);
            $this->assertSame(array_map(fn($number) => $device->getChannels()[$number]->getId(), range(19, 0)), $valve['floodSensorChannelIds']);
            $this->assertSame('ON_CHANGE', $valve['closeValveOnFloodType']);
            $this->assertFalse($valve['extra']);
            $this->assertArrayNotHasKey('sensorChannelNumbers', $valve);
            $this->assertSame(['floodSensorChannelIds'], json_decode(
                $this->row($device->getChannels()[21]->getId())['properties'],
                true
            )['hiddenConfigFields']);
            $this->assertSame(
                ['sensors.channelId', 'other'],
                json_decode($this->row($device->getChannels()[20]->getId())['properties'], true)['hiddenConfigFields']
            );
            foreach ([20, 21] as $index) {
                $id = $device->getChannels()[$index]->getId();
                $before[$id] = $this->row($id);
            }
        }
        $this->getEntityManager()->getConnection()->delete('migration_versions', ['version' => self::VERSION]);
        $this->executeCommand(['command' => 'doctrine:migrations:execute', 'versions' => [self::VERSION], '--up' => true]);
        foreach ($before as $id => $row) {
            $this->assertSame($row, $this->row($id));
        }
    }

    public function testUnsetInvalidConflictsAndOtherDeviceNumbersNeverSelectChannelZero() {
        [$first, $other] = $this->devices();
        $this->getEntityManager()->getConnection()->update('supla_dev_channel', ['channel_number' => 100], ['id' => $other->getChannels()[0]->getId()]);
        $tankId = $this->store($first, 20, ['sensors' => [
            ['channelNo' => null, 'fillLevel' => 10], ['fillLevel' => 20], ['channelNo' => 100, 'fillLevel' => 30],
            ['channelNo' => 255, 'fillLevel' => 40], ['channelNo' => '0', 'fillLevel' => 50], ['channelNo' => -1, 'fillLevel' => 60],
            ['channelNo' => 0, 'fillLevel' => 70], ['channelNo' => 1, 'channelId' => null, 'fillLevel' => 80],
            ['channelNo' => 2, 'channelId' => $other->getChannels()[3]->getId(), 'fillLevel' => 90], null,
        ]]);
        $valveId = $this->store($first, 21, ['sensorChannelNumbers' => [null, -1, 255, '0', 100, 999, false, [], 0]]);
        $conflictId = $this->store($other, 21, ['sensorChannelNumbers' => [1, 2], 'floodSensorChannelIds' => [null, $first->getChannels()[5]->getId()]]);
        $this->migrateReferences();
        $slots = json_decode($this->row($tankId)['user_config'], true)['sensors'];
        $this->assertCount(10, $slots);
        foreach (range(0, 5) as $index) {
            $this->assertNull($slots[$index]['channelId']);
            $this->assertArrayNotHasKey('channelNo', $slots[$index]);
        }
        $this->assertSame($first->getChannels()[0]->getId(), $slots[6]['channelId']);
        $this->assertNull($slots[7]['channelId']);
        $this->assertSame($other->getChannels()[3]->getId(), $slots[8]['channelId']);
        $this->assertNull($slots[9]);
        $this->assertSame(
            [null, null, null, null, null, null, null, null, $first->getChannels()[0]->getId()],
            json_decode($this->row($valveId)['user_config'], true)['floodSensorChannelIds']
        );
        $this->assertSame(['floodSensorChannelIds' => [null, $first->getChannels()[5]->getId()]], json_decode($this->row($conflictId)['user_config'], true));
    }

    public function testMalformedAndUnrelatedJsonAndCanonicalNullAreSafe() {
        [$first, $other] = $this->devices();
        $bad = $this->store($first, 20, '{broken', '{ "extra": {} }');
        $canonical = $this->store($first, 21, '{ "floodSensorChannelIds": null, "extra": [] }');
        $before = $this->row($canonical);
        $conflict = $this->store($other, 21, ['floodSensorChannelIds' => null, 'sensorChannelNumbers' => [0]]);
        $slots = $this->store($other, 20, ['sensors' => [false, ['channelNo' => 1, 'fillLevel' => 60], null, 'bad'], 'extra' => []]);
        $this->migrateReferences();
        $this->assertSame('{broken', $this->row($bad)['user_config']);
        $this->assertSame('{ "extra": {} }', $this->row($bad)['properties']);
        $this->assertSame($before, $this->row($canonical));
        $this->assertSame(['floodSensorChannelIds' => null], json_decode($this->row($conflict)['user_config'], true));
        $actual = json_decode($this->row($slots)['user_config'], true);
        $this->assertSame([false, ['fillLevel' => 60, 'channelId' => $other->getChannels()[1]->getId()], null, 'bad'], $actual['sensors']);
        $this->assertSame([], $actual['extra']);
    }

    public function testPostMigrationCanonicalReadUnrelatedSaveAndNormalDependencyCleanup() {
        [$first] = $this->devices();
        $tankId = $this->store($first, 20, ['sensors' => [['channelNo' => 0, 'fillLevel' => 75],
            ['channelNo' => 1, 'fillLevel' => 95], null], 'warningAboveLevel' => 75]);
        $valveId = $this->store($first, 21, ['sensorChannelNumbers' => [0, 1, null], 'closeValveOnFloodType' => 'ALWAYS']);
        $this->migrateReferences();
        $em = $this->getEntityManager();
        $em->clear();
        $tank = $em->find(IODeviceChannel::class, $tankId);
        $valve = $em->find(IODeviceChannel::class, $valveId);
        $translator = self::getContainer()->get(SubjectConfigTranslator::class);
        $this->assertCount(3, $translator->getConfig($valve)['floodSensorChannelIds']);
        $client = $this->createAuthenticatedClient($tank->getUser());
        $client->apiRequestV3('GET', '/api/channels/' . $valveId);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSame(
            [$first->getChannels()[0]->getId(), $first->getChannels()[1]->getId(), null],
            json_decode($client->getResponse()->getContent(), true)['config']['floodSensorChannelIds']
        );
        $em = $this->getEntityManager();
        $tank = $em->find(IODeviceChannel::class, $tankId);
        $valve = $em->find(IODeviceChannel::class, $valveId);
        $translator = self::getContainer()->get(SubjectConfigTranslator::class);
        $translator->setConfig($tank, array_merge($translator->getConfig($tank), ['muteAlarmSoundWithoutAdditionalAuth' => true]));
        $translator->setConfig($valve, ['closeValveOnFloodType' => 'ON_CHANGE']);
        $em->flush();
        $source = $em->find(IODeviceChannel::class, $first->getChannels()[0]->getId());
        self::getContainer()->get(ChannelDependencies::class)->clearDependencies($source, true);
        $em->flush();
        $this->assertSame([null, $first->getChannels()[1]->getId(), null], $translator->getConfig($valve)['floodSensorChannelIds']);
        $this->assertSame([['fillLevel' => 75, 'channelId' => null],
            ['fillLevel' => 95, 'channelId' => $first->getChannels()[1]->getId()], null], $tank->getUserConfigValue('sensors'));
        $this->assertSame(75, $tank->getUserConfigValue('warningAboveLevel'));
        $this->assertTrue($tank->getUserConfigValue('muteAlarmSoundWithoutAdditionalAuth'));
        $this->assertStringNotContainsString('channelNo', $this->row($tankId)['user_config']);
        $this->assertStringNotContainsString('sensorChannelNumbers', $this->row($valveId)['user_config']);
    }
    public function testDeletedAndWrongAccountRowsOnSameDeviceCannotResolveAndValidSiblingSurvives() {
        [$first] = $this->devices();
        $connection = $this->getEntityManager()->getConnection();
        $otherUser = $this->createConfirmedUser('wrong-migration@supla.org');
        $connection->update('supla_dev_channel', ['user_id' => $otherUser->getId()], ['id' => $first->getChannels()[0]->getId()]);
        $connection->delete('supla_dev_channel', ['id' => $first->getChannels()[1]->getId()]);
        $id = $this->store($first, 21, ['sensorChannelNumbers' => [0, 1, 2], 'extra' => true]);
        $this->migrateReferences();
        $this->assertSame(
            ['extra' => true, 'floodSensorChannelIds' => [null, null, $first->getChannels()[2]->getId()]],
            json_decode($this->row($id)['user_config'], true)
        );
    }

    public static function malformedSensorArrays(): array {
        return [[false], ['bad'], [42], [null], [['unexpected' => 'object']]];
    }

    /** @dataProvider malformedSensorArrays */
    public function testMalformedSensorArraySurvivesMigrationListGetAndUnrelatedSave($sensors) {
        [$device] = $this->devices();
        $id = $this->store($device, 20, ['sensors' => $sensors, 'extra' => 'diagnostic']);
        $before = $this->row($id)['user_config'];
        $this->migrateReferences();
        $this->assertSame($before, $this->row($id)['user_config']);
        $this->getEntityManager()->clear();
        $tank = $this->getEntityManager()->find(IODeviceChannel::class, $id);
        $client = $this->createAuthenticatedClient($tank->getUser());
        $client->apiRequestV3('GET', '/api/channels');
        $this->assertStatusCode(200, $client->getResponse());
        $channels = array_column(json_decode($client->getResponse()->getContent(), true), null, 'id');
        $this->assertSame([], $channels[$id]['config']['levelSensors']);
        $this->assertSame([], $channels[$id]['config']['levelSensorChannelIds']);
        $this->assertSame($before, $this->row($id)['user_config']);
        $client->apiRequestV3('PUT', '/api/channels/' . $id, ['config' => ['muteAlarmSoundWithoutAdditionalAuth' => true]]);
        $this->assertStatusCode(200, $client->getResponse());
        $stored = json_decode($this->row($id)['user_config'], true);
        $this->assertSame($sensors, $stored['sensors']);
        $this->assertSame('diagnostic', $stored['extra']);
        $this->assertTrue($stored['muteAlarmSoundWithoutAdditionalAuth']);
    }

    public function testPercentageValveIsOutsideHistoricalMigrationAndRuntimeTranslatorScope() {
        $device = $this->devices()[0];
        $valve = $device->getChannels()[21];
        EntityUtils::setField($valve, 'type', ChannelType::VALVEPERCENTAGE);
        EntityUtils::setField($valve, 'function', CF::VALVEPERCENTAGE);
        $this->flush();
        $id = $this->store($device, 21, ['sensorChannelNumbers' => [0], 'extra' => 'preserved']);
        $before = $this->row($id);
        $this->migrateReferences();
        $this->assertSame($before, $this->row($id));
        $translator = self::getContainer()->get(\App\Model\UserConfigTranslator\ValveConfigTranslator::class);
        $this->assertFalse($translator->supports($this->freshEntity($valve)));
    }
}

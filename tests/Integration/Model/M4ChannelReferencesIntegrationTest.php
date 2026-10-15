<?php

namespace App\Tests\Integration\Model;

use App\Entity\EntityUtils;
use App\Entity\Main\IODevice;
use App\Entity\Main\User;
use App\Enums\ChannelFlags;
use App\Enums\ChannelConfigChangeScope;
use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType;
use App\Enums\IoDeviceFlags;
use App\Model\UserConfigTranslator\SubjectConfigTranslator;
use App\Model\Dependencies\ChannelDependencies;
use App\Supla\SuplaServerMock;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;
use App\Tests\Integration\Traits\SuplaAssertions;

/** @small */
class M4ChannelReferencesIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;
    use SuplaAssertions;

    private ?User $user = null;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    private function device(?User $user = null, int $flags = IoDeviceFlags::SUPLAN_SUPPORTED): IODevice {
        $device = $this->createDevice($this->createLocation($this->freshEntity($user ?? $this->user)), [
            [ChannelType::SENSORNO, CF::FLOOD_SENSOR], [ChannelType::SENSORNO, CF::CONTAINER_LEVEL_SENSOR],
            [ChannelType::CONTAINER, CF::CONTAINER], [ChannelType::VALVEOPENCLOSE, CF::VALVEOPENCLOSE],
            [ChannelType::HVAC, CF::HVAC_THERMOSTAT], [ChannelType::HVAC, CF::HVAC_THERMOSTAT_HEAT_COOL],
            [ChannelType::RELAY, CF::PUMPSWITCH], [ChannelType::RELAY, CF::HEATORCOLDSOURCESWITCH],
            [ChannelType::HUMIDITYANDTEMPSENSOR, CF::THERMOMETER],
        ]);
        EntityUtils::setField($device, 'flags', $flags);
        EntityUtils::setField($device, 'lastConnected', null);
        EntityUtils::setField($device->getChannels()[3], 'flags', ChannelFlags::FLOOD_SENSORS_SUPPORTED);
        foreach ([4, 5] as $index) {
            $device->getChannels()[$index]->setUserConfig(['mainThermometerChannelId' => null, 'subfunction' => 'HEAT']);
        }
        $this->flush();
        return $device;
    }

    private function referenceConfig(string $role, int $id): array {
        return match ($role) {
            'levelSensorChannelIds' => ['levelSensors' => [['channelId' => $id, 'fillLevel' => 75]]],
            'floodSensorChannelIds' => [$role => [$id]],
            default => [$role => $id],
        };
    }

    public static function roles(): array {
        return [['mainThermometerChannelId', 4, 8], ['auxThermometerChannelId', 4, 8], ['binarySensorChannelId', 4, 0],
            ['masterThermostatChannelId', 4, 5], ['pumpSwitchChannelId', 4, 6], ['heatOrColdSourceSwitchChannelId', 4, 7],
            ['levelSensorChannelIds', 2, 1], ['floodSensorChannelIds', 3, 0]];
    }

    /** @dataProvider roles */
    public function testOfflineRemoteAndLegacyLocalCrossLocationAccepted(string $role, int $destIndex, int $sourceIndex) {
        $destination = $this->device();
        $source = $this->device();
        $legacy = $this->device(null, 0);
        $legacy->getChannels()[$sourceIndex]->setLocation($source->getLocation());
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $destination->getChannels()[$destIndex]->getId(), [
            'config' => $this->referenceConfig($role, $source->getChannels()[$sourceIndex]->getId()),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV3('PUT', '/api/channels/' . $legacy->getChannels()[$destIndex]->getId(), [
            'config' => $this->referenceConfig($role, $legacy->getChannels()[$sourceIndex]->getId()),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
    }

    public static function rejected(): array {
        $cases = [];
        foreach (self::roles() as $role) {
            foreach (['legacy source', 'legacy destination', 'wrong account', 'wrong type', 'wrong function', 'self'] as $reason) {
                $cases[] = [...$role, $reason];
            }
        }
        foreach ([['binarySensorChannelId', 4, 0], ['levelSensorChannelIds', 2, 1], ['floodSensorChannelIds', 3, 0]] as $role) {
            $cases[] = [...$role, 'unsupported active function'];
        }
        return $cases;
    }

    /** @dataProvider rejected */
    public function testRejectsUnauthorizedIncompatibleAndSelfReferences(string $role, int $destIndex, int $sourceIndex, string $reason) {
        $destination = $this->device(null, $reason === 'legacy destination' ? 0 : IoDeviceFlags::SUPLAN_SUPPORTED);
        $source = $this->device(
            $reason === 'wrong account' ? $this->createConfirmedUser('other-m4-' . $role . '@supla.org') : null,
            $reason === 'legacy source' ? 0 : IoDeviceFlags::SUPLAN_SUPPORTED
        );
        $candidate = $source->getChannels()[$sourceIndex];
        if ($reason === 'wrong type') {
            EntityUtils::setField($candidate, 'type', ChannelType::RGBLEDCONTROLLER);
        } elseif ($reason === 'wrong function') {
            EntityUtils::setField($candidate, 'function', CF::NONE);
        } elseif ($reason === 'unsupported active function') {
            EntityUtils::setField($candidate, 'function', CF::LIGHTSWITCH);
        } elseif ($reason === 'self') {
            $candidate = $destination->getChannels()[$destIndex];
        }
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $destination->getChannels()[$destIndex]->getId(), [
            'config' => $this->referenceConfig($role, $candidate->getId()),
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testCompatibleFunctionChangeKeepsRemoteReferencesAndIncompatibleChangeClearsOnlyItsSlots() {
        $destination = $this->device();
        $source = $this->device();
        $sensor = $source->getChannels()[0];
        $siblingId = $source->getChannels()[1]->getId();
        $hvac = $destination->getChannels()[4];
        $tank = $destination->getChannels()[2];
        $valve = $destination->getChannels()[3];
        $hvac->setUserConfigValue('binarySensorChannelId', $sensor->getId());
        $tank->setUserConfig(['sensors' => [['channelId' => $sensor->getId(), 'fillLevel' => 75, 'extra' => 1],
            ['channelId' => $siblingId, 'fillLevel' => 95]], 'warningAboveLevel' => 75]);
        $valve->setUserConfig(['floodSensorChannelIds' => [$sensor->getId(), null, $siblingId], 'closeValveOnFloodType' => 'ON_CHANGE']);
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        SuplaServerMock::$executedCommands = [];
        $client->apiRequestV3('PUT', '/api/channels/' . $sensor->getId(), ['functionId' => CF::CONTAINER_LEVEL_SENSOR]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSame(CF::CONTAINER_LEVEL_SENSOR, $this->freshEntity($sensor)->getFunction()->getId());
        $this->assertSuplaCommandExecuted(sprintf(
            'USER-ON-CHANNEL-CONFIG-CHANGED:%d,%d,%d,%d,%d,%d',
            $this->user->getId(),
            $source->getId(),
            $sensor->getId(),
            ChannelType::SENSORNO,
            CF::CONTAINER_LEVEL_SENSOR,
            ChannelConfigChangeScope::CHANNEL_FUNCTION
        ));
        $this->assertSame($sensor->getId(), $this->freshEntity($tank)->getUserConfigValue('sensors')[0]['channelId']);
        $this->assertSame($sensor->getId(), $this->freshEntity($hvac)->getUserConfigValue('binarySensorChannelId'));
        $this->assertSame([$sensor->getId(), null, $siblingId], $this->freshEntity($valve)->getUserConfigValue('floodSensorChannelIds'));
        $client->apiRequestV3('PUT', '/api/channels/' . $sensor->getId(), ['functionId' => CF::NONE]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertNull($this->freshEntity($hvac)->getUserConfigValue('binarySensorChannelId'));
        foreach ([$tank, $valve, $hvac] as $dependent) {
            $this->assertSuplaCommandExecuted(sprintf(
                'USER-ON-CHANNEL-CONFIG-CHANGED:%d,%d,%d,%d,%d,%d',
                $this->user->getId(),
                $destination->getId(),
                $dependent->getId(),
                $dependent->getType()->getId(),
                $dependent->getFunction()->getId(),
                ChannelConfigChangeScope::RELATIONS | ChannelConfigChangeScope::JSON_BASIC
            ));
        }
        $this->assertSame([null, null, $siblingId], $this->freshEntity($valve)->getUserConfigValue('floodSensorChannelIds'));
        $tank = $this->freshEntity($tank);
        $this->assertSame(
            [['channelId' => null, 'fillLevel' => 75, 'extra' => 1], ['channelId' => $siblingId, 'fillLevel' => 95]],
            $tank->getUserConfigValue('sensors')
        );
        $this->assertSame(75, $tank->getUserConfigValue('warningAboveLevel'));
        $client->apiRequestV3('PUT', '/api/channels/' . $sensor->getId(), ['functionId' => CF::FLOOD_SENSOR]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertNull($this->freshEntity($hvac)->getUserConfigValue('binarySensorChannelId'));
    }

    public function testRemoteMasterChainAndMasterOfOtherDeviceCannotBecomeSlave() {
        $first = $this->device();
        $second = $this->device();
        $third = $this->device();
        $first->getChannels()[4]->setUserConfigValue('masterThermostatChannelId', $second->getChannels()[4]->getId());
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $third->getChannels()[4]->getId(), [
            'config' => ['masterThermostatChannelId' => $first->getChannels()[4]->getId()],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
        $client->apiRequestV3('PUT', '/api/channels/' . $second->getChannels()[4]->getId(), [
            'config' => ['masterThermostatChannelId' => $third->getChannels()[4]->getId()],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }
    /** @dataProvider deletionKinds */
    public function testSourceChannelOrDeviceDeletionClearsRemoteSlotsWithoutReplacingSiblings(bool $wholeDevice) {
        $destination = $this->device();
        $source = $this->device();
        EntityUtils::setField($source, 'flags', IoDeviceFlags::SUPLAN_SUPPORTED | IoDeviceFlags::ALWAYS_ALLOW_CHANNEL_DELETION);
        $sensor = $source->getChannels()[0];
        $sibling = $destination->getChannels()[1]->getId();
        $tank = $destination->getChannels()[2];
        $valve = $destination->getChannels()[3];
        $tank->setUserConfig(['sensors' => [['channelId' => $sensor->getId(), 'fillLevel' => 75],
            ['channelId' => $sibling, 'fillLevel' => 95]], 'warningAboveLevel' => 75]);
        $valve->setUserConfig(['floodSensorChannelIds' => [$sensor->getId(), null, $sibling], 'closeValveOnFloodType' => 'ON_CHANGE']);
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('DELETE', $wholeDevice ? '/api/iodevices/' . $source->getId() : '/api/channels/' . $sensor->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->assertSame([null, null, $sibling], $this->freshEntity($valve)->getUserConfigValue('floodSensorChannelIds'));
        $tank = $this->freshEntity($tank);
        $this->assertNull($tank->getUserConfigValue('sensors')[0]['channelId']);
        $this->assertSame($sibling, $tank->getUserConfigValue('sensors')[1]['channelId']);
        $this->assertSame(75, $tank->getUserConfigValue('warningAboveLevel'));
    }

    public static function deletionKinds(): array {
        return [[false], [true]];
    }

    public function testStaleFormDoesNotResurrectClearedReferenceDuringUnrelatedSave() {
        $destination = $this->device();
        $source = $this->device();
        $tank = $destination->getChannels()[2];
        $sensor = $source->getChannels()[0];
        $tank->setUserConfig(['sensors' => [['channelId' => $sensor->getId(), 'fillLevel' => 75]], 'warningAboveLevel' => 75]);
        $this->flush();
        $translator = self::getContainer()->get(SubjectConfigTranslator::class);
        $old = $translator->getConfig($tank);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $sensor->getId(), ['functionId' => CF::NONE]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV3('PUT', '/api/channels/' . $tank->getId(), [
            'config' => array_merge($old, ['muteAlarmSoundWithoutAdditionalAuth' => true]), 'configBefore' => $old,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $tank = $this->freshEntity($tank);
        $this->assertNull($tank->getUserConfigValue('sensors')[0]['channelId']);
        $this->assertTrue($tank->getUserConfigValue('muteAlarmSoundWithoutAdditionalAuth'));
        // A deliberate attempt to re-add the now incompatible source is rejected.
        $client->apiRequestV3('PUT', '/api/channels/' . $tank->getId(), ['config' => ['levelSensors' => $old['levelSensors']]]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testLocationAndOfflineStateAloneDoNotClearReferences() {
        $destination = $this->device();
        $source = $this->device();
        $hvac = $destination->getChannels()[4];
        $sensor = $source->getChannels()[0];
        $hvac->setUserConfigValue('binarySensorChannelId', $sensor->getId());
        $this->flush();
        $location = $this->createLocation($this->freshEntity($this->user));
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $sensor->getId(), ['locationId' => $location->getId()]);
        $this->assertStatusCode(200, $client->getResponse());
        SuplaServerMock::$mockedResponses['^IS-IODEV-CONNECTED:' . $this->user->getId() . ',' . $source->getId() . '$'] = ['DISCONNECTED:' . $source->getId()];
        $client->apiRequestV3('GET', '/api/iodevices/' . $source->getId() . '?include=connected');
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSame($sensor->getId(), $this->freshEntity($hvac)->getUserConfigValue('binarySensorChannelId'));
    }

    public static function thermometerTypes(): array {
        $cases = [];
        foreach (['mainThermometerChannelId', 'auxThermometerChannelId'] as $role) {
            foreach ([ChannelType::THERMOMETERDS18B20, ChannelType::THERMOMETER, ChannelType::HUMIDITYANDTEMPSENSOR] as $type) {
                foreach ([false, true] as $remote) {
                    $cases[] = [$role, $type, $remote];
                }
            }
        }
        return $cases;
    }

    /** @dataProvider thermometerTypes */
    public function testThermometerLocalAndRemotePolicyThroughApi(string $role, int $type, bool $remote) {
        $destination = $this->device();
        $source = $remote ? $this->device() : $destination;
        $candidate = $source->getChannels()[8];
        EntityUtils::setField($candidate, 'type', $type);
        $candidate->setLocation($this->createLocation($this->freshEntity($this->user)));
        $this->flush();
        $hvac = $destination->getChannels()[4];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $hvac->getId(), ['config' => [$role => $candidate->getId()]]);
        $rejected = $type === ChannelType::THERMOMETERDS18B20;
        $this->assertStatusCode($rejected ? 400 : 200, $client->getResponse());
        $this->assertSame($rejected ? null : $candidate->getId(), $this->freshEntity($hvac)->getUserConfigValue($role));
        if (!$rejected) {
            $client->apiRequestV3('PUT', '/api/channels/' . $hvac->getId(), ['config' => ['minOnTimeS' => 30]]);
            $this->assertStatusCode(200, $client->getResponse());
            $this->assertSame($candidate->getId(), $this->freshEntity($hvac)->getUserConfigValue($role));
        }
    }

    public static function binaryFunctions(): array {
        $cases = [];
        foreach ([['binarySensorChannelId', 4], ['levelSensorChannelIds', 2], ['floodSensorChannelIds', 3]] as [$role, $index]) {
            foreach (ChannelType::functions()[ChannelType::SENSORNO] as $function) {
                $cases[] = [$role, $index, $function->getId()];
            }
        }
        return $cases;
    }

    /** @dataProvider binaryFunctions */
    public function testEveryActiveBinaryFunctionAcceptedThroughApi(string $role, int $index, int $function) {
        $destination = $this->device();
        $source = $this->device();
        $sensor = $source->getChannels()[0];
        $sensor->setFunction(CF::fromString($function));
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $destination->getChannels()[$index]->getId(), [
            'config' => $this->referenceConfig($role, $sensor->getId()),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
    }

    /** @dataProvider roles */
    public function testFunctionNoneClearsOnlyAffectedRoleAndNeverRestoresIt(string $role, int $destIndex, int $sourceIndex) {
        $destination = $this->device();
        $source = $this->device();
        $sensor = $source->getChannels()[$sourceIndex];
        $dependent = $destination->getChannels()[$destIndex];
        if ($sourceIndex === 8) {
            $sensor->setFunction(CF::HUMIDITYANDTEMPERATURE());
            $this->flush();
        }
        $oldFunction = $sensor->getFunction()->getId();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $dependent->getId(), ['config' => $this->referenceConfig($role, $sensor->getId())]);
        $this->assertStatusCode(200, $client->getResponse());
        SuplaServerMock::$executedCommands = [];
        $client->apiRequestV3('PUT', '/api/channels/' . $sensor->getId(), ['functionId' => CF::NONE]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSame(CF::NONE, $this->freshEntity($sensor)->getFunction()->getId());
        $notifications = array_filter(SuplaServerMock::$executedCommands, fn($command) => str_starts_with($command, sprintf(
            'USER-ON-CHANNEL-CONFIG-CHANGED:%d,%d,%d,',
            $this->user->getId(),
            $source->getId(),
            $sensor->getId()
        )));
        $this->assertNotEmpty($notifications);
        foreach ($notifications as $command) {
            $this->assertNotSame(0, ((int)substr($command, strrpos($command, ',') + 1)) & ChannelConfigChangeScope::CHANNEL_FUNCTION);
        }
        $translator = self::getContainer()->get(SubjectConfigTranslator::class);
        $cleared = $translator->getConfig($this->freshEntity($dependent));
        $this->assertSame(str_ends_with($role, 'Ids') ? [null] : null, $cleared[$role]);
        $client->apiRequestV3('PUT', '/api/channels/' . $sensor->getId(), ['functionId' => $oldFunction]);
        $this->assertStatusCode(200, $client->getResponse());
        $restored = $translator->getConfig($this->freshEntity($dependent));
        $this->assertSame($cleared[$role], $restored[$role]);
    }

    public function testType3000CleanupClearsLocalAndRemoteAndRetainsUnrelatedReferences() {
        $source = $this->device();
        $destination = $this->device();
        $candidate = $source->getChannels()[8];
        $local = $source->getChannels()[4];
        $remote = $destination->getChannels()[4];
        EntityUtils::setField($candidate, 'type', ChannelType::THERMOMETERDS18B20);
        $local->setUserConfigValue('mainThermometerChannelId', $candidate->getId());
        $remote->setUserConfigValue('mainThermometerChannelId', $candidate->getId());
        $remote->setUserConfigValue('binarySensorChannelId', $destination->getChannels()[0]->getId());
        $this->flush();
        // Recheck the current Type via the existing cleanup service; no public Type editor exists.
        self::getContainer()->get(ChannelDependencies::class)->clearDependencies($candidate, false, CF::THERMOMETER());
        $this->flush();
        $this->assertNull($this->freshEntity($local)->getUserConfigValue('mainThermometerChannelId'));
        $this->assertNull($this->freshEntity($remote)->getUserConfigValue('mainThermometerChannelId'));
        $this->assertSame($destination->getChannels()[0]->getId(), $this->freshEntity($remote)->getUserConfigValue('binarySensorChannelId'));
    }

    public static function invalidSensorLists(): array {
        return [
            ['levelSensors', 2, ['first' => ['channelId' => null, 'fillLevel' => 75]]],
            ['levelSensors', 2, [2 => ['channelId' => null, 'fillLevel' => 75]]],
            ['floodSensorChannelIds', 3, ['first' => null]],
            ['floodSensorChannelIds', 3, [2 => null]],
        ];
    }

    /** @dataProvider invalidSensorLists */
    public function testSensorListsRejectObjectsWithoutChangingStoredConfig(string $field, int $index, array $invalid) {
        $device = $this->device();
        $destination = $device->getChannels()[$index];
        $stored = $field === 'levelSensors'
            ? ['sensors' => [null, ['channelId' => $device->getChannels()[1]->getId(), 'fillLevel' => 75, 'label' => 'upper']],
                'warningAboveLevel' => 75]
            : ['floodSensorChannelIds' => [null, $device->getChannels()[0]->getId()]];
        $destination->setUserConfig($stored);
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $destination->getId(), ['config' => [$field => $invalid]]);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame($stored, $this->freshEntity($destination)->getUserConfig());
    }

    public function testSensorListsPreserveNullSlotsAndMetadataAndAcceptEmptyLists() {
        $device = $this->device();
        $client = $this->createAuthenticatedClient($this->user);
        foreach ([2 => 'levelSensors', 3 => 'floodSensorChannelIds'] as $index => $field) {
            $destination = $device->getChannels()[$index];
            $list = $index === 2
                ? [null, ['channelId' => $device->getChannels()[1]->getId(), 'fillLevel' => 75, 'label' => 'upper']]
                : [null, $device->getChannels()[0]->getId()];
            $storedField = $index === 2 ? 'sensors' : $field;
            $client->apiRequestV3('PUT', '/api/channels/' . $destination->getId(), ['config' => [$field => $list]]);
            $this->assertStatusCode(200, $client->getResponse());
            $this->assertSame($list, $this->freshEntity($destination)->getUserConfigValue($storedField));
            $client->apiRequestV3('PUT', '/api/channels/' . $destination->getId(), ['config' => [$field => []]]);
            $this->assertStatusCode(200, $client->getResponse());
            $this->assertSame([], $this->freshEntity($destination)->getUserConfigValue($storedField));
        }
    }

    public static function unsupportedHvacRelayReferences(): array {
        return [
            ['pumpSwitchChannelId', 6, false],
            ['pumpSwitchChannelId', 6, true],
            ['heatOrColdSourceSwitchChannelId', 7, false],
            ['heatOrColdSourceSwitchChannelId', 7, true],
        ];
    }

    /** @dataProvider unsupportedHvacRelayReferences */
    public function testRejectsRelay2xG5la1aForLocalAndRemoteHvacReferences(string $role, int $index, bool $remote) {
        $destination = $this->device();
        $source = $remote ? $this->device() : $destination;
        $candidate = $source->getChannels()[$index];
        EntityUtils::setField($candidate, 'type', ChannelType::RELAY2XG5LA1A);
        $this->flush();
        $hvac = $destination->getChannels()[4];
        $stored = $hvac->getUserConfig();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $hvac->getId(), ['config' => [$role => $candidate->getId()]]);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertSame($stored, $this->freshEntity($hvac)->getUserConfig());
    }
}

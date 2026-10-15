<?php

namespace App\Tests\Integration\Model;

use App\Entity\EntityUtils;
use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction as CF;
use App\Enums\ChannelType;
use App\Enums\IoDeviceFlags;
use App\Model\UserConfigTranslator\HvacChannelReferences;
use App\Model\UserConfigTranslator\SubjectConfigTranslator;
use App\Supla\SuplaServerMock;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;
use Doctrine\DBAL\Logging\DebugStack;

/** @small */
class HvacSupLanIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use SuplaApiHelper;

    private ?User $user = null;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    private function device(?User $user = null, int $flags = IoDeviceFlags::SUPLAN_SUPPORTED): IODevice {
        $device = $this->createDevice($this->createLocation($user ?: $this->user), [
            [ChannelType::THERMOMETER, CF::THERMOMETER],
            [ChannelType::HUMIDITYANDTEMPSENSOR, CF::HUMIDITYANDTEMPERATURE],
            [ChannelType::SENSORNO, CF::NOLIQUIDSENSOR],
            [ChannelType::HVAC, CF::HVAC_THERMOSTAT],
            [ChannelType::RELAY, CF::PUMPSWITCH],
            [ChannelType::RELAY, CF::HEATORCOLDSOURCESWITCH],
            [ChannelType::HVAC, CF::HVAC_THERMOSTAT],
        ]);
        EntityUtils::setField($device, 'flags', $flags);
        // No LAN discovery data is installed by these fixtures.
        EntityUtils::setField($device, 'lastConnected', null);
        foreach ([3, 6] as $index) {
            $device->getChannels()[$index]->setUserConfig(['mainThermometerChannelId' => null, 'subfunction' => 'HEAT']);
        }
        $this->flush();
        return $device;
    }

    public function testAvailabilityUsesOneScalarQueryAndCurrentAccountCapabilityAndFunction() {
        $user = $this->createConfirmedUser('availability-query@supla.org');
        $destination = $this->createDevice($this->createLocation($user), [[ChannelType::HVAC, CF::HVAC_THERMOSTAT]]);
        EntityUtils::setField($destination, 'flags', IoDeviceFlags::SUPLAN_SUPPORTED);
        $hvac = $destination->getChannels()[0];
        $hvac->setUserConfig(['mainThermometerChannelId' => null, 'subfunction' => 'HEAT']);
        $source = $this->device($user, 0);
        $this->device($this->createConfirmedUser('other-availability@supla.org'));
        $this->flush();
        $translator = self::getContainer()->get(SubjectConfigTranslator::class);
        $connectionConfig = $this->getEntityManager()->getConnection()->getConfiguration();
        $previousLogger = $connectionConfig->getSQLLogger();
        $logger = new DebugStack();
        $connectionConfig->setSQLLogger($logger);
        try {
            foreach ([false, true, false] as $available) {
                EntityUtils::setField($source, 'flags', $available ? IoDeviceFlags::SUPLAN_SUPPORTED : 0);
                $this->flush();
                $logger->queries = [];
                $config = $translator->getConfig($hvac);
                foreach (['pumpSwitchAvailable', 'heatOrColdSourceSwitchAvailable', 'masterThermostatAvailable'] as $field) {
                    $this->assertSame($available, $config[$field]);
                }
                $this->assertCount(1, $logger->queries);
                $this->assertStringContainsString('COUNT(*)', array_values($logger->queries)[0]['sql']);
                $this->assertStringContainsString('GROUP BY c.func', array_values($logger->queries)[0]['sql']);
            }
            EntityUtils::setField($source, 'flags', IoDeviceFlags::SUPLAN_SUPPORTED);
            foreach ([3, 4, 5, 6] as $index) {
                EntityUtils::setField($source->getChannels()[$index], 'function', CF::NONE);
            }
            $this->flush();
            $config = $translator->getConfig($hvac);
            $this->assertFalse($config['pumpSwitchAvailable']);
            $this->assertFalse($config['heatOrColdSourceSwitchAvailable']);
            $this->assertFalse($config['masterThermostatAvailable']);
            EntityUtils::setField($destination, 'flags', 0);
            $this->flush();
            $logger->queries = [];
            $config = $translator->getConfig($hvac);
            $this->assertFalse($config['pumpSwitchAvailable']);
            $this->assertFalse($config['masterThermostatAvailable']);
            $this->assertCount(0, $logger->queries);
        } finally {
            $connectionConfig->setSQLLogger($previousLogger);
        }
    }

    public function testAllSixCanonicalIdRoundTripThroughTranslatorAndDatabase() {
        $device = $this->device();
        $hvac = $device->getChannels()[6];
        $config = [];
        foreach (HvacChannelReferences::FIELDS as $index => $field) {
            $config[$field . 'ChannelId'] = $device->getChannels()[$index]->getId();
        }
        $translator = self::getContainer()->get(SubjectConfigTranslator::class);
        $translator->setConfig($hvac, $config);
        $this->flush();
        $hvac = $this->freshEntity($hvac);
        $actual = $translator->getConfig($hvac);
        foreach ($config as $key => $id) {
            $this->assertSame($id, $actual[$key]);
            $this->assertSame($id, $hvac->getUserConfigValue($key));
        }
        foreach (HvacChannelReferences::FIELDS as $field) {
            $this->assertArrayNotHasKey($field . 'ChannelNo', $hvac->getUserConfig());
        }
    }

    public function testLegacyConfigMigratesWhenAnUnrelatedSettingIsSaved() {
        $device = $this->device();
        $hvac = $device->getChannels()[6];
        $hvac->setUserConfig(['mainThermometerChannelNo' => 0, 'auxThermometerChannelNo' => null, 'subfunction' => 'HEAT']);
        self::getContainer()->get(SubjectConfigTranslator::class)->setConfig($hvac, ['minOnTimeS' => 12]);
        $this->flush();
        $stored = $this->freshEntity($hvac)->getUserConfig();
        $this->assertSame($device->getChannels()[0]->getId(), $stored['mainThermometerChannelId']);
        $this->assertArrayNotHasKey('mainThermometerChannelNo', $stored);
        $this->assertArrayNotHasKey('auxThermometerChannelNo', $stored);
    }

    public function testSameDeviceReferencesAcceptDifferentLocationsWithoutSupLan() {
        $device = $this->device(null, 0);
        $hvac = $device->getChannels()[6];
        foreach (HvacChannelReferences::FIELDS as $index => $field) {
            $source = $device->getChannels()[$index];
            $source->setLocation($this->createLocation($this->user));
        }
        $this->flush();
        $config = [];
        foreach (HvacChannelReferences::FIELDS as $index => $field) {
            $config[$field . 'ChannelId'] = $device->getChannels()[$index]->getId();
        }
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $hvac->getId(), ['config' => $config]);
        $this->assertStatusCode(200, $client->getResponse());
    }

    public function testOfflineSupLanRemoteMainAcceptedByApiAndStoredAsId() {
        $destination = $this->device();
        $source = $this->device();
        $hvac = $destination->getChannels()[6];
        $thermometer = $source->getChannels()[0];
        SuplaServerMock::$mockedResponses['^IS-IODEV-CONNECTED:' . $this->user->getId() . ',' . $source->getId() . '$'] = [
            'DISCONNECTED:' . $source->getId(),
        ];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/iodevices/' . $source->getId() . '?include=connected');
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertFalse(json_decode($client->getResponse()->getContent(), true)['connected']);
        $client->apiRequestV3('PUT', '/api/channels/' . $hvac->getId(), [
            'config' => ['mainThermometerChannelId' => $thermometer->getId()],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $stored = $this->freshEntity($hvac)->getUserConfig();
        $this->assertSame($thermometer->getId(), $stored['mainThermometerChannelId']);
        $this->assertArrayNotHasKey('mainThermometerChannelNo', $stored);
        $this->assertSame($thermometer->getId(), json_decode($client->getResponse()->getContent(), true)['config']['mainThermometerChannelId']);
    }

    /** @dataProvider rejectedRemoteConfigurations */
    public function testApiRejectsUnsupportedOrUnauthorizedRemoteReferences(
        string $field,
        int $sourceIndex,
        int $destFlags,
        int $sourceFlags,
        bool $wrongAccount
    ) {
        $destination = $this->device(null, $destFlags);
        $source = $this->device($wrongAccount ? $this->createConfirmedUser('other-hvac-' . $field . '@supla.org') : null, $sourceFlags);
        $hvac = $destination->getChannels()[6];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $hvac->getId(), [
            'config' => [$field . 'ChannelId' => $source->getChannels()[$sourceIndex]->getId()],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
        $this->assertNull($this->freshEntity($hvac)->getUserConfigValue($field . 'ChannelId'));
    }

    public static function rejectedRemoteConfigurations(): array {
        $flag = IoDeviceFlags::SUPLAN_SUPPORTED;
        return [
            'legacy destination' => ['mainThermometer', 0, 0, $flag, false],
            'legacy source' => ['mainThermometer', 0, $flag, 0, false],
            'both legacy' => ['mainThermometer', 0, 0, 0, false],
            'wrong account' => ['mainThermometer', 0, $flag, $flag, true],
            'wrong function' => ['mainThermometer', 4, $flag, $flag, false],
            'aux legacy source' => ['auxThermometer', 1, $flag, 0, false],
            'binary legacy destination' => ['binarySensor', 2, 0, $flag, false],
            'master wrong account' => ['masterThermostat', 3, $flag, $flag, true],
            'pump wrong function' => ['pumpSwitch', 5, $flag, $flag, false],
            'heat/cold wrong type' => ['heatOrColdSourceSwitch', 2, $flag, $flag, false],
        ];
    }

    /** @dataProvider remoteFields */
    public function testAllM4RemoteFieldsAcceptOfflineSupLanAndPersistCanonicalIds(string $field, int $index) {
        $destination = $this->device();
        $source = $this->device();
        $hvac = $destination->getChannels()[6];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $hvac->getId(), [
            'config' => [$field . 'ChannelId' => $source->getChannels()[$index]->getId()],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSame($source->getChannels()[$index]->getId(), $this->freshEntity($hvac)->getUserConfigValue($field . 'ChannelId'));
    }

    public static function remoteFields(): array {
        return [['auxThermometer', 1], ['binarySensor', 2], ['masterThermostat', 3], ['pumpSwitch', 4], ['heatOrColdSourceSwitch', 5]];
    }

    /** @dataProvider incompatibleMainThermometers */
    public function testMainRejectsIncompatibleTypeAndFunctionIndependently(int $type, int $function) {
        $destination = $this->device();
        $source = $this->device();
        $thermometer = $source->getChannels()[0];
        EntityUtils::setField($thermometer, 'type', $type);
        EntityUtils::setField($thermometer, 'function', $function);
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $destination->getChannels()[6]->getId(), [
            'config' => ['mainThermometerChannelId' => $thermometer->getId()],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public static function incompatibleMainThermometers(): array {
        return [
            'correct function, wrong type' => [ChannelType::RELAY, CF::THERMOMETER],
            'correct type, wrong function' => [ChannelType::THERMOMETER, CF::HUMIDITY],
        ];
    }

    public function testDeviceCapabilityIsExposedByApiFromPersistedFlags() {
        $device = $this->device();
        $legacy = $this->device(null, 0);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/iodevices/' . $device->getId());
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertTrue(json_decode($client->getResponse()->getContent(), true)['flags']['suplanSupported']);
        $client->apiRequestV3('GET', '/api/iodevices/' . $legacy->getId());
        $this->assertFalse(json_decode($client->getResponse()->getContent(), true)['flags']['suplanSupported']);
    }

    public function testChangingRemoteSourceFunctionClearsDestinationCanonicalReference() {
        $destination = $this->device();
        $source = $this->device();
        $hvac = $destination->getChannels()[6];
        $thermometer = $source->getChannels()[0];
        $hvac->setUserConfigValue('mainThermometerChannelId', $thermometer->getId());
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('PUT', '/api/channels/' . $thermometer->getId(), ['functionId' => CF::NONE]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertNull($this->freshEntity($hvac)->getUserConfigValue('mainThermometerChannelId'));
    }
}

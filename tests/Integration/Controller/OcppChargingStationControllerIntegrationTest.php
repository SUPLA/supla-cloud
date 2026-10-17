<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace App\Tests\Integration\Controller;

use App\Entity\EntityUtils;
use App\Entity\Main\ChannelValue;
use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\OcppChargingStation;
use App\Entity\Main\OAuth\AccessToken;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Enums\VirtualChannelType;
use App\Enums\VirtualDeviceType;
use App\Model\VirtualChannel\VirtualChannelStateUpdater;
use App\Repository\IODeviceChannelRepository;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\ResponseAssertions;
use App\Tests\Integration\Traits\SuplaApiHelper;

class OcppChargingStationControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    private $user;
    private $location;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
    }

    private function createCharger($client): array {
        $client->apiRequestV3('POST', '/api/integrations/ocpp', ['name' => 'Garage wallbox', 'locationId' => $this->location->getId()]);
        $this->assertStatusCode(201, $client->getResponse());
        $this->assertStringContainsString('no-store', $client->getResponse()->headers->get('Cache-Control'));
        return json_decode($client->getResponse()->getContent(), true);
    }

    public function testCreatesDeviceAndThreeChannelsWithoutInventingValues(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $created = $this->createCharger($client);
        $this->assertMatchesRegularExpression('/^[A-F0-9]{32}$/', $created['authorizationKey']);
        $this->assertStringStartsWith('U' . $this->user->getId() . '-', $created['chargePointIdentity']);
        $this->assertSame('wss://ocpp-test.supla.org/' . $created['chargePointIdentity'], $created['url']);
        $this->assertSame($created['chargePointIdentity'], $created['username']);
        $em = $this->getEntityManager();
        $station = $em->find(OcppChargingStation::class, $created['id']);
        $this->assertSame(hash('sha512', hex2bin($created['authorizationKey'])), $station->getAuthKeyHash());
        $device = $station->getIODevice();
        $this->assertSame(VirtualDeviceType::OCPP, $device->getVirtualType());
        $this->assertCount(3, $device->getChannels());
        $this->assertSame(
            [ChannelFunction::ELECTRICITYMETER, ChannelFunction::POWERSWITCH, ChannelFunction::GENERAL_PURPOSE_MEASUREMENT],
            $device->getChannels()->map(fn($ch) => $ch->getFunction()->getId())->toArray()
        );
        $this->assertSame(
            ['countersAvailable' => ['forwardActiveEnergy']],
            $device->getChannels()->first()->getProperties()
        );
        $this->assertSame([], $device->getChannels()[1]->getProperties());
        $this->assertSame(8, $device->getChannels()[1]->getAltIcon());
        $this->assertSame(46, $device->getChannels()[2]->getAltIcon());
        $this->assertSame('%', $device->getChannels()[2]->getUserConfigValue('unitAfterValue'));
        $this->assertFalse($device->getChannels()[2]->getUserConfigValue('keepHistory'));
        $this->assertSame(0, $em->getRepository(ChannelValue::class)->count([]));
        $this->assertNull($device->getLastConnected());

        $client->apiRequestV3('GET', '/api/integrations/ocpp');
        $this->assertStatusCode(200, $client->getResponse());
        $listing = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $listing['stations']);
        $this->assertArrayNotHasKey('authorizationKey', $listing['stations'][0]);
        $this->assertArrayNotHasKey('authKeyHash', $listing['stations'][0]);
        $client->apiRequestV3('GET', '/api/iodevices/' . $created['iodeviceId'] . '?include=channels');
        $this->assertStatusCode(200, $client->getResponse());
        $deviceJson = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('OCPP', $deviceJson['virtualType']);
        $this->assertTrue($deviceJson['isVirtual']);
        $this->assertSame('OCPP', $deviceJson['channels'][0]['virtualType']);
        $this->assertFalse($deviceJson['channels'][0]['deletable']);
        $this->assertStringNotContainsString($station->getAuthKeyHash(), $client->getResponse()->getContent());
    }

    public function testDataSourcesAndCyclicUpdaterNeverUseCharger(): void {
        EntityUtils::setField($this->user, 'limitVirtualChannels', 1);
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $charger = $this->createCharger($client);
        $this->executeCommand('supla:cyclic:update-virtual-channels-state', $client);
        $em = $this->getEntityManager();
        $channels = $em->getRepository(IODeviceChannel::class)->findAll();
        // Even an explicit mixed list must be safe, not only the cyclic query.
        self::getContainer()->get(VirtualChannelStateUpdater::class)->updateChannels($channels);
        $this->assertSame(0, $em->getRepository(ChannelValue::class)->count([]));
        $em->clear();
        $this->assertNull($em->find(IODevice::class, $charger['iodeviceId'])->getLastConnected());
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::OPEN_WEATHER,
            'virtualChannelConfig' => ['cityId' => 1, 'weatherField' => 'temp'],
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $source = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('DATA_SOURCE', $source['virtualType']);
        $this->assertNotSame($charger['iodeviceId'], $source['iodeviceId']);
        $sources = self::getContainer()->get(IODeviceChannelRepository::class)->findDataSources();
        $this->assertCount(1, $sources);
        $this->assertSame($source['id'], $sources[0]->getId());
        $client->apiRequestV3('GET', '/api/users/current?include=relationsCount');
        $this->assertStatusCode(200, $client->getResponse());
        $counts = json_decode($client->getResponse()->getContent(), true)['relationsCount'];
        $this->assertSame(1, $counts['virtualChannels']);
        $this->assertSame(3, $counts['channels']);
    }

    public function testIsolationAndKeyReplacement(): void {
        $other = $this->createConfirmedUser('other@supla.org');
        $otherLocation = $this->createLocation($other);
        $client = $this->createAuthenticatedClient($this->user);
        $created = $this->createCharger($client);
        $client->apiRequestV3('POST', '/api/integrations/ocpp', ['name' => 'Wrong owner', 'locationId' => $otherLocation->getId()]);
        $this->assertStatusCode(404, $client->getResponse());
        $client = $this->createAuthenticatedClient($other);
        $client->apiRequestV3('GET', '/api/integrations/ocpp');
        $this->assertSame([], json_decode($client->getResponse()->getContent(), true)['stations']);
        $client->apiRequestV3('POST', '/api/integrations/ocpp/' . $created['id'] . '/authorization-key', []);
        $this->assertStatusCode(404, $client->getResponse());
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/integrations/ocpp/' . $created['id'] . '/authorization-key', []);
        $this->assertStatusCode(200, $client->getResponse());
        $replacement = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotSame($created['authorizationKey'], $replacement['authorizationKey']);
        $this->getEntityManager()->clear();
        $station = $this->getEntityManager()->find(OcppChargingStation::class, $created['id']);
        $this->assertSame(hash('sha512', hex2bin($replacement['authorizationKey'])), $station->getAuthKeyHash());
    }

    public function testDeviceLimitDoesNotLeavePartialRecords(): void {
        EntityUtils::setField($this->user, 'limitIoDev', 0);
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/integrations/ocpp', ['name' => 'Garage', 'locationId' => $this->location->getId()]);
        $this->assertStatusCode(400, $client->getResponse());
        $em = $this->getEntityManager();
        $this->assertSame(0, $em->getRepository(OcppChargingStation::class)->count([]));
        $this->assertSame(0, $em->getRepository(IODevice::class)->count([]));
        $this->assertSame(0, $em->getRepository(IODeviceChannel::class)->count([]));
    }

    public function testGatewayViewExcludesDisabledDevices(): void {
        $created = $this->createCharger($this->createAuthenticatedClient($this->user));
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $visible = fn() => (int)$connection->fetchOne(
            'SELECT COUNT(*) FROM supla_v_ocpp_charging_station WHERE charging_station_id = ?',
            [$created['id']]
        );
        $this->assertSame(1, $visible());
        $device = $em->find(IODevice::class, $created['iodeviceId']);
        EntityUtils::setField($device, 'enabled', false);
        $this->flush();
        $this->assertSame(0, $visible());
        EntityUtils::setField($device, 'enabled', true);
        $this->flush();
        $this->assertSame(1, $visible());
    }

    public function testWholeDeviceDeletionRemovesCredentialsButSingleChannelDeletionIsForbidden(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $created = $this->createCharger($client);
        $device = $this->getEntityManager()->find(IODevice::class, $created['iodeviceId']);
        $client->apiRequestV3('DELETE', '/api/channels/' . $device->getChannels()->first()->getId());
        $this->assertStatusCode(403, $client->getResponse());
        $client->apiRequestV3('DELETE', '/api/iodevices/' . $created['iodeviceId'] . '?safe=1');
        $this->assertStatusCode(204, $client->getResponse());
        $em = $this->getEntityManager();
        $this->assertSame(0, $em->getRepository(OcppChargingStation::class)->count([]));
        $this->assertSame(0, $em->getRepository(IODeviceChannel::class)->count([]));
    }

    public function testReadOnlyTokenCannotCreateOrReplaceCredentials(): void {
        $client = $this->createAuthenticatedClient($this->user);
        $created = $this->createCharger($client);
        $token = $this->getEntityManager()->getRepository(AccessToken::class)->findOneBy(['user' => $this->user->getId()]);
        EntityUtils::setField($token, 'scope', 'iodevices_r channels_r');
        $this->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/integrations/ocpp');
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV3('POST', '/api/integrations/ocpp', ['name' => 'Garage', 'locationId' => $this->location->getId()]);
        $this->assertStatusCode(403, $client->getResponse());
        $client->apiRequestV3('POST', '/api/integrations/ocpp/' . $created['id'] . '/authorization-key', []);
        $this->assertStatusCode(403, $client->getResponse());
    }
}

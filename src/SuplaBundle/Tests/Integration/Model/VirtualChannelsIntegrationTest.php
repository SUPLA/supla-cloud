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

namespace SuplaBundle\Tests\Integration\Model;

use SuplaBundle\Entity\Main\ChannelValue;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\SuplaAssertions;

/** @small */
class VirtualChannelsIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use SuplaAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->createLocation($this->user);
    }

    public function testFetchingOpenWeatherCities() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/integrations/openweather/cities');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(20, $content);
    }

    public function testCreatingVirtualChannelTemp() {
        SuplaAutodiscoverMock::mockResponse('weather-data', [
            [
                'id' => 1,
                'fetchedAt' => (new \DateTime())->format(\DateTime::ATOM),
                'weather' => ['temp' => 22.2, 'humidity' => 66],
            ],
        ], 200, 'POST');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::OPEN_WEATHER,
            'virtualChannelConfig' => [
                'cityId' => 1,
                'weatherField' => 'temp',
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertEquals(ChannelFunction::THERMOMETER, $content['functionId']);
        $this->assertArrayHasKey('virtualChannelConfig', $content['config']);
        $this->assertEquals(VirtualChannelType::OPEN_WEATHER, $content['config']['virtualChannelConfig']['type']);
        $this->assertEquals(1, $content['config']['virtualChannelConfig']['cityId']);
        $this->assertEquals('temp', $content['config']['virtualChannelConfig']['weatherField']);
        $this->assertEquals(1, $this->getEntityManager()->getRepository(IODevice::class)->count([]));
        return $content['id'];
    }

    public function testCreatingVirtualChannelWind() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::OPEN_WEATHER,
            'virtualChannelConfig' => [
                'cityId' => 1,
                'weatherField' => 'windSpeed',
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertEquals(ChannelFunction::WINDSENSOR, $content['functionId']);
        $this->assertArrayHasKey('virtualChannelConfig', $content['config']);
        $this->assertEquals(VirtualChannelType::OPEN_WEATHER, $content['config']['virtualChannelConfig']['type']);
        $this->assertEquals(1, $content['config']['virtualChannelConfig']['cityId']);
        $this->assertEquals('windSpeed', $content['config']['virtualChannelConfig']['weatherField']);
        $this->assertEquals(1, $this->getEntityManager()->getRepository(IODevice::class)->count([]));
        $this->assertSuplaCommandExecuted(sprintf('USER-ON-CHANNEL-ADDED:1,1,%d', $content['id']));
        return $content['id'];
    }

    public function testCreatingVirtualChannelAir() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::OPEN_WEATHER,
            'virtualChannelConfig' => [
                'cityId' => 2,
                'weatherField' => 'airCo',
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertEquals(ChannelFunction::GENERAL_PURPOSE_MEASUREMENT, $content['functionId']);
        $this->assertArrayHasKey('virtualChannelConfig', $content['config']);
        $this->assertEquals(VirtualChannelType::OPEN_WEATHER, $content['config']['virtualChannelConfig']['type']);
        $this->assertEquals(2, $content['config']['virtualChannelConfig']['cityId']);
        $this->assertEquals('airCo', $content['config']['virtualChannelConfig']['weatherField']);
        $this->assertEquals(1, $this->getEntityManager()->getRepository(IODevice::class)->count([]));
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $config = $channelParamConfigTranslator->getConfig($this->freshChannelById($content['id']));
        $this->assertArrayNotHasKey('waitingForConfigInit', $config);
        $this->assertArrayHasKey('valueMultiplier', $config);
        $this->assertEquals(1, $config['valueMultiplier']);
    }

    /** @depends testCreatingVirtualChannelTemp */
    public function testGettingVirtualChannelState(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', "/api/channels/{$channelId}?include=state");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('state', $body);
        $this->assertNotNull($body['state']['temperature']);
    }

    /** @depends testCreatingVirtualChannelTemp */
    public function testSavesChannelsValueInDatabase(int $channelId) {
        $chValue = $this->getEntityManager()->getRepository(ChannelValue::class)->findOneBy(['channel' => $channelId]);
        $this->assertNotNull($chValue);
        $this->assertEquals(22.2, current(unpack('d', $chValue->getValue())));
    }

    public function testCreatingVirtualChannelRain() {
        SuplaAutodiscoverMock::mockResponse('weather-data', [
            [
                'id' => 1,
                'fetchedAt' => (new \DateTime())->format(\DateTime::ATOM),
                'weather' => ['rainMmh' => 22.3],
            ],
        ], 200, 'POST');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::OPEN_WEATHER,
            'virtualChannelConfig' => [
                'cityId' => 1,
                'weatherField' => 'rainMmh',
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertEquals(ChannelFunction::RAINSENSOR, $content['functionId']);
        $this->assertArrayHasKey('virtualChannelConfig', $content['config']);
        $this->assertEquals(VirtualChannelType::OPEN_WEATHER, $content['config']['virtualChannelConfig']['type']);
        $this->assertEquals(1, $content['config']['virtualChannelConfig']['cityId']);
        $this->assertEquals('rainMmh', $content['config']['virtualChannelConfig']['weatherField']);
        $this->assertEquals(1, $this->getEntityManager()->getRepository(IODevice::class)->count([]));
        $chValue = $this->getEntityManager()->getRepository(ChannelValue::class)->findOneBy(['channel' => $content['id']]);
        $this->assertNotNull($chValue);
        $this->assertEquals(22300, current(unpack('d', $chValue->getValue()))); // mult * 1000
    }

    public function testCreatingVirtualChannelWithUnavailableData() {
        SuplaAutodiscoverMock::mockResponse('weather-data', [
            [
                'id' => 1,
                'fetchedAt' => (new \DateTime())->format(\DateTime::ATOM),
                'weather' => ['temp' => 22.2, 'humidity' => 66],
            ],
        ], 200, 'POST');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::OPEN_WEATHER,
            'virtualChannelConfig' => [
                'cityId' => 1,
                'weatherField' => 'clouds',
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertEquals(ChannelFunction::GENERAL_PURPOSE_MEASUREMENT, $content['functionId']);
        $this->assertArrayHasKey('virtualChannelConfig', $content['config']);
        $this->assertEquals(VirtualChannelType::OPEN_WEATHER, $content['config']['virtualChannelConfig']['type']);
        $this->assertEquals(1, $content['config']['virtualChannelConfig']['cityId']);
        $this->assertEquals('clouds', $content['config']['virtualChannelConfig']['weatherField']);
        $this->assertEquals(1, $this->getEntityManager()->getRepository(IODevice::class)->count([]));
        $chValue = $this->getEntityManager()->getRepository(ChannelValue::class)->findOneBy(['channel' => $content['id']]);
        $this->assertNotNull($chValue);
        $this->assertNan(current(unpack('d', $chValue->getValue())));
    }

    public function testCreatingVirtualChannelTempHum() {
        $client = $this->createAuthenticatedClient($this->user);
        SuplaAutodiscoverMock::mockResponse('weather-data', [
            [
                'id' => 1,
                'fetchedAt' => (new \DateTime())->format(\DateTime::ATOM),
                'weather' => ['temp' => 22.2, 'humidity' => 66],
            ],
        ], 200, 'POST');
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::OPEN_WEATHER,
            'virtualChannelConfig' => [
                'cityId' => 1,
                'weatherField' => 'tempHumidity',
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertEquals(ChannelFunction::HUMIDITYANDTEMPERATURE, $content['functionId']);
        $this->assertArrayHasKey('virtualChannelConfig', $content['config']);
        $chValue = $this->getEntityManager()->getRepository(ChannelValue::class)->findOneBy(['channel' => $content['id']]);
        $this->assertNotNull($chValue);
        ['t' => $temp, 'h' => $hum] = unpack('lt/lh', $chValue->getValue());
        $this->assertEquals(22.2 * 1000, $temp);
        $this->assertEquals(66 * 1000, $hum);
    }
}

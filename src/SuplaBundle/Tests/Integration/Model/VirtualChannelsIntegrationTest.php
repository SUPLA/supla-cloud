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
        $this->assertCount(4, $content);
    }

    public function testCreatingVirtualChannelTemp() {
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
        return $content['id'];
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
        $this->assertGreaterThan(0, $body['state']['temperature']);
    }

    /** @depends testCreatingVirtualChannelTemp */
    public function testSavesChannelsValueInDatabase(int $channelId) {
        $chValue = $this->getEntityManager()->getRepository(ChannelValue::class)->findOneBy(['channel' => $channelId]);
        $this->assertNotNull($chValue);
        $this->assertGreaterThan(0, $chValue->getValue());
    }
}

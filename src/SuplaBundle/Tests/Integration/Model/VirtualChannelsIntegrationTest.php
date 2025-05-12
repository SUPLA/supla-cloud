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

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
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

    public function testCreatingVirtualChannel() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('POST', '/api/channels', [
            'virtualChannelType' => VirtualChannelType::OPEN_WEATHER,
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertArrayHasKey('virtualChannelType', $content['config']);
        $this->assertEquals(VirtualChannelType::OPEN_WEATHER, $content['config']['virtualChannelType']);
        return $content['id'];
    }

    /** @depends testCreatingVirtualChannel */
    public function testSettingChannelConfiguration(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $client->apiRequestV3('PUT', '/api/channels/' . $channelId, [
            'config' => [
                'cityId' => 1,
                'field' => 'temp',
            ],
            'configBefore' => $channelParamConfigTranslator->getConfig($this->freshChannelById($channelId)),
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        return $channelId;
    }

    /** @depends testSettingChannelConfiguration */
    public function testGettingVirtualChannelState(int $channelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', "/api/channels/{$channelId}?include=state");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('state', $body);
        $this->assertNotNull($body['state']['value']);
        $this->assertGreaterThan(0, $body['state']['value']);
    }
}

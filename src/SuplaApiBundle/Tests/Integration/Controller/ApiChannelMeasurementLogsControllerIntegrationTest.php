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

namespace SuplaApiBundle\Tests\Integration\Controller;

use SuplaApiBundle\Entity\EntityUtils;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\TemperatureLogItem;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;

class ApiChannelMeasurementLogsControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
        ]);
        foreach ([21, 22, 23] as $temperature) {
            $logItem = new TemperatureLogItem();
            EntityUtils::setField($logItem, 'channel_id', 2);
            EntityUtils::setField($logItem, 'date', new \DateTime());
            EntityUtils::setField($logItem, 'temperature', $temperature);
            $this->getEntityManager()->persist($logItem);
        }
        $this->getEntityManager()->flush();
    }

    public function testGettingMeasurementLogsCount() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/2/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $this->assertTrue($response->headers->has('X-Total-Count'));
        $this->assertEquals(3, $response->headers->get('X-Total-Count'));
    }

    public function testGettingMeasurementLogsCountObsolete() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequest('GET', '/api/channels/2/temperature-log-count');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(3, $content['count']);
    }

    public function testGettingMeasurementLogs() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/2/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(3, $content);
        $temperaturesInOrder = [23, 22, 21];
        $this->assertEquals($temperaturesInOrder, array_map('intval', array_column($content, 'temperature')));
    }

    public function testGettingMeasurementLogsWithOffset() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/2/measurement-logs?offset=1&limit=1');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals(22, intval($content[0]['temperature']));
    }

    public function testGettingMeasurementLogsOfUnsupportedChannel() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channels/1/measurement-logs');
        $response = $client->getResponse();
        $this->assertStatusCode('400', $response);
    }
}

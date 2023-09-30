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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class HvacIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var IODeviceChannel */
    private $hvacChannel;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::HUMIDITYANDTEMPSENSOR, ChannelFunction::HUMIDITYANDTEMPERATURE],
            [ChannelType::HVAC, ChannelFunction::HVAC_THERMOSTAT],
        ]);
        $this->hvacChannel = $this->device->getChannels()[2];
    }

    public function testWaitingForConfigInit() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(['waitingForConfigInit' => true], $content['config']);
    }

    public function testConfigInitialized() {
        $client = $this->createAuthenticatedClient($this->user);
        $this->hvacChannel = $this->freshEntity($this->hvacChannel);
        $this->hvacChannel->setUserConfigValue('subfunction', 'HEAT');
        $this->hvacChannel->setUserConfigValue('mainThermometerChannelNo', 2);
        $this->getEntityManager()->persist($this->hvacChannel);
        $this->getEntityManager()->flush();
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('waitingForConfigInit', $content['config']);
        $this->assertEquals('HEAT', $content['config']['subfunction']);
        $this->assertNull($content['config']['mainThermometerChannelId']);
    }

    /** @depends testConfigInitialized */
    public function testClearingSubfunctionOnFunctionChange() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'functionId' => ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals([], $content['config']);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'functionId' => ChannelFunction::HVAC_THERMOSTAT,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(['waitingForConfigInit' => true], $content['config']);
        $this->hvacChannel = $this->freshEntity($this->hvacChannel);
        $this->hvacChannel->setUserConfigValue('subfunction', 'HEAT');
        $this->getEntityManager()->persist($this->hvacChannel);
        $this->getEntityManager()->flush();
    }

    /** @depends testConfigInitialized */
    public function testSettingMainThermometer() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => [
                'mainThermometerChannelId' => $this->device->getChannels()[0]->getId(),
            ],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('HEAT', $content['config']['subfunction']);
        $this->assertEquals($this->device->getChannels()[0]->getId(), $content['config']['mainThermometerChannelId']);
        $hvacChannel = $this->freshEntity($this->hvacChannel);
        $this->assertNull($hvacChannel->getUserConfigValue('mainThermometerChannelId'));
        $this->assertEquals(0, $hvacChannel->getUserConfigValue('mainThermometerChannelNo'));
    }
}

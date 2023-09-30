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
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaDeveloperBundle\DataFixtures\ORM\DevicesFixture;

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
        $this->device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceHvac($location);
        $this->hvacChannel = $this->device->getChannels()[2];
    }

    public function testFixtureDeviceConfig() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $config = $content['config'];
        $this->assertArrayNotHasKey('waitingForConfigInit', $config);
        $this->assertEquals('HEAT', $config['subfunction']);
        $this->assertNull($config['mainThermometerChannelId']);
        $this->assertNotNull($config['weeklySchedule']);
        $this->assertCount(24 * 4 * 7, $config['weeklySchedule']['quarters']);
        $this->assertCount(4, $config['weeklySchedule']['programSettings']);
        $this->assertEquals(0, $config['weeklySchedule']['programSettings'][2]['setpointTemperatureMin']);
        $this->assertEquals(21, $config['weeklySchedule']['programSettings'][2]['setpointTemperatureMax']);
    }

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

    public function testSettingAuxThermometer() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => [
                'auxThermometerChannelId' => $this->device->getChannels()[1]->getId(),
            ],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('HEAT', $content['config']['subfunction']);
        $this->assertEquals($this->device->getChannels()[1]->getId(), $content['config']['auxThermometerChannelId']);
        $hvacChannel = $this->freshEntity($this->hvacChannel);
        $this->assertNull($hvacChannel->getUserConfigValue('auxThermometerChannelId'));
        $this->assertEquals(1, $hvacChannel->getUserConfigValue('auxThermometerChannelNo'));
    }

    public function testSettingWeeklySchedule() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $week = $channelConfig['weeklySchedule'];
        $this->assertNotEquals(2, $week['quarters'][123]);
        $week['quarters'][123] = 2;
        $week['programSettings'][2]['setpointTemperatureMin'] = 10;
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => [
                'weeklySchedule' => $week,
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(2, $content['config']['weeklySchedule']['quarters'][123]);
        $this->assertEquals(10, $content['config']['weeklySchedule']['programSettings'][2]['setpointTemperatureMin']);
        $this->hvacChannel = $this->freshEntity($this->hvacChannel);
        $this->assertEquals(1000, $this->hvacChannel->getUserConfigValue('weeklySchedule')['programSettings'][2]['setpointTemperatureMin']);
    }

    public function testSettingWeeklyScheduleWithIncompleteQuarters() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $week = $channelConfig['weeklySchedule'];
        $week['quarters'] = [1, 1, 1, 1, 1, 1, 1, 2];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => [
                'weeklySchedule' => $week,
            ],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testSettingWeeklyScheduleWithInvalidProgram() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $week = $channelConfig['weeklySchedule'];
        $week['quarters'][123] = 8;
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => [
                'weeklySchedule' => $week,
            ],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testSettingAltWeeklySchedule() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $week = $channelConfig['altWeeklySchedule'];
        $this->assertNotEquals(2, $week['quarters'][125]);
        $week['quarters'][125] = 2;
        $week['programSettings'][3]['setpointTemperatureMin'] = 10;
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => ['altWeeklySchedule' => $week],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertNotEquals(2, $content['config']['weeklySchedule']['quarters'][125]);
        $this->assertEquals(2, $content['config']['altWeeklySchedule']['quarters'][125]);
        $this->assertEquals(10, $content['config']['altWeeklySchedule']['programSettings'][3]['setpointTemperatureMin']);
    }

    public function testWaitingForConfigInit() {
        $deviceWithoutConfig = $this->createDevice($this->device->getLocation(), [
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::HUMIDITYANDTEMPSENSOR, ChannelFunction::HUMIDITYANDTEMPERATURE],
            [ChannelType::HVAC, ChannelFunction::HVAC_THERMOSTAT],
        ]);
        $hvacChannel = $deviceWithoutConfig->getChannels()[2];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels/' . $hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(['waitingForConfigInit' => true], $content['config']);
        return $hvacChannel;
    }

    /** @depends testWaitingForConfigInit */
    public function testConfigInitialized(IODeviceChannel $hvacChannel) {
        $client = $this->createAuthenticatedClient($this->user);
        $hvacChannel = $this->freshEntity($hvacChannel);
        $hvacChannel->setUserConfigValue('subfunction', 'HEAT');
        $hvacChannel->setUserConfigValue('mainThermometerChannelNo', 2);
        $this->getEntityManager()->persist($hvacChannel);
        $this->getEntityManager()->flush();
        $client->apiRequestV24('GET', '/api/channels/' . $hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('waitingForConfigInit', $content['config']);
        $this->assertEquals('HEAT', $content['config']['subfunction']);
        $this->assertNull($content['config']['mainThermometerChannelId']);
        return $hvacChannel;
    }

    /** @depends testConfigInitialized */
    public function testCantSetWeeklyScheduleIfNoneProvided(IODeviceChannel $hvacChannel) {
        $client = $this->createAuthenticatedClient($this->user);
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $client->apiRequestV24('PUT', '/api/channels/' . $hvacChannel->getId(), [
            'config' => ['weeklySchedule' => $channelConfig['weeklySchedule']],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    /** @depends testConfigInitialized */
    public function testClearingSubfunctionOnFunctionChange(IODeviceChannel $hvacChannel) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $hvacChannel->getId(), [
            'functionId' => ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals([], $content['config']);
        $client->apiRequestV24('PUT', '/api/channels/' . $hvacChannel->getId(), [
            'functionId' => ChannelFunction::HVAC_THERMOSTAT,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(['waitingForConfigInit' => true], $content['config']);
        $hvacChannel = $this->freshEntity($hvacChannel);
        $hvacChannel->setUserConfigValue('subfunction', 'HEAT');
        $this->getEntityManager()->persist($hvacChannel);
        $this->getEntityManager()->flush();
    }
}

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
use SuplaBundle\Enums\ChannelConfigChangeScope;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\SuplaAssertions;
use SuplaDeveloperBundle\DataFixtures\ORM\DevicesFixture;

/** @small */
class GeneralPurposeMeasurementIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use SuplaAssertions;

    private ?User $user;
    private ?IODevice $device;
    private ?int $measurementChannelId;
    private ?int $meterChannelId;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceGeneralPurposeMeasurement($location);
        $this->flush();
        $this->measurementChannelId = $this->device->getChannels()[0]->getId();
        $this->meterChannelId = $this->device->getChannels()[1]->getId();
    }

    public function testGettingConfigMeasurement() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels/' . $this->measurementChannelId);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $config = $content['config'];
        $this->assertEquals(0.012, $config['valueDivider']);
        $this->assertEquals(0.034, $config['valueMultiplier']);
        $this->assertEquals(0.056, $config['valueAdded']);
        $this->assertEquals(2, $config['valuePrecision']);
        $this->assertEquals('ABCD', $config['unitBeforeValue']);
        $this->assertEquals('EFGH', $config['unitAfterValue']);
        $this->assertTrue($config['keepHistory']);
        $this->assertFalse($config['noSpaceAfterValue']);
        $this->assertEquals('CANDLE', $config['chartType']);
        $this->assertArrayNotHasKey('fillMissingData', $config);
        $this->assertEquals(0.078, $config['defaults']['valueDivider']);
        $this->assertEquals(0.910, $config['defaults']['valueMultiplier']);
        $this->assertEquals(1.112, $config['defaults']['valueAdded']);
        $this->assertEquals(4, $config['defaults']['valuePrecision']);
        $this->assertEquals('XCVB', $config['defaults']['unitBeforeValue']);
        $this->assertEquals('GHJK', $config['defaults']['unitAfterValue']);
    }

    public function testGettingConfigMeter() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels/' . $this->meterChannelId);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $config = $content['config'];
        $this->assertEquals(0.056, $config['valueAdded']);
        $this->assertEquals('EFGH', $config['unitAfterValue']);
        $this->assertTrue($config['keepHistory']);
        $this->assertTrue($config['includeValueAddedInHistory']);
        $this->assertTrue($config['fillMissingData']);
        $this->assertEquals('ALWAYS_INCREMENT', $config['counterType']);
        $this->assertEquals(0.910, $config['defaults']['valueMultiplier']);
    }

    public function testSettingConfig() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->measurementChannelId, ['config' => [
            'valueDivider' => 12.34,
            'valueMultiplier' => -11.223,
            'valueAdded' => null,
            'valuePrecision' => 2,
            'unitBeforeValue' => '',
            'unitAfterValue' => 'metrów ³',
            'keepHistory' => false,
            'noSpaceAfterValue' => true,
            'chartType' => 'BAR',
        ]]);
        $channel = $this->freshChannelById($this->measurementChannelId);
        $this->assertEquals(12340, $channel->getUserConfigValue('valueDivider'));
        $this->assertEquals(0, $channel->getUserConfigValue('valueAdded'));
        $this->assertEquals(2, $channel->getUserConfigValue('valuePrecision'));
        $this->assertEquals('', $channel->getUserConfigValue('unitBeforeValue'));
        $this->assertEquals('metrów ³', $channel->getUserConfigValue('unitAfterValue'));
        $this->assertFalse($channel->getUserConfigValue('keepHistory'));
        $this->assertEquals('BAR', $channel->getUserConfigValue('chartType'));
        $this->assertTrue($channel->getUserConfigValue('noSpaceAfterValue'));
        $this->assertSuplaCommandNotExecuted('USER-RECONNECT:1');
    }

    public function testClearingConfigOnFunctionChange() {
        $previousConfig = $this->freshChannelById($this->measurementChannelId)->getUserConfig();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->measurementChannelId, ['functionId' => ChannelFunction::NONE]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSuplaCommandExecuted(sprintf(
            'USER-ON-CHANNEL-CONFIG-CHANGED:1,%d,%d,9000,0,%d',
            $this->device->getId(),
            $this->measurementChannelId,
            ChannelConfigChangeScope::CHANNEL_FUNCTION
        ));
        $client->apiRequestV24('PUT', '/api/channels/' . $this->measurementChannelId, [
            'functionId' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $this->measurementChannelId);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('waitingForConfigInit', $content['config']);
        $this->assertTrue($content['config']['waitingForConfigInit']);
        $channel = $this->freshChannelById($this->measurementChannelId);
        $channel->setUserConfig($previousConfig);
        $this->persist($channel);
        $client->apiRequestV24('GET', '/api/channels/' . $this->measurementChannelId);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('waitingForConfigInit', $content['config']);
    }

    /** @dataProvider invalidConfigRequests */
    public function testSettingInvalidConfigs(array $invalidConfig) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->measurementChannelId, ['config' => $invalidConfig]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function invalidConfigRequests() {
        return [
            [['valueDivider' => 3_000_000]],
            [['valueDivider' => [1]]],
            [['valueMultiplier' => -3_000_000]],
            [['valueMultiplier' => 'abc']],
            [['valueAdded' => 200_000_000]],
            [['valuePrecision' => -1]],
            [['valuePrecision' => 11]],
            [['valuePrecision' => '2']],
            [['valuePrecision' => 2.5]],
            [['unitAfterValue' => 21]],
            [['unitBeforeValue' => '🙉🙉🙉🙉']],
            [['unitBeforeValue' => 'very very long unit']],
            [['chartType' => 'UNICORN']],
        ];
    }

    public function testResettingCounters() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', "/api/channels/{$this->meterChannelId}/settings", [
            'action' => 'resetCounters',
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSuplaCommandExecuted("RESET-COUNTERS:1,{$this->device->getId()},{$this->meterChannelId}");
    }

    public function testGettingState() {
        SuplaServerMock::mockResponse('GET-GPM-VALUE', "VALUE:2.3\n");
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('GET', "/api/channels/{$this->meterChannelId}?include=state");
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSuplaCommandExecuted("GET-GPM-VALUE:1,{$this->device->getId()},{$this->meterChannelId}");
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(['connected' => true, 'connectedCode' => 'CONNECTED', 'value' => 2.3], $content['state']);
    }
}

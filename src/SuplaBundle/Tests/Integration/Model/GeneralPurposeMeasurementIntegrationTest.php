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

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceGeneralPurposeMeasurement($location);
        $this->flush();
        $this->measurementChannelId = $this->device->getChannels()[0]->getId();
    }

    public function testGettingConfig() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels/' . $this->measurementChannelId);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $config = $content['config'];
        $this->assertEquals(0.012, $config['valueDivider']);
        $this->assertEquals(0.034, $config['valueMultiplier']);
        $this->assertEquals(0.056, $config['valueAdded']);
        $this->assertEquals(5, $config['valuePrecision']);
        $this->assertEquals('ABCD', $config['unitBeforeValue']);
        $this->assertEquals('EFGH', $config['unitAfterValue']);
        $this->assertTrue($config['keepHistory']);
        $this->assertEquals('CANDLE', $config['chartType']);
        $this->assertEquals(0.078, $config['defaults']['valueDivider']);
        $this->assertEquals(0.910, $config['defaults']['valueMultiplier']);
        $this->assertEquals(1.112, $config['defaults']['valueAdded']);
        $this->assertEquals(9, $config['defaults']['valuePrecision']);
        $this->assertEquals('XCVB', $config['defaults']['unitBeforeValue']);
        $this->assertEquals('GHJK', $config['defaults']['unitAfterValue']);
    }

    public function testSettingConfig() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->measurementChannelId, ['config' => [
            'valueDivider' => 12.34,
            'valueMultiplier' => -11.223,
            'valueAdded' => 100.1,
            'valuePrecision' => 2,
            'unitBeforeValue' => '$$$',
            'unitAfterValue' => 'metrów ³',
            'keepHistory' => false,
            'chartType' => 'BAR',
        ]]);
        $channel = $this->freshChannelById($this->measurementChannelId);
        $this->assertEquals(12340, $channel->getUserConfigValue('valueDivider'));
        $this->assertEquals(100100, $channel->getUserConfigValue('valueAdded'));
        $this->assertEquals(2, $channel->getUserConfigValue('valuePrecision'));
        $this->assertEquals('$$$', $channel->getUserConfigValue('unitBeforeValue'));
        $this->assertEquals('metrów ³', $channel->getUserConfigValue('unitAfterValue'));
        $this->assertFalse($channel->getUserConfigValue('keepHistory'));
        $this->assertEquals('BAR', $channel->getUserConfigValue('chartType'));
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
            [['valueDivider' => 999999]],
            [['valueDivider' => [1]]],
            [['valueMultiplier' => 999999]],
            [['valueMultiplier' => 'abc']],
            [['valueAdded' => 200000000]],
            [['valuePrecision' => -1]],
            [['valuePrecision' => 11]],
            [['valuePrecision' => '2']],
            [['valuePrecision' => 2.5]],
            [['unitAfterValue' => 21]],
            [['unitBeforeValue' => 'very very long unit']],
            [['chartType' => 'UNICORN']],
        ];
    }
}

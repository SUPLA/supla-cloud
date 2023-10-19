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
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\SuplaAssertions;
use SuplaDeveloperBundle\DataFixtures\ORM\DevicesFixture;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @small */
class HvacIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use SuplaAssertions;

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

    /** @dataProvider hvacChannelConfigs */
    public function testFixtureDeviceConfig(int $hvacChannelIndex, callable $configValidator) {
        $client = $this->createAuthenticatedClient($this->user);
        $hvacChannel = $this->device->getChannels()[$hvacChannelIndex];
        $client->apiRequestV24('GET', '/api/channels/' . $hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $config = $content['config'];
        $this->assertArrayNotHasKey('waitingForConfigInit', $config);
        $this->assertNotNull($config['weeklySchedule']);
        $this->assertCount(24 * 4 * 7, $config['weeklySchedule']['quarters']);
        $this->assertCount(4, $config['weeklySchedule']['programSettings']);
        $configValidator($config);
    }

    /** @dataProvider hvacChannelConfigs */
    public function testFixtureConfigsCanBeSavedWithoutModifications(int $hvacChannelIndex) {
        $client = $this->createAuthenticatedClient($this->user);
        $hvacChannel = $this->device->getChannels()[$hvacChannelIndex];
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($hvacChannel);
        $client->apiRequestV24('PUT', '/api/channels/' . $hvacChannel->getId(), ['config' => $channelConfig]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(json_decode(json_encode($channelConfig), true), $content['config']);
    }

    public function hvacChannelConfigs() {
        return [
            'THERMOSTAT' => [
                2,
                function (array $config) {
                    $this->assertEquals('HEAT', $config['subfunction']);
                    $this->assertEquals(2, $config['mainThermometerChannelId']);
                    $this->assertEquals(21, $config['weeklySchedule']['programSettings'][2]['setpointTemperatureHeat']);
                    $this->assertNull($config['weeklySchedule']['programSettings'][2]['setpointTemperatureCool']);
                    $this->assertEquals(21, $config['altWeeklySchedule']['programSettings'][2]['setpointTemperatureCool']);
                    $this->assertEquals('NOT_SET', $config['auxThermometerType']);
                    $this->assertFalse($config['antiFreezeAndOverheatProtectionEnabled']);
                    $this->assertFalse($config['temperatureSetpointChangeSwitchesToManualMode']);
                    $this->assertCount(2, $config['availableAlgorithms']);
                    $this->assertEquals(0, $config['minOnTimeS']);
                    $this->assertEquals(0, $config['outputValueOnError']);
                    $this->assertNull($config['binarySensorChannelId']);
                    $this->assertCount(5, $config['temperatures']);
                },
            ],
            'THERMOSTAT_AUTO' => [
                3,
                function (array $config) {
                    $this->assertArrayNotHasKey('subfunction', $config);
                    $this->assertArrayNotHasKey('altWeeklySchedule', $config);
                    $this->assertEquals('HEAT', $config['weeklySchedule']['programSettings'][1]['mode']);
                    $this->assertEquals('COOL', $config['weeklySchedule']['programSettings'][2]['mode']);
                    $this->assertEquals('AUTO', $config['weeklySchedule']['programSettings'][3]['mode']);
                    $this->assertEquals(21, $config['weeklySchedule']['programSettings'][1]['setpointTemperatureHeat']);
                    $this->assertEquals(2, $config['auxThermometerChannelId']);
                    $this->assertEquals('FLOOR', $config['auxThermometerType']);
                    $this->assertTrue($config['antiFreezeAndOverheatProtectionEnabled']);
                    $this->assertTrue($config['temperatureSetpointChangeSwitchesToManualMode']);
                    $this->assertCount(1, $config['availableAlgorithms']);
                    $this->assertEquals(60, $config['minOnTimeS']);
                    $this->assertEquals(120, $config['minOffTimeS']);
                    $this->assertEquals(42, $config['outputValueOnError']);
                    $this->assertCount(10, $config['temperatures']);
                    $this->assertCount(8, $config['temperatureConstraints']);
                },
            ],
            'DOMESTIC_HOT_WATER' => [
                4,
                function (array $config) {
                    $this->assertArrayNotHasKey('subfunction', $config);
                    $this->assertArrayNotHasKey('altWeeklySchedule', $config);
                    $this->assertEquals('HEAT', $config['weeklySchedule']['programSettings'][1]['mode']);
                    $this->assertEquals('HEAT', $config['weeklySchedule']['programSettings'][2]['mode']);
                    $this->assertEquals(24, $config['weeklySchedule']['programSettings'][1]['setpointTemperatureHeat']);
                    $this->assertEquals('ON_OFF_SETPOINT_AT_MOST', $config['usedAlgorithm']);
                    $this->assertEquals(6, $config['binarySensorChannelId']);
                    $this->assertCount(5, $config['temperatures']);
                    $this->assertCount(0, array_filter($config['temperatures']));
                },
            ],
        ];
    }

    public function testSupportedFunctions() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId() . '?include=supportedFunctions');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content['supportedFunctions']);
        $this->assertEquals(
            [ChannelFunction::HVAC_THERMOSTAT, ChannelFunction::HVAC_DOMESTIC_HOT_WATER],
            array_column($content['supportedFunctions'], 'id')
        );
        $client->apiRequestV24('GET', '/api/channels/' . $this->device->getChannels()[4]->getId() . '?include=supportedFunctions');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(4, $content['supportedFunctions']);
    }

    public function testSettingMainThermometer() {
        $client = $this->createAuthenticatedClient($this->user);
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $client->apiRequestV3('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => [
                'mainThermometerChannelId' => $this->device->getChannels()[0]->getId(),
            ],
            'configBefore' => $channelParamConfigTranslator->getConfig($this->hvacChannel),
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
        $this->assertSuplaCommandExecuted('CHANNEL-SETTINGS-CHANGED:1,1,3,' . base64_encode('havac'));
    }

    /** @depends testSettingMainThermometer */
    public function testSendingFullConfigButChangingMainThermometerOnly() {
        SuplaServerMock::reset();
        $client = $this->createAuthenticatedClient($this->user);
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $config = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $configBefore = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $config['mainThermometerChannelId'] = $this->device->getChannels()[1]->getId();
        $client->apiRequestV3('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => $config,
            'configBefore' => $configBefore,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('HEAT', $content['config']['subfunction']);
        $this->assertEquals($this->device->getChannels()[1]->getId(), $content['config']['mainThermometerChannelId']);
        $hvacChannel = $this->freshEntity($this->hvacChannel);
        $this->assertNull($hvacChannel->getUserConfigValue('mainThermometerChannelId'));
        $this->assertEquals(1, $hvacChannel->getUserConfigValue('mainThermometerChannelNo'));
        $this->assertSuplaCommandExecuted('CHANNEL-SETTINGS-CHANGED:1,1,3,' . base64_encode('havac'));
    }

    /** @depends testSendingFullConfigButChangingMainThermometerOnly */
    public function testSettingSubfunction() {
        SuplaServerMock::reset();
        $client = $this->createAuthenticatedClient($this->user);
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $client->apiRequestV3('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => ['subfunction' => 'COOL'],
            'configBefore' => $channelParamConfigTranslator->getConfig($this->hvacChannel),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('COOL', $content['config']['subfunction']);
        $this->assertSuplaCommandExecuted('CHANNEL-SETTINGS-CHANGED:1,1,3,' . base64_encode('subfunction'));
    }

    /** @depends testSettingSubfunction */
    public function testSettingSubfunctionAndThermometer() {
        SuplaServerMock::reset();
        $client = $this->createAuthenticatedClient($this->user);
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $client->apiRequestV3('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => ['subfunction' => 'HEAT', 'mainThermometerChannelId' => $this->device->getChannels()[0]->getId()],
            'configBefore' => $channelParamConfigTranslator->getConfig($this->hvacChannel),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $this->hvacChannel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertSuplaCommandExecuted('CHANNEL-SETTINGS-CHANGED:1,1,3,' . base64_encode('havac,subfunction'));
        $this->assertSuplaCommandNotExecuted('USER-RECONNECT:1');
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

    public function testApiQuartersStartWithMondayAndBackendQuartersStartWithSunday() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channel = $this->device->getChannels()[3];
        $channelConfig = $channelParamConfigTranslator->getConfig($channel);
        $week = $channelConfig['weeklySchedule'];
        // Monday - 1s, rest - 0s
        $week['quarters'] = array_map(
            'intval',
            // MONDAY                           TUE - SUN
            str_split(str_repeat('1', 24 * 4) . str_repeat('0', 24 * 4 * 6))
        );
        $channelParamConfigTranslator->setConfig($channel, ['weeklySchedule' => $week]);
        $quartersForApi = $channelParamConfigTranslator->getConfig($channel)['weeklySchedule']['quarters'];
        $quartersInDb = $channel->getUserConfigValue('weeklySchedule')['quarters'];
        $this->assertEquals($week['quarters'], $quartersForApi);
        $expectedQuartersInDb = array_map(
            'intval',
            // SUNDAY                           MONDAY                    TUE - SAT
            str_split(str_repeat('0', 24 * 4) . str_repeat('1', 24 * 4) . str_repeat('0', 24 * 4 * 5))
        );
        $this->assertEquals($expectedQuartersInDb, $quartersInDb);
    }

    public function testSettingWeeklySchedule() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $week = $channelConfig['weeklySchedule'];
        $this->assertNotEquals(2, $week['quarters'][123]);
        $week['quarters'][123] = 2;
        $week['programSettings'][2]['setpointTemperatureHeat'] = 10;
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
        $this->assertEquals(10, $content['config']['weeklySchedule']['programSettings'][2]['setpointTemperatureHeat']);
        $this->hvacChannel = $this->freshEntity($this->hvacChannel);
        $this->assertEquals(
            1000,
            $this->hvacChannel->getUserConfigValue('weeklySchedule')['programSettings'][2]['setpointTemperatureHeat']
        );
        $this->assertSuplaCommandExecuted('CHANNEL-SETTINGS-CHANGED:1,1,3,' . base64_encode('weekly_schedule'));
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

    public function testSettingWeeklyScheduleWithInvalidProgramInQuarters() {
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

    public function testSettingWeeklyScheduleWithInvalidProgramMode() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $week = $channelConfig['weeklySchedule'];
        $week['programSettings'][2]['mode'] = 'COOL';
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
        $week['programSettings'][3]['setpointTemperatureHeat'] = 5;
        $week['programSettings'][3]['setpointTemperatureCool'] = 10;
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => ['altWeeklySchedule' => $week],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertNotEquals(2, $content['config']['weeklySchedule']['quarters'][125]);
        $this->assertEquals(2, $content['config']['altWeeklySchedule']['quarters'][125]);
        $this->assertEquals(0, $content['config']['altWeeklySchedule']['programSettings'][3]['setpointTemperatureHeat']);
        $this->assertEquals(10, $content['config']['altWeeklySchedule']['programSettings'][3]['setpointTemperatureCool']);
        $this->assertSuplaCommandExecuted('CHANNEL-SETTINGS-CHANGED:1,1,3,' . base64_encode('weekly_schedule'));
    }

    public function testSettingAltWeeklyScheduleWithInvalidProgramMode() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $week = $channelConfig['weeklySchedule'];
        $week['programSettings'][2]['mode'] = 'HEAT';
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), [
            'config' => [
                'altWeeklySchedule' => $week,
            ],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    /** @dataProvider invalidConfigRequests */
    public function testSettingInvalidConfigs(array $invalidConfig) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $this->hvacChannel->getId(), ['config' => $invalidConfig]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function invalidConfigRequests() {
        return [
            [['usedAlgorithm' => 'unicorn']],
            [['auxThermometerType' => 'unicorn']],
            [['auxThermometerChannelId' => 123]],
            [['auxThermometerChannelId' => 3]],
            [['mainThermometerChannelId' => 5]],
            [['binarySensorChannelId' => 3]],
            [['binarySensorChannelId' => 1]],
            [['mainThermometerChannelId' => ['abc']]],
            [['weeklySchedule' => 'abc']],
            [['minOnTimeS' => -5]],
            [['minOffTimeS' => 5000]],
            [['minOnTimeS' => 'abc']],
            [['outputValueOnError' => 'abc']],
            [['outputValueOnError' => 101]],
            [['temperatures' => ['freezeProtection' => 100]]],
            [['temperatures' => ['unknownTemperature' => 10]]],
            [['temperatures' => ['roomMin' => 10]]],
            [['temperatures' => ['auxMinSetpoint' => 10, 'auxMaxSetpoint' => 9]]],
        ];
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
        return $hvacChannel->getId();
    }

    /** @depends testWaitingForConfigInit */
    public function testConfigInitialized(int $hvacChannelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $hvacChannel = $this->freshEntityById(IODeviceChannel::class, $hvacChannelId);
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
        return $hvacChannel->getId();
    }

    /** @depends testConfigInitialized */
    public function testCantSetWeeklyScheduleIfNoneProvided(int $hvacChannelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($this->hvacChannel);
        $client->apiRequestV24('PUT', '/api/channels/' . $hvacChannelId, [
            'config' => ['weeklySchedule' => $channelConfig['weeklySchedule']],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    /** @depends testConfigInitialized */
    public function testClearingConfigOnFunctionChange(int $hvacChannelId) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/' . $hvacChannelId, [
            'functionId' => ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertSuplaCommandExecuted("CHANNEL-SETTINGS-CHANGED:1,2,$hvacChannelId," . base64_encode('function'));
        $client->apiRequestV24('GET', '/api/channels/' . $hvacChannelId);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(['waitingForConfigInit' => true], $content['config']);
        $client->apiRequestV24('PUT', '/api/channels/' . $hvacChannelId, [
            'functionId' => ChannelFunction::HVAC_THERMOSTAT,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $client->apiRequestV24('GET', '/api/channels/' . $hvacChannelId);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(['waitingForConfigInit' => true], $content['config']);
        $hvacChannel = $this->freshEntityById(IODeviceChannel::class, $hvacChannelId);
        $hvacChannel->setUserConfigValue('subfunction', 'HEAT');
        $this->getEntityManager()->persist($hvacChannel);
        $this->getEntityManager()->flush();
    }

    /**
     * @dataProvider hvacActionsProvider
     */
    public function testExecutingActionOnHvac(int $channelId, array $actionRequest, string $expectedCommand) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->request('PATCH', '/api/channels/' . $channelId, [], [], [], json_encode($actionRequest));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains($expectedCommand, $commands, implode(PHP_EOL, $commands));
    }

    public function hvacActionsProvider() {
        // @codingStandardsIgnoreStart
        return [
            [3, ['action' => 'TURN_ON'], 'ACTION-TURN-ON:1,1,3'],
            [4, ['action' => 'TURN_ON'], 'ACTION-TURN-ON:1,1,4'],
            [3, ['action' => 'TURN_OFF'], 'ACTION-TURN-OFF:1,1,3'],
            [4, ['action' => 'TURN_OFF'], 'ACTION-TURN-OFF:1,1,4'],
            [4, ['action' => 'TURN_OFF_WITH_DURATION'], 'ACTION-TURN-OFF:1,1,4'],
            [4, ['action' => 'TURN_OFF_WITH_DURATION', 'durationMs' => 0], 'ACTION-TURN-OFF:1,1,4'],
            [3, ['action' => 'TURN_OFF_WITH_DURATION', 'durationMs' => 100], 'ACTION-TURN-OFF-WITH-DURATION:1,1,3,100'],
            [4, ['action' => 'TURN_OFF_WITH_DURATION', 'durationMs' => 31535000000], 'ACTION-TURN-OFF-WITH-DURATION:1,1,4,31535000000'],
            [3, ['action' => 'HVAC_SWITCH_TO_PROGRAM_MODE'], 'ACTION-HVAC-SWITCH-TO-PROGRAM-MODE:1,1,3'],
            [4, ['action' => 'HVAC_SWITCH_TO_PROGRAM_MODE'], 'ACTION-HVAC-SWITCH-TO-PROGRAM-MODE:1,1,4'],
            [3, ['action' => 'HVAC_SWITCH_TO_MANUAL_MODE'], 'ACTION-HVAC-SWITCH-TO-MANUAL-MODE:1,1,3'],
            [4, ['action' => 'HVAC_SWITCH_TO_MANUAL_MODE'], 'ACTION-HVAC-SWITCH-TO-MANUAL-MODE:1,1,4'],
            [3, ['action' => 'HVAC_SET_TEMPERATURES', 'setpoints' => ['heat' => 22.5]], 'ACTION-HVAC-SET-TEMPERATURES:1,1,3,2250,0,1'],
            [4, ['action' => 'HVAC_SET_TEMPERATURES', 'setpoints' => ['heat' => 22.5]], 'ACTION-HVAC-SET-TEMPERATURES:1,1,4,2250,0,1'],
            [4, ['action' => 'HVAC_SET_TEMPERATURES', 'setpoints' => ['heat' => 22.5]], 'ACTION-HVAC-SET-TEMPERATURES:1,1,4,2250,0,1'],
            [4, ['action' => 'HVAC_SET_TEMPERATURES', 'setpoints' => ['heat' => 22.5]], 'ACTION-HVAC-SET-TEMPERATURES:1,1,4,2250,0,1'],
            [4, ['action' => 'HVAC_SET_TEMPERATURES', 'setpoints' => ['cool' => 21.5]], 'ACTION-HVAC-SET-TEMPERATURES:1,1,4,0,2150,2'],
            [4, ['action' => 'HVAC_SET_TEMPERATURES', 'setpoints' => ['heat' => 22.5, 'cool' => 21.5]], 'ACTION-HVAC-SET-TEMPERATURES:1,1,4,2250,2150,3'],
            [3, ['action' => 'HVAC_SET_PARAMETERS'], 'ACTION-SET-HVAC-PARAMETERS:1,1,3,0,10,0,0,0'],
            [3, ['action' => 'HVAC_SET_PARAMETERS', 'setpoints' => ['heat' => 22.5]], 'ACTION-SET-HVAC-PARAMETERS:1,1,3,0,10,2250,0,1'],
            [3, ['action' => 'HVAC_SET_PARAMETERS', 'setpoints' => ['heat' => 22.5], 'durationMs' => 1200], 'ACTION-SET-HVAC-PARAMETERS:1,1,3,1200,10,2250,0,1'],
            [3, ['action' => 'HVAC_SET_PARAMETERS', 'durationMs' => 1200], 'ACTION-SET-HVAC-PARAMETERS:1,1,3,1200,10,0,0,0'],
            [4, ['action' => 'HVAC_SET_PARAMETERS'], 'ACTION-SET-HVAC-PARAMETERS:1,1,4,0,10,0,0,0'],
            [4, ['action' => 'HVAC_SET_PARAMETERS', 'mode' => 'HEAT'], 'ACTION-SET-HVAC-PARAMETERS:1,1,4,0,2,0,0,0'],
            [4, ['action' => 'HVAC_SET_PARAMETERS', 'mode' => 'COOL', 'setpoints' => ['cool' => 22.5]], 'ACTION-SET-HVAC-PARAMETERS:1,1,4,0,3,0,2250,2'],
            [4, ['action' => 'HVAC_SET_PARAMETERS', 'mode' => 'AUTO', 'setpoints' => ['heat' => 123, 'cool' => 22.5], 'durationMs' => 3600], 'ACTION-SET-HVAC-PARAMETERS:1,1,4,3600,4,12300,2250,3'],
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @dataProvider hvacInvalidActionsProvider
     */
    public function testExecutingInvalidActionOnHvac(int $channelId, array $actionRequest) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->request('PATCH', '/api/channels/' . $channelId, [], [], [], json_encode($actionRequest));
        $response = $client->getResponse();
        $this->assertStatusCode('400', $response);
    }

    public function hvacInvalidActionsProvider() {
        // @codingStandardsIgnoreStart
        return [
            [3, ['action' => 'OPEN']],
            [3, ['action' => 'TURN_OFF_WITH_DURATION', 'durationMs' => -1]],
            [3, ['action' => 'TURN_OFF_WITH_DURATION', 'durationMs' => 931536000000]],
            [3, ['action' => 'HVAC_SET_TEMPERATURES', 'setpoints' => ['cool' => 22.5]]],
            [3, ['action' => 'HVAC_SET_TEMPERATURES', 'setpoints' => ['heat' => 22.5, 'cool' => 21.5]]],
            [3, ['action' => 'HVAC_SWITCH_TO_MANUAL_MODE', 'setpoints' => ['heat' => 22.5]]],
            [3, ['action' => 'HVAC_SET_PARAMETERS', 'setpoints' => ['cool' => 22.5]]],
            [3, ['action' => 'HVAC_SET_PARAMETERS', 'duration' => -1]],
            [3, ['action' => 'HVAC_SET_PARAMETERS', 'mode' => 'HEAT', 'setpoints' => ['heat' => 22.5]]],
            [3, ['action' => 'HVAC_SET_PARAMETERS', 'unicorn' => 'HEAT', 'setpoints' => ['heat' => 22.5]]],
        ];
        // @codingStandardsIgnoreEnd
    }

    public function testCreatingNewScheduleForHvac() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequest(Request::METHOD_POST, '/api/schedules', [
            'channelId' => $this->hvacChannel->getId(),
            'actionId' => ChannelFunctionAction::HVAC_SET_TEMPERATURES,
            'actionParam' => ['setpoints' => ['heat' => 22.5]],
            'mode' => ScheduleMode::ONCE,
            'timeExpression' => '2 2 * * *',
        ]);
        $this->assertStatusCode(Response::HTTP_CREATED, $client->getResponse());
        $scheduleFromResponse = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThan(0, $scheduleFromResponse['id']);
        $config = $scheduleFromResponse['config'][0];
        $this->assertEquals('2 2 * * *', $config['crontab']);
        $this->assertEquals(ChannelFunctionAction::HVAC_SET_TEMPERATURES, $config['action']['id']);
        $this->assertEquals(['setpoints' => ['heat' => 22.5]], $config['action']['param']);
    }
}

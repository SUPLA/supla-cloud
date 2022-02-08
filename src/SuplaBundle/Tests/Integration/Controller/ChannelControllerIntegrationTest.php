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

namespace SuplaBundle\Tests\Integration\Controller;

use SuplaBundle\Auth\OAuthScope;
use SuplaBundle\Auth\SuplaOAuth2;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\OAuth\AccessToken;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\SceneOperation;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;

/** @small */
class ChannelControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;
    /** @var AccessToken */
    private $peronsalToken;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
        $this->device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER],
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::DIMMERANDRGBLIGHTING],
            [ChannelType::VALVEOPENCLOSE, ChannelFunction::VALVEOPENCLOSE],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::RELAY, ChannelFunction::NONE],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
        ]);
        $oauth = self::$container->get(SuplaOAuth2::class);
        $this->peronsalToken = $oauth->createPersonalAccessToken($this->user, 'TEST', new OAuthScope(OAuthScope::getSupportedScopes()));
        $this->getEntityManager()->persist($this->peronsalToken);
        $this->getEntityManager()->flush();
    }

    public function testGettingChannelInfo() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $channel = $this->device->getChannels()[0];
        $client->request('GET', '/api/channels/' . $channel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertTrue($content->enabled);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertGreaterThanOrEqual(1, count($commands));
    }

    public function testGettingChannelInfoV23() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $channel = $this->device->getChannels()[0];
        $client->apiRequestV23('GET', '/api/channels/' . $channel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(ChannelFunction::LIGHTSWITCH, $content['functionId']);
        $this->assertEquals(ChannelFunction::LIGHTSWITCH, $content['function']['id']);
        $this->assertEmpty($this->getSuplaServerCommands($client));
        $this->assertArrayHasKey('param1', $content);
    }

    public function testGettingChannelInfoV24() {
        $client = $this->createAuthenticatedClient($this->user);
        $channel = $this->device->getChannels()[0];
        $client->apiRequestV24('GET', '/api/channels/' . $channel->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(ChannelFunction::LIGHTSWITCH, $content['functionId']);
        $this->assertEquals(ChannelFunction::LIGHTSWITCH, $content['function']['id']);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertArrayHasKey('subjectType', $content);
        $this->assertArrayNotHasKey('param1', $content);
        $this->assertArrayHasKey('config', $content);
        $this->assertEquals(ActionableSubjectType::CHANNEL, $content['subjectType']);
    }

    public function testGettingChannelInfoWithDeviceLocationV24() {
        $client = $this->createAuthenticatedClient($this->user);
        $channel = $this->device->getChannels()[0];
        $client->apiRequestV24('GET', '/api/channels/' . $channel->getId() . '?include=iodevice,iodevice.location');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(ChannelFunction::LIGHTSWITCH, $content['functionId']);
        $this->assertEquals(ChannelFunction::LIGHTSWITCH, $content['function']['id']);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertArrayHasKey('iodevice', $content);
        $this->assertArrayHasKey('location', $content['iodevice']);
        $this->assertArrayNotHasKey('location', $content);
    }

    public function testGettingChannelsWithLocationsV24() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?include=location,iodevice');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('location', $content[0]);
        $this->assertArrayHasKey('iodevice', $content[0]);
        $this->assertArrayHasKey('relationsCount', $content[0]);
        $this->assertArrayNotHasKey('location', $content[0]['iodevice']);
    }

    public function testFilteringByFunction() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?function=LIGHTSWITCH,DIMMERANDRGBLIGHTING');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(3, $content);
    }

    public function testFilteringBySkipIds() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?skipIds=1,2,3,4');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(5, $content);
    }

    public function testFilteringByInput() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?io=input');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
    }

    public function testFilteringByInvalidInput() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?io=unicorn');
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testFilteringByHasFunction() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?hasFunction=1');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertLessThan(count($this->device->getChannels()), count($content));
    }

    public function testFilteringByHasFunctionNone() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?hasFunction=0');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
    }

    public function testFilteringByHasFunctionFalse() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?hasFunction=false');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
    }

    public function testFilteringByHasFunctionAnything() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?hasFunction=unicorn');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertLessThan(count($this->device->getChannels()), count($content));
    }

    public function testGettingChannelsWithDeviceLocationsV24() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels?include=location,iodevice,iodevice.location');
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    /**
     * @dataProvider changingChannelStateDataProvider
     */
    public function testChangingChannelState(int $channelId, string $action, string $expectedCommand, array $additionalRequest = []) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $request = array_merge(['action' => $action], $additionalRequest);
        $client->request('PATCH', '/api/channels/' . $channelId, [], [], [], json_encode($request));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains($expectedCommand, $commands, implode(PHP_EOL, $commands));
    }

    public function changingChannelStateDataProvider() {
        return [
            [1, 'turn-on', 'SET-CHAR-VALUE:1,1,1,1'],
            [1, 'turn-off', 'SET-CHAR-VALUE:1,1,1,0'],
            [2, 'open', 'SET-CHAR-VALUE:1,1,2,1'],
            [3, 'open-close', 'SET-CHAR-VALUE:1,1,3,1'],
            [3, 'open', 'ACTION-OPEN:1,1,3'],
            [3, 'close', 'ACTION-CLOSE:1,1,3'],
            [4, 'shut', 'SET-CHAR-VALUE:1,1,4,110'],
            [4, 'reveal', 'SET-CHAR-VALUE:1,1,4,10'],
            [4, 'stop', 'SET-CHAR-VALUE:1,1,4,0'],
            [4, 'shut', 'SET-CHAR-VALUE:1,1,4,50', ['percent' => 40]],
            [4, 'reveal', 'SET-CHAR-VALUE:1,1,4,50', ['percent' => 60]],
            [5, 'set-rgbw-parameters', 'SET-RGBW-VALUE:1,1,5,16711935,58,42,0',
                ['color' => 0xFF00FF, 'color_brightness' => 58, 'brightness' => 42]],
            [5, 'set-rgbw-parameters', 'SET-RGBW-VALUE:1,1,5,16711935,100,0,0', ['color' => '0xFF00FF', 'brightness' => 0]],
            [5, 'set-rgbw-parameters', 'SET-RGBW-VALUE:1,1,5,16711935,100,0,0', ['color' => 0xFF00FF, 'brightness' => 0]],
            [5, 'set-rgbw-parameters', 'SET-RGBW-VALUE:1,1,5,11141290,67,0,0', ['color' => '0xAA00AA', 'brightness' => 0]],
            [5, 'set-rgbw-parameters', 'SET-RGBW-VALUE:1,1,5,16711935,58,42,0',
                ['color' => '0xFF00FF', 'color_brightness' => 58, 'brightness' => 42]],
            [5, 'set-rgbw-parameters', 'SET-RGBW-VALUE:1,1,5,16711935,58,42,1',
                ['color' => '0xFF00FF', 'color_brightness' => 58, 'brightness' => 42, 'turnOnOff' => 1]],
            [5, 'set-rgbw-parameters', 'SET-RGBW-VALUE:1,1,5,16711935,58,42,3',
                ['color' => '0xFF00FF', 'color_brightness' => 58, 'brightness' => 42, 'turnOnOff' => 3]],
            [5, 'set-rgbw-parameters', 'SET-RAND-RGBW-VALUE:1,1,5,58,42',
                ['color' => 'random', 'color_brightness' => 58, 'brightness' => 42]],
            [6, 'open', 'SET-CHAR-VALUE:1,1,6,1'],
            [6, 'close', 'SET-CHAR-VALUE:1,1,6,0'],
            [1, 'copy', 'ACTION-COPY:1,1,1,1,9', ['sourceChannelId' => 9]],
        ];
    }

    public function testTryingToExecuteActionInvalidForChannel() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->request('PATCH', '/api/channels/' . 1, [], [], [], json_encode(array_merge(['action' => 'open'])));
        $response = $client->getResponse();
        $this->assertStatusCode('4xx', $response);
    }

    public function testTryingToExecuteInvalidAction() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->request('PATCH', '/api/channels/' . 1, [], [], [], json_encode(array_merge(['action' => 'unicorn'])));
        $response = $client->getResponse();
        $this->assertStatusCode('4xx', $response);
    }

    public function testChangingChannelRgbwState20() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $request = ['color' => 0xFF00FF, 'color_brightness' => 58, 'brightness' => 42];
        $client->request('PUT', '/api/channels/5', [], [], $this->versionHeader(ApiVersions::V2_0()), json_encode($request));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('SET-RGBW-VALUE:1,1,5,16711935,58,42,0', $commands);
    }

    public function testChangingChannelFunctionClearsRelatedSensorInOtherDevices() {
        $client = $this->createAuthenticatedClient();
        $channelParamConfigTranslator = self::$container->get(ChannelParamConfigTranslator::class);
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $gateChannel = $this->device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::CONTROLLINGTHEGATE;
        })->first();
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        // assign sensor to the gate from other device
        $channelParamConfigTranslator->setParamsFromConfig($gateChannel, ['openingSensorChannelId' => $sensorChannel->getId()]);
        $this->getEntityManager()->refresh($gateChannel);
        $this->assertEquals($sensorChannel->getId(), $gateChannel->getParam2());
        $client->apiRequestV23('PUT', '/api/channels/' . $sensorChannel->getId(), [
            'functionId' => ChannelFunction::OPENINGSENSOR_GARAGEDOOR,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->getEntityManager()->refresh($gateChannel);
        $this->getEntityManager()->refresh($sensorChannel);
        $this->assertEquals(0, $gateChannel->getParam2(), 'The paired sensor has not been cleared.');
        $this->assertEquals(ChannelFunction::OPENINGSENSOR_GARAGEDOOR, $sensorChannel->getFunction()->getId());
    }

    public function testCanChangeChannelFunctionToNone() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $sensorChannel->getId(), [
            'functionId' => ChannelFunction::NONE,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $sensorChannel = $this->getEntityManager()->find(IODeviceChannel::class, $sensorChannel->getId());
        $this->assertEquals(ChannelFunction::NONE, $sensorChannel->getFunction()->getId());
    }

    public function testChangingChannelFunctionFromPowerswitchToOpeningGate() {
        $client = $this->createAuthenticatedClient();
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::POWERSWITCH],
        ]);
        $relayChannel = $anotherDevice->getChannels()[0];
        // change function to the opening gate with default params from GUI
        $client->apiRequestV23('PUT', '/api/channels/' . $relayChannel->getId(), [
            'functionId' => ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
            'param1' => 500,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->getEntityManager()->refresh($relayChannel);
        $this->assertEquals(500, $relayChannel->getParam1(), 'Opening time has been set.');
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGATEWAYLOCK, $relayChannel->getFunction()->getId());
    }

    public function testChangingChannelCaptionToEmoji() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $sensorChannel->getId(), [
            'caption' => 'Gate 🏎️',
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $sensorChannel = $this->getEntityManager()->find(IODeviceChannel::class, $sensorChannel->getId());
        $this->assertEquals('Gate 🏎️', $sensorChannel->getCaption());
    }

    public function testCannotChangeChannelFunctionToNotSupported() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $sensorChannel->getId(), [
            'functionId' => ChannelFunction::THERMOMETER,
        ]);
        $this->assertStatusCode(400, $client->getResponse());
        return $sensorChannel;
    }

    public function testChangingChannelFunctionDeletesExistingDirectLinks() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $directLink = new DirectLink($sensorChannel);
        $directLink->generateSlug(new BCryptPasswordEncoder(4));
        $this->getEntityManager()->persist($directLink);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV23('PUT', '/api/channels/' . $sensorChannel->getId(), [
            'functionId' => ChannelFunction::OPENINGSENSOR_GARAGEDOOR,
        ]);
        $this->assertStatusCode(409, $client->getResponse());
        $this->assertEmpty(SuplaServerMock::$executedCommands);
        return $sensorChannel;
    }

    /** @depends testChangingChannelFunctionDeletesExistingDirectLinks */
    public function testChangingChannelFunctionDeletesExistingDirectLinksWhenConfirmed(IODeviceChannel $sensorChannel) {
        $sensorChannel = $this->getEntityManager()->find(IODeviceChannel::class, $sensorChannel->getId());
        $directLink = $sensorChannel->getDirectLinks()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV23('PUT', '/api/channels/' . $sensorChannel->getId() . '?confirm=1', [
            'functionId' => ChannelFunction::OPENINGSENSOR_GARAGEDOOR,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertNull($this->getEntityManager()->find(DirectLink::class, $directLink->getId()));
        return $sensorChannel;
    }

    /** @depends testChangingChannelFunctionDeletesExistingDirectLinksWhenConfirmed */
    public function testNotifiesSuplaServerAboutFunctionChange(IODeviceChannel $sensorChannel) {
        $this->assertContains('USER-BEFORE-CHANNEL-FUNCTION-CHANGE:1,' . $sensorChannel->getId(), SuplaServerMock::$executedCommands);
    }

    public function testChangingChannelFunctionDeletesExistingScenes() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATEWAYLOCK],
        ]);
        $gateChannel = $anotherDevice->getChannels()[0];
        $scene = new Scene($anotherDevice->getLocation());
        $scene->setOpeartions([new SceneOperation($gateChannel, ChannelFunctionAction::OPEN_CLOSE())]);
        $this->getEntityManager()->persist($scene);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $gateChannel->getId() . '?safe=1', [
            'functionId' => ChannelFunction::CONTROLLINGTHEGATE,
        ]);
        $this->assertStatusCode(409, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $content['sceneOperations']);
        $this->assertArrayHasKey('owningScene', $content['sceneOperations'][0]); // important for frontend - it displays scene name
        return $gateChannel;
    }

    /** @depends testChangingChannelFunctionDeletesExistingScenes */
    public function testChangingChannelFunctionDeletesExistingDirectLinksWhenNotSafe(IODeviceChannel $gateChannel) {
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        $sceneOperation = $gateChannel->getSceneOperations()[0];
        $scene = $sceneOperation->getOwningScene();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $gateChannel->getId(), [
            'functionId' => ChannelFunction::CONTROLLINGTHEGATE,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertNull($this->getEntityManager()->find(SceneOperation::class, $sceneOperation->getId()));
        $this->assertNull($this->getEntityManager()->find(Scene::class, $scene->getId()));
        return $gateChannel;
    }

    /** @depends testChangingChannelFunctionDeletesExistingDirectLinksWhenNotSafe */
    public function testChangingChannelFunctionAndSettingConfigAtTheSameTimeWorksV23(IODeviceChannel $gateChannel) {
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV23('PUT', '/api/channels/' . $gateChannel->getId(), [
            'functionId' => ChannelFunction::CONTROLLINGTHEDOORLOCK,
            'param1' => 1566,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        $this->assertEquals(1566, $gateChannel->getParam1());
        return $gateChannel;
    }

    /** @depends testChangingChannelFunctionDeletesExistingDirectLinksWhenNotSafe */
    public function testChangingChannelFunctionAndSettingConfigAtTheSameTimeWorks(IODeviceChannel $gateChannel) {
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $gateChannel->getId(), [
            'functionId' => ChannelFunction::CONTROLLINGTHEGATE,
            'config' => ['relayTimeMs' => 1567],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        $this->assertEquals(1567, $gateChannel->getParam1());
        return $gateChannel;
    }

    /** @depends testChangingChannelFunctionAndSettingConfigAtTheSameTimeWorks */
    public function testSavingParamsConfigInDatabaseAsJson(IODeviceChannel $gateChannel) {
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        $expectedConfig = [
            'relayTimeMs' => 1567,
            'openingSensorChannelId' => null,
            'openingSensorSecondaryChannelId' => null,
            'timeSettingAvailable' => true,
            'numberOfAttemptsToOpen' => 5,
            'numberOfAttemptsToClose' => 5,
        ];
        $this->assertEquals($expectedConfig, $gateChannel->getUserConfig());
    }

    /** @depends testChangingChannelFunctionDeletesExistingDirectLinksWhenNotSafe */
    public function testCannotStoreRubbishInConfig(IODeviceChannel $gateChannel) {
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $gateChannel->getId(), [
            'config' => ['unicorn' => 123],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        $this->assertArrayNotHasKey('unicorn', $gateChannel->getUserConfig());
    }

    /** @depends testChangingChannelFunctionDeletesExistingDirectLinks */
    public function testChangingChannelFunctionToNoneClearsConfig(IODeviceChannel $channel) {
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, $channel->getId());
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $channel->getId(), ['functionId' => ChannelFunction::NONE]);
        $this->assertStatusCode(200, $client->getResponse());
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, $channel->getId());
        $this->assertEmpty($channel->getUserConfig());
        $this->assertEquals(0, $channel->getParam1());
        $this->assertEquals(0, $channel->getParam2());
        $this->assertEquals(0, $channel->getParam3());
    }

    public function testSettingConfigForActionTrigger() {
        $anotherDevice = $this->createDeviceSonoff($this->getEntityManager()->find(Location::class, $this->location->getId()));
        $trigger = $anotherDevice->getChannels()[2];
        $channel = $this->device->getChannels()[0];
        $actions = ['TURN_ON' => [
            'subjectId' => $channel->getId(), 'subjectType' => ActionableSubjectType::CHANNEL,
            'action' => ['id' => $channel->getPossibleActions()[0]->getId()]]];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $trigger->getId(), ['config' => ['actions' => $actions]]);
        $this->assertStatusCode(200, $client->getResponse());
        $trigger = $this->getEntityManager()->find(IODeviceChannel::class, $trigger->getId());
        $this->assertArrayHasKey('actions', $trigger->getUserConfig());
        $this->assertCount(1, $trigger->getUserConfig()['actions']);
        return $trigger;
    }

    /** @depends testSettingConfigForActionTrigger */
    public function testGettingChannelActionTriggersCount(IODeviceChannel $trigger) {
        $channelWithRelatedTrigger = $this->getEntityManager()->find(IODeviceChannel::class, $trigger->getParam1());
        $this->assertNotNull($channelWithRelatedTrigger);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channels/' . $channelWithRelatedTrigger->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertArrayHasKey('actionTriggers', $content['relationsCount']);
        $this->assertEquals(1, $content['relationsCount']['actionTriggers']);
    }

    /** @depends testSettingConfigForActionTrigger */
    public function testGettingIoDeviceChannels(IODeviceChannel $trigger) {
        $device = $trigger->getIoDevice();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/iodevices/{$device->getId()}/channels");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(3, $content);
        $this->assertArrayHasKey('relationsCount', $content[0]);
        $this->assertEquals(1, $content[0]['relationsCount']['actionTriggers']);
    }

    /** @depends testSettingConfigForActionTrigger */
    public function testChangingChannelFunctionTriesToClearRelatedActionTriggers(IODeviceChannel $trigger) {
        $channel = $this->device->getChannels()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $channel->getId() . '?safe=1', [
            'functionId' => ChannelFunction::POWERSWITCH,
        ]);
        $this->assertStatusCode(409, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('actionTriggers', $content);
        $this->assertCount(1, $content['actionTriggers']);
        $this->assertEquals($trigger->getId(), $content['actionTriggers'][0]['id']);
        return $trigger;
    }

    /** @depends testChangingChannelFunctionTriesToClearRelatedActionTriggers */
    public function testChangingChannelFunctionClearsRelatedActionTriggers(IODeviceChannel $trigger) {
        $channel = $this->device->getChannels()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $channel->getId(), [
            'functionId' => ChannelFunction::POWERSWITCH,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $trigger = $this->getEntityManager()->find(IODeviceChannel::class, $trigger->getId());
        $this->assertEmpty($trigger->getUserConfig()['actions']);
    }

    public function testChangingChannelFunctionClearsRelatedActionTriggersOnly() {
        $anotherDevice = $this->createDeviceSonoff($this->getEntityManager()->find(Location::class, $this->location->getId()));
        $trigger = $anotherDevice->getChannels()[2];
        $channel1 = $this->device->getChannels()[0];
        $channel2 = $this->device->getChannels()[1];
        $actions = [
            'TURN_ON' => [
                'subjectId' => $channel1->getId(), 'subjectType' => ActionableSubjectType::CHANNEL,
                'action' => ['id' => $channel1->getPossibleActions()[0]->getId()]],
            'TURN_OFF' => [
                'subjectId' => $channel2->getId(), 'subjectType' => ActionableSubjectType::CHANNEL,
                'action' => ['id' => $channel2->getPossibleActions()[0]->getId()]],
        ];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $trigger->getId(), ['config' => ['actions' => $actions]]);
        $this->assertStatusCode(200, $client->getResponse());
        $trigger = $this->getEntityManager()->find(IODeviceChannel::class, $trigger->getId());
        $this->assertCount(2, $trigger->getUserConfig()['actions']);
        $client->apiRequestV24('PUT', '/api/channels/' . $channel2->getId(), [
            'functionId' => ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $trigger = $this->getEntityManager()->find(IODeviceChannel::class, $trigger->getId());
        $this->assertCount(1, $trigger->getUserConfig()['actions']);
        $this->assertArrayHasKey('TURN_ON', $trigger->getUserConfig()['actions']);
    }

    public function testSettingConfigWithAtActionForActionTrigger() {
        $anotherDevice = $this->createDeviceSonoff($this->getEntityManager()->find(Location::class, $this->location->getId()));
        $trigger = $anotherDevice->getChannels()[2];
        $actions = ['TURN_ON' => [
            'subjectType' => ActionableSubjectType::OTHER,
            'action' => ['id' => ChannelFunctionAction::AT_FORWARD_OUTSIDE]]];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $trigger->getId(), ['config' => ['actions' => $actions]]);
        $this->assertStatusCode(200, $client->getResponse());
        $trigger = $this->getEntityManager()->find(IODeviceChannel::class, $trigger->getId());
        $this->assertArrayHasKey('actions', $trigger->getUserConfig());
        $this->assertCount(1, $trigger->getUserConfig()['actions']);
    }

    public function testChangingChannelFunctionCanSetSettingForTheNewFunction() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
        ]);
        $channel = $anotherDevice->getChannels()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV23('PUT', '/api/channels/' . $channel->getId(), [
            'functionId' => ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            'param1' => 2000,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, $channel->getId());
        $this->assertEquals(ChannelFunction::CONTROLLINGTHEGARAGEDOOR, $channel->getFunction()->getId());
        $this->assertEquals(2000, $channel->getParam1());
    }

    public function testChangingChannelFunctionCanSetAltIconImmediately() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
        ]);
        $channel = $anotherDevice->getChannels()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV23('PUT', '/api/channels/' . $channel->getId(), [
            'functionId' => ChannelFunction::POWERSWITCH,
            'altIcon' => 1,
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $channel = $this->getEntityManager()->find(IODeviceChannel::class, $channel->getId());
        $this->assertEquals(ChannelFunction::POWERSWITCH, $channel->getFunction()->getId());
        $this->assertEquals(1, $channel->getAltIcon());
    }

    public function testChangingChannelFunctionCanSetConfigImmediately() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
        ]);
        $channel = $anotherDevice->getChannels()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $channel->getId(), [
            'functionId' => ChannelFunction::CONTROLLINGTHEGATE,
            'config' => ['relayTimeMs' => 999],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $channel = $this->freshEntity($channel);
        $this->assertEquals(999, $channel->getParam1());
    }

    public function testOpeningValveIfFloodingFromWebClient() {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', "VALUE:1,1\n");
        $client = $this->createAuthenticatedClient($this->user);
        $client->request('PATCH', '/api/channels/6', [], [], [], json_encode(array_merge(['action' => 'open'])));
        $response = $client->getResponse();
        $this->assertStatusCode('2XX', $response);
    }

    public function testOpeningValveIfManuallyShutFromWebClient() {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', "VALUE:1,2\n");
        $client = $this->createAuthenticatedClient($this->user);
        $client->request('PATCH', '/api/channels/6', [], [], [], json_encode(array_merge(['action' => 'open'])));
        $response = $client->getResponse();
        $this->assertStatusCode('2XX', $response);
    }

    public function testPreventingToOpenValveIfManuallyShutFromApiClient() {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', "VALUE:1,2\n");
        $client = self::createClient(
            ['debug' => false],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->peronsalToken->getToken(), 'HTTPS' => true]
        );
        $client->request('PATCH', '/api/v2.3.0/channels/6', [], [], [], json_encode(array_merge(['action' => 'open'])));
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertContains('closed manually', $body['message']);
    }

    public function testPreventingToOpenValveIfFloodingFromApiClient() {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', "VALUE:1,1\n");
        $client = self::createClient(
            ['debug' => false],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->peronsalToken->getToken(), 'HTTPS' => true]
        );
        $client->request('PATCH', '/api/v2.3.0/channels/6', [], [], [], json_encode(array_merge(['action' => 'open'])));
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
        $body = json_decode($response->getContent(), true);
        $this->assertContains('closed manually', $body['message']);
    }

    public function testCanOpenValveIfNotManuallyShutFromApiClient() {
        SuplaServerMock::mockResponse('GET-VALVE-VALUE', "VALUE:1,0\n");
        $client = self::createClient(
            ['debug' => false],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->peronsalToken->getToken(), 'HTTPS' => true]
        );
        $client->request('PATCH', '/api/v2.3.0/channels/6', [], [], [], json_encode(array_merge(['action' => 'open'])));
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
    }

    public function testResettingCounters() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_WATERMETER],
        ]);
        $measurementChannel = $anotherDevice->getChannels()[0];
        EntityUtils::setField($measurementChannel, 'flags', ChannelFunctionBitsFlags::RESET_COUNTERS_ACTION_AVAILABLE);
        $this->getEntityManager()->persist($measurementChannel);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $measurementChannelId = $measurementChannel->getId();
        $client->apiRequestV24('PATCH', "/api/channels/{$measurementChannelId}/settings", [
            'action' => 'resetCounters',
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertContains("RESET-COUNTERS:1,{$anotherDevice->getId()},{$measurementChannelId}", SuplaServerMock::$executedCommands);
    }

    public function testResettingCountersOfUnsupportedChannel() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
        ]);
        $channel = $anotherDevice->getChannels()[0];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', "/api/channels/{$channel->getId()}/settings", [
            'action' => 'resetCounters',
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testRecalibrating() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER],
        ]);
        $channel = $anotherDevice->getChannels()[0];
        EntityUtils::setField($channel, 'flags', ChannelFunctionBitsFlags::RECALIBRATE_ACTION_AVAILABLE);
        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $measurementChannelId = $channel->getId();
        $client->apiRequestV24('PATCH', "/api/channels/{$measurementChannelId}/settings", [
            'action' => 'recalibrate',
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertContains("RECALIBRATE:1,{$anotherDevice->getId()},{$measurementChannelId}", SuplaServerMock::$executedCommands);
    }

    public function testFetchingActionTriggers() {
        $client = $this->createAuthenticatedClient($this->user);
        $anotherDevice = $this->createDeviceSonoff($this->getEntityManager()->find(Location::class, $this->location->getId()));
        $client->apiRequestV24('GET', '/api/channels/' . $anotherDevice->getChannels()[0]->getId() . '?include=actionTriggers');
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('actionTriggersIds', $content);
        $this->assertEquals([$anotherDevice->getChannels()[2]->getId()], $content['actionTriggersIds']);
    }

    public function testDoesNotFetchOtherParam1AsActionTriggers() {
        $device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
        ]);
        $firstChannel = $device->getChannels()[0];
        $secondChannel = $device->getChannels()[1];
        $firstChannel->setParam1($secondChannel->getId()); // pretending param1 as AT
        $this->getEntityManager()->persist($firstChannel);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('GET', '/api/channels/' . $secondChannel->getId() . '?include=actionTriggers');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertEquals(0, $content['relationsCount']['actionTriggers']);
        $this->assertEmpty($content['actionTriggersIds']);
    }

    public function testChangingChannelFunctionClearsRelatedMeasurementChannel() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::IMPULSECOUNTER, ChannelFunction::IC_WATERMETER],
        ]);
        $relay = $anotherDevice->getChannels()[0];
        $measurement = $anotherDevice->getChannels()[1];
        $client = $this->createAuthenticatedClient();
        // set measurement channel
        $client->apiRequestV24('PUT', '/api/channels/' . $relay->getId() . '?safe=1', [
            'config' => ['relatedChannelId' => $measurement->getId()],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $relay = $this->freshEntity($relay);
        $measurement = $this->freshEntity($measurement);
        $this->assertEquals($measurement->getId(), $relay->getParam1());
        $this->assertEquals($relay->getId(), $measurement->getParam4());
        // change relay function
        $client->apiRequestV24('PUT', '/api/channels/' . $relay->getId(), [
            'functionId' => ChannelFunction::CONTROLLINGTHEGATE,
            'config' => ['relatedChannelId' => $measurement->getId()], // old config should not influence the result
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $relay = $this->freshEntity($relay);
        $measurement = $this->freshEntity($measurement);
        $this->assertEquals(500, $relay->getParam1()); // default gate opening time
        $this->assertEquals(0, $measurement->getParam4());
    }
}

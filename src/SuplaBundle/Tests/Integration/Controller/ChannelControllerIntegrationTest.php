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

use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Model\ChannelParamsUpdater\IODeviceChannelWithParams;
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

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
        $this->device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER],
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::DIMMERANDRGBLIGHTING],
        ]);
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
        $content = json_decode($response->getContent());
        $this->assertEquals(ChannelFunction::LIGHTSWITCH, $content->functionId);
        $this->assertEquals(ChannelFunction::LIGHTSWITCH, $content->function->id);
        $this->assertEmpty($this->getSuplaServerCommands($client));
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
        $this->assertArrayNotHasKey('location', $content[0]['iodevice']);
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
        $this->assertContains($expectedCommand, $commands);
    }

    public function changingChannelStateDataProvider() {
        return [
            [1, 'turn-on', 'SET-CHAR-VALUE:1,1,1,1'],
            [1, 'turn-off', 'SET-CHAR-VALUE:1,1,1,0'],
            [2, 'open', 'SET-CHAR-VALUE:1,1,2,1'],
            [3, 'open-close', 'SET-CHAR-VALUE:1,1,3,1'],
            [4, 'shut', 'SET-CHAR-VALUE:1,1,4,110'],
            [4, 'reveal', 'SET-CHAR-VALUE:1,1,4,10'],
            [4, 'stop', 'SET-CHAR-VALUE:1,1,4,0'],
            [4, 'shut', 'SET-CHAR-VALUE:1,1,4,50', ['percent' => 40]],
            [4, 'reveal', 'SET-CHAR-VALUE:1,1,4,50', ['percent' => 60]],
            [5, 'set-rgbw-parameters', 'SET-RGBW-VALUE:1,1,5,16711935,58,42',
                ['color' => 0xFF00FF, 'color_brightness' => 58, 'brightness' => 42]],
            [5, 'set-rgbw-parameters', 'SET-RGBW-VALUE:1,1,5,16711935,58,42',
                ['color' => '0xFF00FF', 'color_brightness' => 58, 'brightness' => 42]],
            [5, 'set-rgbw-parameters', 'SET-RAND-RGBW-VALUE:1,1,5,58,42',
                ['color' => 'random', 'color_brightness' => 58, 'brightness' => 42]],
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
        $this->assertContains('SET-RGBW-VALUE:1,1,5,16711935,58,42', $commands);
    }

    public function testChangingChannelFunctionClearsRelatedSensorInOtherDevices() {
        $client = $this->createAuthenticatedClient();
        $channelParamsUpdater = self::$container->get(ChannelParamsUpdater::class);
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
        $channelParamsUpdater->updateChannelParams($gateChannel, new IODeviceChannelWithParams(0, $sensorChannel->getId()));
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
    }
}

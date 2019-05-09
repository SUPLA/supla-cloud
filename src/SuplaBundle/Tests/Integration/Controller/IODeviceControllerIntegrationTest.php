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

/** @small */
class IODeviceControllerIntegrationTest extends IntegrationTestCase {
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
        $this->device = $this->createDeviceFull($this->location);
    }

    public function testGettingDevicesList() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertTrue(property_exists($content, 'iodevices'));
        $iodevices = $content->iodevices;
        $this->assertCount(1, $iodevices);
    }

    public function testGettingDevicesListVersion22() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertCount(1, $content);
        $this->assertEquals($this->device->getId(), $content[0]->id);
        $this->assertEquals($this->device->getSoftwareVersion(), $content[0]->softwareVersion);
        $this->assertFalse(property_exists($content[0], 'location'));
        $this->assertFalse(property_exists($content[0], 'channels'));
    }

    public function testGettingDevicesWithChannels22() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices?include=channels', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertCount(1, $content);
        $this->assertEquals($this->device->getId(), $content[0]->id);
        $this->assertFalse(property_exists($content[0], 'location'));
        $this->assertTrue(property_exists($content[0], 'channels'));
        $this->assertCount(5, $content[0]->channels);
    }

    public function testGettingDevicesWithLocationAndOriginalLocation22() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices?include=location,originalLocation', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertCount(1, $content);
        $this->assertEquals($this->device->getId(), $content[0]->id);
        $this->assertTrue(property_exists($content[0], 'location'));
        $this->assertTrue(property_exists($content[0], 'originalLocation'));
        $this->assertFalse(property_exists($content[0], 'channels'));
        $this->assertFalse(property_exists($content[0], 'connected'));
    }

    public function testGettingDevicesWithConnectedStatus22() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices?include=connected', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertCount(1, $content);
        $this->assertEquals($this->device->getId(), $content[0]->id);
        $this->assertFalse(property_exists($content[0], 'location'));
        $this->assertFalse(property_exists($content[0], 'originalLocation'));
        $this->assertFalse(property_exists($content[0], 'channels'));
        $this->assertTrue(property_exists($content[0], 'connected'));
    }

    public function testPassingWrongIncludeParam() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices?include=turtles,channels,unicorns', [], [], $this->versionHeader(ApiVersions::V2_2()));
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $content = json_decode($response->getContent());
        $this->assertContains('not supported: turtles', $content->message);
        $this->assertContains('unicorns', $content->message);
    }

    public function testGettingDevicesDetails() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices/' . $this->device->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = current(json_decode($response->getContent(), true));
        $this->assertEquals($this->device->getId(), $content['id']);
    }

    public function test404OnGettingInvalidIoDevice() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices/123245');
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
    }

    public function test403OnGettingDeviceOfAnotherUser() {
        $user = $this->createConfirmedUser('another@supla.org');
        $client = $this->createAuthenticatedClient($user->getUsername());
        $client->request('GET', '/api/iodevices/' . $this->device->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(403, $response);
    }

    public function testDeletingDevice() {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/iodevices/' . $this->device->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
        $this->assertEmpty($this->user->getIODevices());
    }

    /** @large */
    public function testDeletingDeviceClearsRelatedGateOtherDevices() {
        $client = $this->createAuthenticatedClient();
        $channelParamsUpdater = $this->container->get(ChannelParamsUpdater::class);
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $gateChannel = $this->device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::CONTROLLINGTHEGATE;
        })->first();
        // assign sensor to the gate from other device
        $channelParamsUpdater->updateChannelParams($gateChannel, new IODeviceChannelWithParams(0, $sensorChannel->getId()));
        $this->getEntityManager()->refresh($sensorChannel);
        $this->assertEquals($gateChannel->getId(), $sensorChannel->getParam1());
        $client->request('DELETE', '/api/iodevices/' . $this->device->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->getEntityManager()->refresh($sensorChannel);
        $this->assertEquals(0, $sensorChannel->getParam1(), 'The paired sensor has not been cleared.');
    }

    /** @large */
    public function testDeletingDeviceClearsRelatedSensorInOtherDevices() {
        $client = $this->createAuthenticatedClient();
        $channelParamsUpdater = $this->container->get(ChannelParamsUpdater::class);
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GATE],
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
        $client->request('DELETE', '/api/iodevices/' . $anotherDevice->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->getEntityManager()->refresh($gateChannel);
        $this->assertEquals(0, $gateChannel->getParam2(), 'The paired sensor has not been cleared.');
    }

    /** @large */
    public function testDeletingDeviceClearsRelatedSecondaryGateOtherDevices() {
        $client = $this->createAuthenticatedClient();
        $channelParamsUpdater = $this->container->get(ChannelParamsUpdater::class);
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $gateChannel = $this->device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::CONTROLLINGTHEGATE;
        })->first();
        // assign sensor to the gate from other device
        $channelParamsUpdater->updateChannelParams($gateChannel, new IODeviceChannelWithParams(0, 0, $sensorChannel->getId()));
        $this->getEntityManager()->refresh($sensorChannel);
        $this->assertEquals($gateChannel->getId(), $sensorChannel->getParam2());
        $client->request('DELETE', '/api/iodevices/' . $this->device->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->getEntityManager()->refresh($sensorChannel);
        $this->assertEquals(0, $sensorChannel->getParam2(), 'The paired sensor has not been cleared.');
    }

    /** @large */
    public function testDeletingDeviceClearsRelatedSecondarySensorInOtherDevices() {
        $client = $this->createAuthenticatedClient();
        $channelParamsUpdater = $this->container->get(ChannelParamsUpdater::class);
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $gateChannel = $this->device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::CONTROLLINGTHEGATE;
        })->first();
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        // assign sensor to the gate from other device
        $channelParamsUpdater->updateChannelParams($gateChannel, new IODeviceChannelWithParams(0, 0, $sensorChannel->getId()));
        $this->getEntityManager()->refresh($gateChannel);
        $this->assertEquals($sensorChannel->getId(), $gateChannel->getParam3());
        $client->request('DELETE', '/api/iodevices/' . $anotherDevice->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->getEntityManager()->refresh($gateChannel);
        $this->assertEquals(0, $gateChannel->getParam3(), 'The paired sensor has not been cleared.');
    }
}

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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\AnyFieldSetter;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaDeveloperBundle\DataFixtures\ORM\NotificationsFixture;

/** @small */
class IODeviceControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
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
        $this->assertTrue(property_exists($content[0], 'channelsIds'));
        $this->assertEquals(ip2long($this->device->getRegIpv4()), $content[0]->regIpv4);
    }

    public function testGettingDevicesListVersion24() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices', [], [], $this->versionHeader(ApiVersions::V2_4()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertCount(1, $content);
        $this->assertEquals($this->device->getId(), $content[0]->id);
        $this->assertEquals($this->device->getSoftwareVersion(), $content[0]->softwareVersion);
        $this->assertFalse(property_exists($content[0], 'location'));
        $this->assertFalse(property_exists($content[0], 'channels'));
        $this->assertFalse(property_exists($content[0], 'channelsIds'));
        $this->assertEquals($this->device->getRegIpv4(), $content[0]->regIpv4);
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

    public function testGettingDevicesChannels() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices/' . $this->device->getId() . '/channels');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(5, $content);
    }

    public function testGettingDevicesDetailsWithLocation() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('GET', '/api/iodevices/' . $this->device->getId() . '?include=location');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($this->device->getId(), $content['id']);
        $this->assertArrayHasKey('relationsCount', $content['location']);
        $this->assertTrue($content['enterConfigurationModeAvailable']);
    }

    public function testGettingDeviceDetailsWhenFlagsIsNull() {
        EntityUtils::setField($this->device, 'flags', null);
        $this->getEntityManager()->persist($this->device);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('GET', '/api/iodevices/' . $this->device->getId() . '?include=location');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
    }

    public function testGettingDevicesDetailsByGuid() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices/' . $this->device->getGUIDString());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = current(json_decode($response->getContent(), true));
        $this->assertEquals($this->device->getId(), $content['id']);
    }

    public function testOtherUserCanHaveDeviceWithTheSameGuid() {
        $anotherUser = $this->createConfirmedUser('anotherguid@supla.org');
        $location = $this->createLocation($anotherUser);
        $device = $this->createDeviceFull($location);
        $this->getEntityManager()->getConnection()->exec("SELECT guid INTO @guid FROM supla_iodevice WHERE id={$this->device->getId()}");
        $this->getEntityManager()->getConnection()->exec("UPDATE supla_iodevice SET guid=@guid WHERE id={$device->getId()}");
        $this->getEntityManager()->clear();
        $client = $this->createAuthenticatedClient($anotherUser->getUsername());
        $client->request('GET', '/api/iodevices/' . $this->device->getGUIDString());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = current(json_decode($response->getContent(), true));
        $this->assertEquals($device->getId(), $content['id']);
        $this->testGettingDevicesDetailsByGuid();
    }

    public function testGettingDevicesDetailsByGuidWith0xPrefix() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices/0x' . $this->device->getGUIDString());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = current(json_decode($response->getContent(), true));
        $this->assertEquals($this->device->getId(), $content['id']);
    }

    public function testGettingDevicesDetailsByUnknownGuid() {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/iodevices/badguid' . $this->device->getGUIDString());
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
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

    public function testEnteringConfigurationMode() {
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), ['action' => 'enterConfigurationMode']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
    }

    public function testEnteringConfigurationModeWhenDeviceDoesNotSupportIt() {
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        AnyFieldSetter::set($device, 'flags', 0);
        $this->getEntityManager()->persist($device);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), ['action' => 'enterConfigurationMode']);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testEnteringConfigurationModeWhenSuplaServerRefuses() {
        SuplaServerMock::mockResponse('ENTER-CONFIGURATION-MODE', 'NO!');
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), ['action' => 'enterConfigurationMode']);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testDeletingDevice() {
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        $deviceId = $device->getId();
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/iodevices/' . $device->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
        $deviceIds = EntityUtils::mapToIds($this->user->getIODevices());
        $this->assertNotContains($deviceId, $deviceIds);
        return $deviceId;
    }

    /** @depends testDeletingDevice */
    public function testNotifiesSuplaServerAboutIoDeviceDeletion(int $deviceId) {
        $this->assertContains('USER-BEFORE-DEVICE-DELETE:1,' . $deviceId, SuplaServerMock::$executedCommands);
        $this->assertContains('USER-ON-DEVICE-DELETED:1,' . $deviceId, SuplaServerMock::$executedCommands);
    }

    public function testSuplaServerCanPreventDeviceDeletion() {
        $device = $this->createDeviceFull($this->freshEntity($this->location));
        SuplaServerMock::mockResponse('USER-BEFORE-DEVICE-DELETE:1,' . $device->getId(), 'NO!');
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/iodevices/' . $device->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testDeletingDeviceClearsRelatedGateOtherDevices() {
        $device = $this->createDeviceFull($this->freshEntity($this->location));
        $client = $this->createAuthenticatedClient();
        $paramConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->freshEntity($this->location), [
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $gateChannel = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::CONTROLLINGTHEGATE;
        })->first();
        $paramConfigTranslator->setConfig($gateChannel, ['openingSensorChannelId' => $sensorChannel->getId()]);
        $this->getEntityManager()->refresh($sensorChannel);
        $this->assertEquals($gateChannel->getId(), $sensorChannel->getParam1());
        $client->request('DELETE', '/api/iodevices/' . $device->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->getEntityManager()->refresh($sensorChannel);
        $this->assertEquals(0, $sensorChannel->getParam1(), 'The paired sensor has not been cleared.');
    }

    public function testDeletingDeviceClearsRelatedSensorInOtherDevices() {
        $client = $this->createAuthenticatedClient();
        $paramConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->freshEntity($this->location), [
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $gateChannel = $this->device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::CONTROLLINGTHEGATE;
        })->first();
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        // assign sensor to the gate from other device
        $paramConfigTranslator->setConfig($gateChannel, ['openingSensorChannelId' => $sensorChannel->getId()]);
        $this->getEntityManager()->refresh($gateChannel);
        $this->assertEquals($sensorChannel->getId(), $gateChannel->getParam2());
        $client->request('DELETE', '/api/iodevices/' . $anotherDevice->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->getEntityManager()->refresh($gateChannel);
        $this->assertEquals(0, $gateChannel->getParam2(), 'The paired sensor has not been cleared.');
    }

    public function testDeletingDeviceClearsRelatedSecondaryGateOtherDevices() {
        $device = $this->createDeviceFull($this->freshEntity($this->location));
        $client = $this->createAuthenticatedClient();
        $paramConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->freshEntity($this->location), [
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $gateChannel = $device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::CONTROLLINGTHEGATE;
        })->first();
        $paramConfigTranslator->setConfig($gateChannel, ['openingSensorSecondaryChannelId' => $sensorChannel->getId()]);
        $this->getEntityManager()->refresh($sensorChannel);
        $this->assertEquals($gateChannel->getId(), $sensorChannel->getParam2());
        $client->request('DELETE', '/api/iodevices/' . $device->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->getEntityManager()->refresh($sensorChannel);
        $this->assertEquals(0, $sensorChannel->getParam2(), 'The paired sensor has not been cleared.');
    }

    public function testDeletingDeviceClearsRelatedSecondarySensorInOtherDevices() {
        $client = $this->createAuthenticatedClient();
        $paramConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $this->simulateAuthentication($this->user);
        $anotherDevice = $this->createDevice($this->freshEntity($this->location), [
            [ChannelType::RELAY, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $sensorChannel = $anotherDevice->getChannels()[0];
        $gateChannel = $this->device->getChannels()->filter(function (IODeviceChannel $channel) {
            return $channel->getFunction()->getId() == ChannelFunction::CONTROLLINGTHEGATE;
        })->first();
        $gateChannel = $this->getEntityManager()->find(IODeviceChannel::class, $gateChannel->getId());
        // assign sensor to the gate from other device
        $paramConfigTranslator->setConfig($gateChannel, ['openingSensorSecondaryChannelId' => $sensorChannel->getId()]);
        $this->getEntityManager()->refresh($gateChannel);
        $this->assertEquals($sensorChannel->getId(), $gateChannel->getParam3());
        $client->request('DELETE', '/api/iodevices/' . $anotherDevice->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->getEntityManager()->refresh($gateChannel);
        $this->assertEquals(0, $gateChannel->getParam3(), 'The paired sensor has not been cleared.');
    }

    public function testDeletingWithAskingAboutDependencies() {
        $cg = new IODeviceChannelGroup($this->user, $this->location, [$this->device->getChannels()[0]]);
        $this->getEntityManager()->persist($cg);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/iodevices/' . $this->device->getId() . '?safe=yes');
        $this->assertStatusCode(409, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('sceneOperations', $content);
        $this->assertArrayHasKey('channelGroups', $content);
        $this->assertArrayHasKey('directLinks', $content);
        $this->assertArrayHasKey('schedules', $content);
        $this->assertArrayHasKey('reactions', $content);
        $this->assertCount(1, $content['channelGroups']);
        $this->assertEmpty($content['directLinks']);
        $this->assertEquals($cg->getId(), $content['channelGroups'][0]['id']);
        return $cg;
    }

    public function testTurningOffWithAskingAboutDependencies() {
        $cg = new IODeviceChannelGroup($this->user, $this->location, [$this->device->getChannels()[0]]);
        $this->getEntityManager()->persist($cg);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/iodevices/' . $this->device->getId() . '?safe=yes', ['enabled' => false]);
        $this->assertStatusCode(409, $client->getResponse());
        $this->assertTrue($this->getEntityManager()->find(IODevice::class, $this->device->getId())->getEnabled());
        $this->assertNotNull($this->getEntityManager()->find(IODeviceChannelGroup::class, $cg->getId()));
    }

    public function testTurningOffWithoutAskingAboutDependencies() {
        $cg = new IODeviceChannelGroup($this->user, $this->location, [$this->device->getChannels()[0]]);
        $this->getEntityManager()->persist($cg);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/iodevices/' . $this->device->getId(), ['enabled' => false]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertFalse($this->getEntityManager()->find(IODevice::class, $this->device->getId())->getEnabled());
        $this->assertNotNull($this->getEntityManager()->find(IODeviceChannelGroup::class, $cg->getId()));
    }

    public function testDeletingWithDependencies() {
        $device = $this->createDeviceFull($this->location);
        $cg = new IODeviceChannelGroup($this->user, $this->location, [$device->getChannels()[0]]);
        $this->getEntityManager()->persist($cg);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/iodevices/' . $device->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->assertNull($this->getEntityManager()->find(IODeviceChannelGroup::class, $cg->getId()));
    }

    public function testDeletingWithDependentScene() {
        $device = $this->createDeviceSonoff($this->location);
        $device2 = $this->createDeviceSonoff($this->location);
        $scene = new Scene($device->getLocation());
        $scene->setOpeartions([
            new SceneOperation($device->getChannels()[0], ChannelFunctionAction::TURN_ON()),
            new SceneOperation($device2->getChannels()[0], ChannelFunctionAction::TURN_ON()),
        ]);
        $this->getEntityManager()->persist($scene);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/iodevices/' . $device->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->assertNull($this->getEntityManager()->find(SceneOperation::class, $scene->getOperations()[0]->getId()));
        $this->assertContains('USER-ON-SCENE-CHANGED:1,' . $scene->getId(), SuplaServerMock::$executedCommands);
        $this->assertNotNull($this->getEntityManager()->find(Scene::class, $scene->getId()));
        $this->assertNotContains('USER-ON-SCENE-REMOVED:1,' . $scene->getId(), SuplaServerMock::$executedCommands);
        $scene = $this->freshEntity($scene);
        $this->assertCount(1, $scene->getOperations());
    }

    public function testDeletingDependentSceneIfSceneLeftEmpty() {
        $device = $this->createDeviceSonoff($this->location);
        $scene = new Scene($device->getLocation());
        $scene->setOpeartions([new SceneOperation($device->getChannels()[0], ChannelFunctionAction::TURN_ON())]);
        $this->getEntityManager()->persist($scene);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/iodevices/' . $device->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $this->assertNull($this->getEntityManager()->find(SceneOperation::class, $scene->getOperations()[0]->getId()));
        $this->assertNull($this->getEntityManager()->find(Scene::class, $scene->getId()));
        $this->assertContains('USER-ON-SCENE-REMOVED:1,' . $scene->getId(), SuplaServerMock::$executedCommands);
    }

    public function testGettingDevicesWithAllPossibleIncludesDoesNotCauseRecursion() {
        $newLocation = $this->createLocation($this->user);
        $channel = $this->device->getChannels()[0];
        $channel->setLocation($newLocation);
        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV23('GET', '/api/iodevices?include=channels,location,originalLocation,connected,state');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent());
        $this->assertNotEmpty($content);
        $this->assertEquals($this->device->getId(), $content[0]->id);
        $this->assertTrue(property_exists($content[0], 'location'));
        $this->assertTrue(property_exists($content[0], 'channels'));
        $this->assertFalse(property_exists($content[0]->channels[0], 'location'));
        $this->assertTrue(property_exists($content[0]->channels[0], 'locationId'));
        $this->assertTrue(property_exists($content[0]->channels[0], 'state'));
    }

    public function testGettingManagedNotificationsCount() {
        (new NotificationsFixture())->createDeviceNotification($this->getEntityManager(), $this->device);
        (new NotificationsFixture())->createChannelNotification($this->getEntityManager(), $this->device->getChannels()[0]);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('GET', '/api/iodevices/' . $this->device->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($this->device->getId(), $content['id']);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertArrayHasKey('channels', $content['relationsCount']);
        $this->assertArrayHasKey('managedNotifications', $content['relationsCount']);
        $this->assertEquals(5, $content['relationsCount']['channels']);
        $this->assertEquals(1, $content['relationsCount']['managedNotifications']);
    }
}

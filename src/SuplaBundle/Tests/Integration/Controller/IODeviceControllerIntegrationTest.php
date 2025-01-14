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
use SuplaBundle\Entity\Main\ValueBasedTrigger;
use SuplaBundle\Enums\ChannelConfigChangeScope;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\IoDeviceFlags;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\AnyFieldSetter;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestMailer;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\SuplaAssertions;
use SuplaDeveloperBundle\DataFixtures\ORM\DevicesFixture;
use SuplaDeveloperBundle\DataFixtures\ORM\NotificationsFixture;

/** @small */
class IODeviceControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use SuplaAssertions;

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
        $this->assertStringContainsString('not supported: turtles', $content->message);
        $this->assertStringContainsString('unicorns', $content->message);
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
        $this->assertArrayHasKey('config', $content);
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
        $this->assertSuplaCommandExecuted('ENTER-CONFIGURATION-MODE:1,' . $device->getId());
    }

    public function testEnteringConfigurationModeWhenDeviceDoesNotSupportIt() {
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        AnyFieldSetter::set($device, 'flags', 0);
        $this->persist($device);
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

    public function testRemoteRestartingDevice() {
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        AnyFieldSetter::set($device, 'flags', IoDeviceFlags::REMOTE_RESTART_AVAILABLE);
        $this->persist($device);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), ['action' => 'restartDevice']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertSuplaCommandExecuted('RESTART-DEVICE:1,' . $device->getId());
    }

    public function testRemoteRestartingDeviceWhenDeviceDoesNotSupportIt() {
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), ['action' => 'restartDevice']);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testRemoteRestartingDeviceWhenSuplaServerRefuses() {
        SuplaServerMock::mockResponse('RESTART-DEVICE', 'NO!');
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        AnyFieldSetter::set($device, 'flags', IoDeviceFlags::REMOTE_RESTART_AVAILABLE);
        $this->persist($device);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), ['action' => 'restartDevice']);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testPairingSubdevice() {
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        AnyFieldSetter::set($device, 'flags', IoDeviceFlags::PAIRING_SUBDEVICES_AVAILABLE);
        $this->persist($device);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), ['action' => 'pairSubdevice']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertSuplaCommandExecuted('PAIR-SUBDEVICE:1,' . $device->getId());
    }

    public function testIdentifyingDevice() {
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        AnyFieldSetter::set($device, 'flags', IoDeviceFlags::IDENTIFY_DEVICE_AVAILABLE);
        $this->persist($device);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), ['action' => 'identifyDevice']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertSuplaCommandExecuted('IDENTIFY-DEVICE:1,' . $device->getId());
    }

    public function testSettingDeviceTime() {
        $this->user->setTimezone('Europe/Warsaw');
        $this->persist($this->user);
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        $client = $this->createAuthenticatedClient();
        $request = ['action' => 'setTime', 'time' => '2023-11-02T22:07:25+02:00'];
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), $request);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertSuplaCommandExecuted("DEVICE-SET-TIME:1,{$device->getId()},2023,11,2,5,21,07,25,RXVyb3BlL1dhcnNhdw==");
    }

    public function testSettingDeviceTimeWithInvalidDate() {
        $this->user->setTimezone('Europe/Warsaw');
        $this->persist($this->user);
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        $client = $this->createAuthenticatedClient();
        $request = ['action' => 'setTime', 'time' => 'unicorn'];
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), $request);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testSettingDeviceTimeWhenServerRefuses() {
        SuplaServerMock::mockResponse('DEVICE-SET-TIME', 'NO!');
        $device = $this->createDeviceSonoff($this->freshEntity($this->location));
        $client = $this->createAuthenticatedClient();
        $request = ['action' => 'setTime', 'time' => (new \DateTime())->format(\DateTime::ATOM)];
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $device->getId(), $request);
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
        $this->flush();
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
        $this->flush();
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
        $this->flush();
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
        $this->flush();
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
        $this->assertArrayHasKey('conflictOn', $content);
        $this->assertArrayHasKey('dependencies', $content);
        $this->assertArrayHasKey('sceneOperations', $content['dependencies']);
        $this->assertArrayHasKey('channelGroups', $content['dependencies']);
        $this->assertArrayHasKey('directLinks', $content['dependencies']);
        $this->assertArrayHasKey('schedules', $content['dependencies']);
        $this->assertArrayHasKey('reactions', $content['dependencies']);
        $this->assertCount(1, $content['dependencies']['channelGroups']);
        $this->assertEmpty($content['dependencies']['directLinks']);
        $this->assertEquals($cg->getId(), $content['dependencies']['channelGroups'][0]['id']);
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

    public function testUpdatingDeviceConfig() {
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/iodevices/' . $this->device->getId(), ['config' => ['statusLed' => 'ALWAYS_OFF']]);
        $this->assertStatusCode(200, $client->getResponse());
        $device = $this->freshEntity($this->device);
        $this->assertEquals('ALWAYS_OFF', $device->getUserConfigValue('statusLed'));
    }

    public function testTurningOffWithChannelReactions() {
        $this->simulateAuthentication($this->user);
        $this->getEntityManager()->refresh($this->device);
        $this->device->setEnabled(true);
        $this->getEntityManager()->persist($this->device);
        $reaction = new ValueBasedTrigger($this->device->getChannels()[0]);
        $reaction->setTrigger(['on_change' => []]);
        $reaction->setSubject($this->device->getChannels()[1]);
        $reaction->setAction(ChannelFunctionAction::TURN_ON());
        $this->getEntityManager()->persist($reaction);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/iodevices/' . $this->device->getId(), ['enabled' => false]);
        $this->assertStatusCode(200, $client->getResponse());
        /** @var ValueBasedTrigger $reaction */
        $reaction = $this->getEntityManager()->find(ValueBasedTrigger::class, $reaction->getId());
        $this->assertFalse($reaction->isEnabled());
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

    public function testUpdatingConfigWithComparison() {
        $anotherDevice = $this->createDevice($this->getEntityManager()->find(Location::class, $this->location->getId()), [
            [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
        ]);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $anotherDevice->getId(), [
            'config' => ['statusLed' => 'ALWAYS_OFF'],
            'configBefore' => ['statusLed' => 'ON_WHEN_CONNECTED'],
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $anotherDevice = $this->freshEntity($anotherDevice);
        $this->assertEquals('ALWAYS_OFF', $anotherDevice->getUserConfigValue('statusLed'));
        $this->assertSuplaCommandExecuted('USER-ON-DEVICE-CONFIG-CHANGED:1,' . $anotherDevice->getId());
        $this->assertSuplaCommandNotExecuted('USER-RECONNECT:1');
        return $anotherDevice->getId();
    }

    /** @depends testUpdatingConfigWithComparison */
    public function testCantUpdateWithoutConfigBefore(int $deviceId) {
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $deviceId . '?safe=true', [
            'config' => ['statusLed' => 'OFF_WHEN_CONNECTED'],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    /** @depends testUpdatingConfigWithComparison */
    public function testCanUpdateCaptionWithoutConfigBefore(int $deviceId) {
        $client = $this->createAuthenticatedClient();
        SuplaServerMock::reset();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $deviceId, [
            'comment' => 'Unicorn device',
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $device = $this->getEntityManager()->find(IODevice::class, $deviceId);
        $this->assertEquals('Unicorn device', $device->getComment());
        $this->assertSuplaCommandNotExecuted('USER-ON-DEVICE-CONFIG-CHANGED:1,2');
        $this->assertSuplaCommandExecuted('USER-RECONNECT:1');
    }

    /** @depends testUpdatingConfigWithComparison */
    public function testUpdatingConfigWithConflictingConfigBefore(int $deviceId) {
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $deviceId, [
            'config' => ['statusLed' => 'OFF_WHEN_CONNECTED'],
            'configBefore' => ['statusLed' => 'ON_WHEN_CONNECTED'],
        ]);
        $this->assertStatusCode(409, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('details', $content);
        $this->assertEquals('ALWAYS_OFF', $content['details']['config']['statusLed']);
        $this->assertEquals('statusLed', $content['details']['conflictingField']);
    }

    public function testChangingLocation() {
        $device = $this->createDevice($this->createLocation($this->user), [
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATE],
        ]);
        $anotherLocation = $this->createLocation($this->user);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $device->getId() . '?safe=true', [
            'locationId' => $anotherLocation->getId(),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $device = $this->freshEntity($device);
        $this->assertEquals($anotherLocation->getId(), $device->getLocation()->getId());
        $this->assertSuplaCommandNotExecuted('USER-ON-DEVICE-CONFIG-CHANGED:1,2');
        $this->assertSuplaCommandExecuted('USER-RECONNECT:1');
    }

    public function testChangingLocationOfADeviceWithChannelRelationsSafe() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $location = $this->createLocation($this->user);
        $device1 = $this->createDevice($location, [[ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATE]]);
        $device2 = $this->createDevice($location, [[ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE]]);
        $sensorChannel = $device1->getChannels()[0];
        $gateChannel = $device2->getChannels()[0];
        $channelParamConfigTranslator->setConfig($gateChannel, ['openingSensorChannelId' => $sensorChannel->getId()]);
        $this->flush();
        $anotherLocation = $this->createLocation($this->user);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $device1->getId() . '?safe=true', [
            'locationId' => $anotherLocation->getId(),
        ]);
        $this->assertStatusCode(409, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent());
        $this->assertEquals([$sensorChannel->getId(), $gateChannel->getId()], array_column($content->dependencies->channels, 'id'));
        return [$device1->getId(), $device2->getId()];
    }

    /** @depends testChangingLocationOfADeviceWithChannelRelationsSafe */
    public function testChangingLocationOfADeviceWithChannelRelationsConfirm($ids) {
        [$device1Id, $device2Id] = $ids;
        $device1 = $this->freshEntityById(IODevice::class, $device1Id);
        $device2 = $this->freshEntityById(IODevice::class, $device2Id);
        $sensorChannel = $device1->getChannels()[0];
        $gateChannel = $device2->getChannels()[0];
        $anotherLocation = $this->createLocation($this->user);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $device1->getId(), [
            'locationId' => $anotherLocation->getId(),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertEquals($anotherLocation->getId(), $this->freshEntity($device1)->getLocation()->getId());
        $this->assertEquals($anotherLocation->getId(), $this->freshEntity($sensorChannel)->getLocation()->getId());
        $this->assertEquals($anotherLocation->getId(), $this->freshEntity($gateChannel)->getLocation()->getId());
        $this->assertSuplaCommandExecuted(sprintf(
            'USER-ON-CHANNEL-CONFIG-CHANGED:1,%d,%d,2900,20,%d',
            $gateChannel->getIoDevice()->getId(),
            $gateChannel->getId(),
            ChannelConfigChangeScope::LOCATION
        ));
        return [$device1->getId(), $device2->getId()];
    }

    /** @depends testChangingLocationOfADeviceWithChannelRelationsConfirm */
    public function testChangingLocationOfADeviceWithChannelRelationsButNotInheritedLocation($ids) {
        [$device1Id, $device2Id] = $ids;
        $device1 = $this->freshEntityById(IODevice::class, $device1Id);
        $device2 = $this->freshEntityById(IODevice::class, $device2Id);
        $sensorChannel = $device1->getChannels()[0];
        $sensorChannel->setLocation($device1->getLocation());
        $this->persist($sensorChannel);
        $gateChannel = $device2->getChannels()[0];
        $anotherLocation = $this->createLocation($this->user);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $device1->getId() . '?safe=true', [
            'locationId' => $anotherLocation->getId(),
        ]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertEquals($anotherLocation->getId(), $this->freshEntity($device1)->getLocation()->getId());
        $this->assertEquals($sensorChannel->getLocation()->getId(), $this->freshEntity($sensorChannel)->getLocation()->getId());
        $this->assertEquals($sensorChannel->getLocation()->getId(), $this->freshEntity($gateChannel)->getLocation()->getId());
    }

    public function testChangingLocationOfADeviceWithChannelRelationsToEachOtherSafe() {
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [
            [ChannelType::SENSORNO, ChannelFunction::OPENINGSENSOR_GATE],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
        ]);
        $sensorChannel = $device->getChannels()[0];
        $gateChannel = $device->getChannels()[1];
        $channelParamConfigTranslator->setConfig($gateChannel, ['openingSensorChannelId' => $sensorChannel->getId()]);
        $this->flush();
        $anotherLocation = $this->createLocation($this->user);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $device->getId() . '?safe=true', ['locationId' => $anotherLocation->getId()]);
        $this->assertStatusCode(409, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent());
        $this->assertEquals([$sensorChannel->getId(), $gateChannel->getId()], array_column($content->dependencies->channels, 'id'));
        return [$device->getId()];
    }

    /** @depends testChangingLocationOfADeviceWithChannelRelationsToEachOtherSafe */
    public function testChangingLocationOfADeviceWithChannelRelationsToEachOtherConfirm(array $ids) {
        [$deviceId] = $ids;
        $device = $this->freshEntityById(IODevice::class, $deviceId);
        $sensorChannel = $device->getChannels()[0];
        $gateChannel = $device->getChannels()[1];
        $anotherLocation = $this->createLocation($this->user);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('PUT', '/api/iodevices/' . $device->getId(), ['locationId' => $anotherLocation->getId()]);
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertEquals($anotherLocation->getId(), $this->freshEntity($device)->getLocation()->getId());
        $this->assertEquals($anotherLocation->getId(), $this->freshEntity($sensorChannel)->getLocation()->getId());
        $this->assertEquals($anotherLocation->getId(), $this->freshEntity($gateChannel)->getLocation()->getId());
    }

    public function testRequestingDeviceUnlockCode() {
        SuplaAutodiscoverMock::mockResponse('unlock-device', ['unlock_code' => 'abcdefg'], 200, 'POST');
        $lockedDevice = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceLocked($this->location);
        $user = $lockedDevice->getUser();
        $user->setLocale('pl');
        $this->persist($user);
        $client = $this->createAuthenticatedClient();
        $request = ['action' => 'unlock', 'email' => 'installer@supla.org'];
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $lockedDevice->getId(), $request);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        /** @var IODevice $lockedDevice */
        $lockedDevice = $this->freshEntity($lockedDevice);
        $this->assertTrue($lockedDevice->isLocked());
        $this->assertTrue($lockedDevice->isEnterConfigurationModeAvailable());
        $this->flushMessagesQueue($client);
        $this->assertNotEmpty(TestMailer::getMessages());
        $confirmationMessage = TestMailer::getMessages()[0];
        $this->assertEquals('installer@supla.org', $confirmationMessage->getTo()[0]->getAddress());
        $this->assertStringContainsString('Odblokowanie urządzenia przeznaczonego', $confirmationMessage->getSubject());
        $this->assertStringContainsString($lockedDevice->getName(), $confirmationMessage->getHtmlBody());
        $this->assertStringContainsString($lockedDevice->getGUIDString(), $confirmationMessage->getHtmlBody());
        $this->assertStringContainsString('/abcdefg', $confirmationMessage->getHtmlBody());
        $this->assertStringContainsString('link nie działa', $confirmationMessage->getHtmlBody());
        return $lockedDevice->getId();
    }

    /** @depends testRequestingDeviceUnlockCode */
    public function testCantUnlockDeviceWithBadCode(int $deviceId) {
        SuplaAutodiscoverMock::mockResponse('unlock-device', null, 404, 'POST');
        $lockedDevice = $this->freshEntityById(IODevice::class, $deviceId);
        $client = $this->createAuthenticatedClient();
        $request = ['code' => 'abcdefg'];
        $client->apiRequestV24('PATCH', '/api/confirm-device-unlock/' . $lockedDevice->getId(), $request);
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
        /** @var IODevice $lockedDevice */
        $lockedDevice = $this->freshEntity($lockedDevice);
        $this->assertTrue($lockedDevice->isLocked());
        $this->assertTrue($lockedDevice->isEnterConfigurationModeAvailable());
    }

    /** @depends testRequestingDeviceUnlockCode */
    public function testUnlockingDeviceWithCode(int $deviceId) {
        $lockedDevice = $this->freshEntityById(IODevice::class, $deviceId);
        $client = $this->createAuthenticatedClient();
        $request = ['code' => 'abcdefg'];
        TestMailer::reset();
        $client->apiRequestV24('PATCH', '/api/confirm-device-unlock/' . $lockedDevice->getId(), $request);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        /** @var IODevice $lockedDevice */
        $lockedDevice = $this->freshEntity($lockedDevice);
        $this->assertFalse($lockedDevice->isLocked());
        $this->assertTrue($lockedDevice->isEnterConfigurationModeAvailable());
        $this->flushMessagesQueue($client);
        $this->assertNotEmpty(TestMailer::getMessages());
        $confirmationMessage = TestMailer::getMessages()[0];
        $this->assertEquals($lockedDevice->getUser()->getEmail(), $confirmationMessage->getTo()[0]->getAddress());
        $this->assertStringContainsString('zostało odblokowane', $confirmationMessage->getSubject());
        $this->assertStringContainsString($lockedDevice->getName(), $confirmationMessage->getHtmlBody());
    }

    public function testDoesNotUnlockDeviceWhenAdRefuses() {
        $lockedDevice = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceLocked($this->location);
        $client = $this->createAuthenticatedClient();
        $request = ['action' => 'unlock', 'email' => 'installer@supla.org'];
        SuplaAutodiscoverMock::mockResponse('unlock-device', null, 404, 'POST');
        $client->apiRequestV24('PATCH', '/api/iodevices/' . $lockedDevice->getId(), $request);
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
        /** @var IODevice $lockedDevice */
        $lockedDevice = $this->freshEntity($lockedDevice);
        $this->assertTrue($lockedDevice->isLocked());
        $this->assertTrue($lockedDevice->isEnterConfigurationModeAvailable());
        $this->flushMessagesQueue($client);
        $this->assertEmpty(TestMailer::getMessages());
    }

    public function testDeletingDeviceWithScheduleAndSceneScheduleDependencies() {
        $sonoff = $this->createDeviceSonoff($this->location);
        $channel = $sonoff->getChannels()[0];
        $schedule = $this->createSchedule($channel, '*/5 * * * *');
        $scene = $this->createScene($this->location, $channel, $schedule);
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/iodevices/' . $sonoff->getId() . '?safe=1');
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('conflictOn', $content);
        $this->assertArrayHasKey('dependencies', $content);
        $this->assertCount(1, $content['dependencies']['sceneOperations']);
        $this->assertCount(1, $content['dependencies']['schedules']);
//        $this->assertEquals($cg->getId(), $content['dependencies']['channelGroups'][0]['id']);
        $client->request('DELETE', '/api/iodevices/' . $sonoff->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
//        $deviceIds = EntityUtils::mapToIds($this->user->getIODevices());
//        $this->assertNotContains($deviceId, $deviceIds);
//        return $deviceId;
    }

    public function testDeviceWithConflictingChannels() {
        $sonoff = $this->createDeviceSonoff($this->location);
        $channel = $sonoff->getChannels()[0];
        EntityUtils::setField($channel, 'conflictDetails', '{"type": 2000}');
        $this->persist($channel);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('GET', "/api/iodevices/{$sonoff->getId()}");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertArrayHasKey('channelsWithConflict', $content['relationsCount']);
        $this->assertEquals(1, $content['relationsCount']['channelsWithConflict']);
    }

    public function testFetchingSubDevices() {
        $device = (new DevicesFixture())->setObjectManager($this->getEntityManager())->createDeviceGateway($this->device->getLocation());
        $this->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV3('GET', "/api/subdevices");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $subDevices = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(3, $subDevices);
        $this->assertEquals([1, 3, 4], array_column($subDevices, 'id'));
        $this->assertEquals('My Cool Subdevice', $subDevices[0]['name']);
        return $device->getId();
    }

    /** @depends testFetchingSubDevices */
    public function testDeletingDeviceDeletesItsSubDevices(int $deviceId) {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/iodevices/' . $deviceId);
        $this->assertStatusCode(204, $client->getResponse());
        $client->apiRequestV3('GET', "/api/subdevices");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $subDevices = json_decode($client->getResponse()->getContent(), true);
        $this->assertEmpty($subDevices);
    }
}

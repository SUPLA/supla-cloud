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

use SuplaBundle\Entity\Main\DirectLink;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

/** @small */
class SceneControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var IODeviceChannelGroup */
    private $channelGroup;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::DIMMERANDRGBLIGHTING],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::THERMOMETER, ChannelFunction::THERMOMETER],
        ]);
        $this->channelGroup = new IODeviceChannelGroup($this->user, $location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[1],
        ]);
        $this->getEntityManager()->persist($this->channelGroup);
        $this->getEntityManager()->flush();
    }

    // upgrade, dude :-)
    public function testCreatingSceneIn23Fails() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV23('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[0]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::TURN_ON,
                ],
            ],
        ]);
        $this->assertStatusCode(404, $client->getResponse());
    }

    public function testCreatingScene() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[0]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::TURN_ON,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My scene', $content['caption']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
        return $content;
    }

    /** @depends testCreatingScene */
    public function testNotifiesSuplaServerAboutSceneCreated(array $response) {
        $this->assertContains('USER-ON-SCENE-ADDED:1,' . $response['id'], SuplaServerMock::$executedCommands);
    }

    public function testCreatingSceneWithOpenGateAction() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[3]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::OPEN,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My scene', $content['caption']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
        return $content;
    }

    /** @depends testCreatingScene */
    public function testGettingSceneDetails($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/scenes/' . $id . '?include=subject,operations');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals('My scene', $content['caption']);
        $this->assertCount(1, $content['operations']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
        $this->assertArrayNotHasKey('id', $content['operations'][0]);
        $this->assertArrayHasKey('config', $content);
        $this->assertArrayHasKey('googleHome', $content['config']);
        $this->assertArrayHasKey('alexa', $content['config']);
    }

    /** @depends testCreatingScene */
    public function testGettingScenesList($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/scenes');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertEquals($id, $content[0]['id']);
    }

    /** @depends testCreatingScene */
    public function testGettingScenesListForChannel($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', sprintf('/api/channels/%d/scenes', $this->device->getChannels()[0]->getId()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals($id, $content[0]['id']);
    }

    /** @depends testCreatingScene */
    public function testGettingScenesListForChannel2() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', sprintf('/api/channels/%d/scenes', $this->device->getChannels()[1]->getId()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(0, $content);
    }

    /** @depends testCreatingScene */
    public function testUpdatingSceneDetails($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id, [
            'caption' => 'My scene 2',
            'enabled' => false,
            'altIcon' => 1,
            'operations' => $sceneDetails['operations'],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals('My scene 2', $content['caption']);
        $this->assertEquals(1, $content['altIcon']);
        $this->assertFalse($content['enabled']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
        return $content;
    }

    /** @depends testCreatingScene */
    public function testUpdatingSceneActiveFromTo($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id, [
            'activeFrom' => "2025-01-15T23:30:00+00:00",
            'activeTo' => "2027-01-15T23:30:00+00:00",
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals('2025-01-15T23:30:00+00:00', $content['activeFrom']);
        $this->assertEquals('2027-01-15T23:30:00+00:00', $content['activeTo']);
    }

    /** @depends testCreatingScene */
    public function testUpdatingSceneActiveHours($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id, [
            'activeHours' => [1 => [12, 15, 18], 3 => [1, 3, 4]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals([1 => [12, 15, 18], 3 => [1, 3, 4]], $content['activeHours']);
    }

    /** @depends testCreatingScene */
    public function testUpdatingSceneActivityConditions($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id, [
            'activityConditions' => [[['afterSunrise' => 27], ['beforeSunset' => 11]]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals([[['afterSunrise' => 27], ['beforeSunset' => 11]]], $content['activityConditions']);
    }

    /** @depends testCreatingScene */
    public function testUpdatingSceneActivityConditionsToInvalid($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id, [
            'activityConditions' => [[['afterUnicorn' => 27], ['beforeSunset' => 11]]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    /** @depends testUpdatingSceneDetails */
    public function testUpdatingSceneDetailsCaptionOnly($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id, ['caption' => 'My scene 3']);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals('My scene 3', $content['caption']);
        $this->assertEquals(1, $content['altIcon']);
        $this->assertFalse($content['enabled']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
    }

    /** @depends testUpdatingSceneDetails */
    public function testNotifiesSuplaServerAboutSceneUpdated(array $response) {
        $this->assertContains('USER-ON-SCENE-CHANGED:1,' . $response['id'], SuplaServerMock::$executedCommands);
    }

    /** @depends testCreatingScene */
    public function testAddingOperationsToScene($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id . '?include=operations,subject', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[0]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::TURN_ON,
                ],
                [
                    'subjectId' => $this->device->getChannels()[1]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::TURN_OFF,
                    'delayMs' => 1000,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertCount(2, $content['operations']);
        $operation = $content['operations'][0];
        $this->assertEquals($this->device->getChannels()[0]->getId(), $operation['subjectId']);
        $this->assertEquals($this->device->getChannels()[0]->getId(), $operation['subject']['id']);
        $this->assertEquals(ActionableSubjectType::CHANNEL, $operation['subjectType']);
        $this->assertEquals(ChannelFunctionAction::TURN_ON, $operation['actionId']);
        $this->assertNull($operation['actionParam']);
        $this->assertEquals(0, $operation['delayMs']);
        $operation = $content['operations'][1];
        $this->assertEquals($this->device->getChannels()[1]->getId(), $operation['subjectId']);
        $this->assertEquals($this->device->getChannels()[1]->getId(), $operation['subject']['id']);
        $this->assertEquals(ActionableSubjectType::CHANNEL, $operation['subjectType']);
        $this->assertEquals(ChannelFunctionAction::TURN_OFF, $operation['actionId']);
        $this->assertNull($operation['actionParam']);
        $this->assertEquals(1000, $operation['delayMs']);
        return $sceneDetails;
    }

    /** @depends testAddingOperationsToScene */
    public function testGettingSceneDetailsWithOperations($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/scenes/' . $id . '?include=subject,operations');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(2, $content['relationsCount']['operations']);
        $this->assertCount(2, $content['operations']);
    }

    /** @depends testAddingOperationsToScene */
    public function testGettingSceneDetailsWithState($sceneDetails) {
        $id = $sceneDetails['id'];
        SuplaServerMock::mockResponse("GET-SCENE-SUMMARY:1,$id", "SUMMARY:$id,2,3," . base64_encode('unicorn') . ',5,6');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/scenes/' . $id . '?include=state');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('state', $content);
        $this->assertArrayHasKey('executing', $content['state']);
        $this->assertTrue($content['state']['executing']);
        $this->assertEquals([
            'executing' => true,
            'initiatorTypeId' => 2,
            'initiatorType' => 'CLIENT',
            'initiatorId' => 3,
            'initiatorName' => 'unicorn',
            'millisecondsFromStart' => 5,
            'millisecondsToEnd' => 6,
        ], $content['state']);
    }

    /** @depends testCreatingScene */
    public function testExecutingScene(array $sceneDetails) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/' . $sceneDetails['id'], ['action' => 'execute']);
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $lastCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('EXECUTE-SCENE:1,1', $lastCommand);
    }

    /** @depends testCreatingScene */
    public function testExecutingSceneDuringExecution(array $sceneDetails) {
        SuplaServerMock::mockResponse('EXECUTE-SCENE:1,1', 'IS-DURING-EXECUTION:1');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/' . $sceneDetails['id'], ['action' => 'execute']);
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
    }

    /** @depends testCreatingScene */
    public function testExecutingSceneDuringInactivePeriod(array $sceneDetails) {
        SuplaServerMock::mockResponse('EXECUTE-SCENE:1,1', 'INACTIVE-PERIOD:1');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/' . $sceneDetails['id'], ['action' => 'execute']);
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
    }

    /** @depends testCreatingScene */
    public function testInterruptingScene(array $sceneDetails) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/' . $sceneDetails['id'], ['action' => 'interrupt']);
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $lastCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('INTERRUPT-SCENE:1,1', $lastCommand);
    }

    /** @depends testCreatingScene */
    public function testInterruptAndExecuteScene(array $sceneDetails) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/' . $sceneDetails['id'], ['action' => 'interrupt-and-execute']);
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $lastCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('INTERRUPT-AND-EXECUTE-SCENE:1,1', $lastCommand);
    }

    /** @depends testCreatingScene */
    public function testUpdatingSceneMultipleTimes(array $sceneDetails) {
        $this->testAddingOperationsToScene($sceneDetails);
        $this->testAddingOperationsToScene($sceneDetails);
        $this->testAddingOperationsToScene($sceneDetails);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/scenes/' . $sceneDetails['id'] . '?include=subject,operations');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content['operations']);
    }

    public function testDeletingScene() {
        $sceneDetails = $this->testCreatingScene();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('DELETE', '/api/scenes/' . $sceneDetails['id']);
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
        $this->assertNull($this->getEntityManager()->find(Scene::class, $sceneDetails['id']));
        $this->assertContains('USER-ON-SCENE-REMOVED:1,' . $sceneDetails['id'], SuplaServerMock::$executedCommands);
    }

    public function testDeletingSceneWithConfirmation() {
        $sceneDetails = $this->testCreatingScene();
        $scene = $this->getEntityManager()->find(Scene::class, $sceneDetails['id']);
        $link = new DirectLink($scene);
        $link->generateSlug(new PlaintextPasswordEncoder());
        $this->persist($link);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('DELETE', '/api/scenes/' . $sceneDetails['id'] . '?safe=true');
        $response = $client->getResponse();
        $this->assertStatusCode(409, $response);
        $this->assertNotNull($this->getEntityManager()->find(Scene::class, $sceneDetails['id']));
        $this->assertNotContains('USER-ON-SCENE-REMOVED:1,' . $sceneDetails['id'], SuplaServerMock::$executedCommands);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content['dependencies']['directLinks']);
        $this->assertCount(0, $content['dependencies']['sceneOperations']);
        $client->apiRequestV24('DELETE', '/api/scenes/' . $sceneDetails['id']);
        $this->assertStatusCode(204, $client->getResponse());
        $this->assertNull($this->getEntityManager()->find(Scene::class, $sceneDetails['id']));
        $this->assertNull($this->getEntityManager()->find(DirectLink::class, $link->getId()));
        $this->assertContains('USER-ON-SCENE-REMOVED:1,' . $sceneDetails['id'], SuplaServerMock::$executedCommands);
    }

    /** @depends testCreatingScene */
    public function testAddingOperationsWithParamsToScene($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id . '?include=operations,subject', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[2]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::SET_RGBW_PARAMETERS,
                    'actionParam' => ['brightness' => 55],
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertCount(1, $content['operations']);
        $operation = $content['operations'][0];
        $this->assertEquals($this->device->getChannels()[2]->getId(), $operation['subjectId']);
        $this->assertEquals($this->device->getChannels()[2]->getId(), $operation['subject']['id']);
        $this->assertEquals(ActionableSubjectType::CHANNEL, $operation['subjectType']);
        $this->assertEquals(ChannelFunctionAction::SET_RGBW_PARAMETERS, $operation['actionId']);
        $this->assertEquals(['brightness' => 55], $operation['actionParam']);
        $this->assertEquals(0, $operation['delayMs']);
    }

    /** @depends testCreatingScene */
    public function testAddingOperationsWithChannelAndChannelGroupToScene($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id . '?include=operations,subject', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[0]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::TURN_ON,
                ],
                [
                    'subjectId' => $this->channelGroup->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL_GROUP,
                    'actionId' => ChannelFunctionAction::TURN_ON,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertCount(2, $content['operations']);
        $operation = $content['operations'][1];
        $this->assertEquals($this->channelGroup->getId(), $operation['subjectId']);
        $this->assertEquals(ActionableSubjectType::CHANNEL_GROUP, $operation['subjectType']);
        return $sceneDetails;
    }

    /** @depends testAddingOperationsWithChannelAndChannelGroupToScene */
    public function testGettingScenesListForChannelGroup($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', sprintf('/api/channel-groups/%d/scenes', $this->channelGroup->getId()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals($id, $content[0]['id']);
        $this->assertEquals(2, $content[0]['relationsCount']['operations']);
    }

    /** @depends testCreatingScene */
    public function testAddingInvalidActionToOperation($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id . '?include=operations,subject', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[0]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::OPEN,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    /** @depends testCreatingScene */
    public function testAddingInvalidActionParamToOperation($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id . '?include=operations,subject', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[2]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::SET_RGBW_PARAMETERS,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    /** @depends testCreatingScene */
    public function testAddingInvalidSubjectParamToOperation($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id . '?include=operations,subject', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => 666,
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::TURN_ON,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
    }

    /** @depends testCreatingScene */
    public function testAddingNotMineChannelToOperation($sceneDetails) {
        $user = $this->createConfirmedUser('another@supla.org');
        $location = $this->createLocation($user);
        $device = $this->createDevice($location, [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]]);
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id . '?include=operations,subject', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $device->getChannels()[0]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::TURN_ON,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
    }

    /** @depends testCreatingScene */
    public function testCreatingSceneWithOtherScene(array $scene1Details) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $scene1Details['id'],
                    'subjectType' => ActionableSubjectType::SCENE,
                    'actionId' => ChannelFunctionAction::EXECUTE,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My scene', $content['caption']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
        return $content;
    }

    /** @depends testCreatingSceneWithOtherScene */
    public function testGettingScenesListForScene($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', sprintf('/api/scenes/%d/scenes', $sceneDetails['operations'][0]['subjectId']));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertEquals($id, $content[0]['id']);
    }

    /** @depends testCreatingScene */
    public function testCreatingSceneThatReferencesItselfIsForbidden(array $sceneDetails) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $sceneDetails['id'], [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $sceneDetails['id'],
                    'subjectType' => ActionableSubjectType::SCENE,
                    'actionId' => ChannelFunctionAction::EXECUTE,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    /**
     * Trying to create 3 -> 2 -> 1 -> 3 scene execution cycle.
     * @depends testCreatingSceneWithOtherScene
     */
    public function testCreatingSceneExecutionCycleIsForbidden(array $scene2Details) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $scene2Details['id'],
                    'subjectType' => ActionableSubjectType::SCENE,
                    'actionId' => ChannelFunctionAction::EXECUTE,
                ],
            ],
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $scene3Details = json_decode($client->getResponse()->getContent(), true);
        $scene1Id = $scene2Details['operations'][0]['subjectId'];
        // at this point, we have 3 -> 2 -> 1, trying to add 1 -> 3
        $client->apiRequestV24('PUT', '/api/scenes/' . $scene1Id, [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $scene3Details['id'],
                    'subjectType' => ActionableSubjectType::SCENE,
                    'actionId' => ChannelFunctionAction::EXECUTE,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('forbidden to have recursive', $content['message']);
    }

    /** @depends testCreatingSceneWithOtherScene */
    public function testGettingDetailsOfSceneThatExecutesSceneThatExecutesSceneYouKnowWhatIMean(array $sceneDetails) {
        $client = $this->createAuthenticatedClientDebug($this->user);
        for ($i = 0; $i < 10; $i++) {
            $client->apiRequestV24('POST', '/api/scenes', [
                'caption' => 'My scene',
                'enabled' => true,
                'operations' => [
                    [
                        'subjectId' => $sceneDetails['id'],
                        'subjectType' => ActionableSubjectType::SCENE,
                        'actionId' => ChannelFunctionAction::EXECUTE,
                    ],
                ],
            ]);
            $this->assertStatusCode(201, $client->getResponse());
            $sceneDetails = json_decode($client->getResponse()->getContent(), true);
        }
        $client->enableProfiler();
        $client->apiRequestV24('GET', '/api/scenes/' . $sceneDetails['id'] . '?include=subject,operations,location');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $this->assertLessThan(5000, strlen($response->getContent()));
        $body = json_decode($response->getContent(), 'true');
        $this->assertArrayHasKey('operations', $body);
        $this->assertArrayHasKey('relationsCount', $body);
        $profile = $client->getProfile();
        $this->assertNotNull($profile);
        $this->assertGreaterThan(1, $profile->getCollector('db')->getQueryCount());
        $this->assertLessThan(15, $profile->getCollector('db')->getQueryCount());
    }

    public function testCreatingSceneWithoutOperationsFails() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testCreatingSceneWithTooManyOperationsFails() {
        $operations = array_map(function (int $i) {
            return [
                'subjectId' => $this->device->getChannels()[0]->getId(),
                'subjectType' => ActionableSubjectType::CHANNEL,
                'actionId' => ChannelFunctionAction::TURN_ON,
                'delayMs' => 1000 * $i,
            ];
        }, range(1, 21));
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => $operations,
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Too many operations in this scene', $content['message']);
    }

    public function testCreatingSceneWithThermometerFails() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[4]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::READ,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Cannot execute an action on this subject.', $content['message']);
    }

    public function testCreatingSceneThatExecutesAnotherSceneTwice() {
        $scene1 = $this->testCreatingScene();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $scene1['id'],
                    'subjectType' => ActionableSubjectType::SCENE,
                    'actionId' => ChannelFunctionAction::EXECUTE,
                ],
                [
                    'subjectId' => $scene1['id'],
                    'subjectType' => ActionableSubjectType::SCENE,
                    'actionId' => ChannelFunctionAction::EXECUTE,
                    'delayMs' => 1000,
                ],
            ],
        ]);
        $this->assertStatusCode(201, $client->getResponse());
    }

    public function testCreatingSceneWithScheduleOperation() {
        $schedule = $this->createSchedule($this->channelGroup, '*/5 * * * *', [
            'mode' => ScheduleMode::MINUTELY,
        ]);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene with schedule',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $schedule->getId(),
                    'subjectType' => ActionableSubjectType::SCHEDULE,
                    'actionId' => ChannelFunctionAction::DISABLE,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My scene with schedule', $content['caption']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
        return $content;
    }

    public function testCreatingSceneWithDelayOnlyOperation() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $this->device->getChannels()[0]->getId(),
                    'subjectType' => ActionableSubjectType::CHANNEL,
                    'actionId' => ChannelFunctionAction::TURN_ON,
                ],
                [
                    'delayMs' => 222,
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My scene', $content['caption']);
        $this->assertEquals(2, $content['relationsCount']['operations']);
    }

    public function testCreatingSceneWithNotification() {
        $client = $this->createAuthenticatedClient($this->user);
        $aid = $this->user->getAccessIDS()[0];
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene with notification',
            'enabled' => true,
            'operations' => [
                [
                    'subjectType' => ActionableSubjectType::NOTIFICATION,
                    'actionId' => ChannelFunctionAction::SEND,
                    'actionParam' => ['body' => 'Sample notification', 'accessIds' => [['id' => $aid->getId()]]],
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My scene with notification', $content['caption']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
        /** @var PushNotification $notification */
        $notification = $this->getEntityManager()->find(PushNotification::class, 1);
        $this->assertNotNull($notification);
        $this->assertEquals('Sample notification', $notification->getBody());
        return $content;
    }

    /** @depends testCreatingSceneWithNotification */
    public function testEditingSceneWithNotification(array $scene) {
        $client = $this->createAuthenticatedClient($this->user);
        $aid = $this->user->getAccessIDS()[0];
        $client->apiRequestV24('PUT', "/api/scenes/$scene[id]", [
            'caption' => 'My scene with notification updated',
            'enabled' => true,
            'operations' => [
                [
                    'subjectType' => ActionableSubjectType::NOTIFICATION,
                    'actionId' => ChannelFunctionAction::SEND,
                    'actionParam' => ['body' => 'Another notification updated', 'accessIds' => [$aid->getId()]],
                ],
            ],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertTrue($content['enabled']);
        $this->assertEquals('My scene with notification updated', $content['caption']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
        /** @var PushNotification $notification */
        $notification = $this->getEntityManager()->find(PushNotification::class, 2);
        $this->assertNotNull($notification);
        $this->assertEquals('Another notification updated', $notification->getBody());
        $this->assertNull($this->getEntityManager()->find(PushNotification::class, 1));
    }

    public function testDeletingSceneThatIsUsedAsOnlyOperationInAnotherSceneThatIsReferencedByValueBasedTrigger() {
        $sceneOne = $this->testCreatingScene();
        $sceneOne = $this->getEntityManager()->find(Scene::class, $sceneOne['id']);
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/scenes?include=operations', [
            'caption' => 'My scene',
            'enabled' => true,
            'operations' => [
                [
                    'subjectId' => $sceneOne->getId(),
                    'subjectType' => $sceneOne->getOwnSubjectType(),
                    'actionId' => ChannelFunctionAction::EXECUTE,
                ],
            ],
        ]);
        $sceneTwo = json_decode($client->getResponse()->getContent(), true);
        $sceneTwo = $this->getEntityManager()->find(Scene::class, $sceneTwo['id']);
        $thermometer = $this->device->getChannels()[4];
        $client->apiRequestV24('POST', "/api/channels/{$thermometer->getId()}/reactions", [
            'subjectId' => $sceneTwo->getId(), 'subjectType' => $sceneTwo->getOwnSubjectType(),
            'actionId' => ChannelFunctionAction::EXECUTE,
            'trigger' => ['on_change_to' => ['lt' => 20, 'name' => 'temperature', 'resume' => ['ge' => 20]]],
        ]);
        SuplaServerMock::$executedCommands = [];
        // deleting scene one will result in deleting scene two (the only action) and in consequence - deletes also the reaction
        $client->apiRequestV24('DELETE', '/api/scenes/' . $sceneOne->getId());
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
        $this->assertNull($this->getEntityManager()->find(Scene::class, $sceneOne->getId()));
        $this->assertNull($this->getEntityManager()->find(Scene::class, $sceneTwo->getId()));
        $this->assertContains('USER-ON-SCENE-REMOVED:1,' . $sceneOne->getId(), SuplaServerMock::$executedCommands);
        $this->assertContains('USER-ON-SCENE-REMOVED:1,' . $sceneTwo->getId(), SuplaServerMock::$executedCommands);
        $client->apiRequestV24('GET', "/api/channels/{$thermometer->getId()}/reactions");
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEmpty($content);
        $this->assertContains('USER-ON-VBT-CHANGED:1', SuplaServerMock::$executedCommands);
    }
}

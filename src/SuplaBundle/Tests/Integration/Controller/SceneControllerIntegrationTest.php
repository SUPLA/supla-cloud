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
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class SceneControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
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
        $client = $this->createAuthenticatedClientDebug($this->user);
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
        $client = $this->createAuthenticatedClientDebug($this->user);
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
    public function testGettingSceneDetails($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClientDebug($this->user);
        $client->apiRequestV24('GET', '/api/scenes/' . $id . '?include=subject,operations');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals('My scene', $content['caption']);
        $this->assertCount(1, $content['operations']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
        $this->assertArrayNotHasKey('id', $content['operations'][0]);
    }

    /** @depends testCreatingScene */
    public function testUpdatingSceneDetails($sceneDetails) {
        $id = $sceneDetails['id'];
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/' . $id, [
            'caption' => 'My scene 2',
            'enabled' => false,
            'operations' => $sceneDetails['operations'],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($id, $content['id']);
        $this->assertEquals('My scene 2', $content['caption']);
        $this->assertFalse($content['enabled']);
        $this->assertEquals(1, $content['relationsCount']['operations']);
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

    /** @depends testCreatingScene */
    public function testExecutingScene(array $sceneDetails) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/' . $sceneDetails['id']);
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $lastCommand = end(SuplaServerMock::$executedCommands);
        $this->assertEquals('EXECUTE-SCENE:1,1', $lastCommand);
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
// TODO why? works in 300ef164bf5ba484f825b7a2f1ad7bc49284a9bc
//        $this->assertArrayNotHasKey('operations', $body['operations'][0]['subject']);
//        $this->assertArrayNotHasKey('relationsCount', $body['operations'][0]['subject']);
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
}

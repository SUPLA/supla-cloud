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
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\SuplaAssertions;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

/** @small */
class ChannelGroupControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use SuplaAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
        ]);
        $group1 = new IODeviceChannelGroup($this->user, $location, [
            $this->device->getChannels()[0],
            $this->device->getChannels()[1],
        ]);
        $group2 = new IODeviceChannelGroup($this->user, $location, [
            $this->device->getChannels()[2],
            $this->device->getChannels()[3],
        ]);
        $this->getEntityManager()->persist($group1);
        $this->getEntityManager()->persist($group2);
        $this->getEntityManager()->flush();
    }

    /** @dataProvider changingChannelGroupStateDataProvider */
    public function testChangingChannelGroupState(
        int $channelGroupId,
        string $action,
        string $expectedCommand,
        array $additionalRequest = []
    ) {
        $client = $this->createAuthenticatedClient($this->user);
        $request = array_merge(['action' => $action], $additionalRequest);
        $client->apiRequestV22('PATCH', '/api/channel-groups/' . $channelGroupId, $request);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $this->assertSuplaCommandExecuted($expectedCommand);
    }

    public static function changingChannelGroupStateDataProvider() {
        return [
            [1, 'turn-on', 'ACTION-CG-TURN-ON:1,1'],
            [1, 'turn-off', 'ACTION-CG-TURN-OFF:1,1'],
            [2, 'open', 'SET-CG-CHAR-VALUE:1,2,1'],
        ];
    }

    public function testCreatingChannelGroupV23() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV23('POST', '/api/channel-groups', ['channelsIds' => [$this->device->getChannels()[0]->getId()]]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('channelsIds', $content);
        return $content;
    }

    /** @depends testCreatingChannelGroupV23 */
    public function testUpdatingChannelGroupV23(array $cgData) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV23(
            'PUT',
            '/api/channel-groups/' . $cgData['id'],
            ['channelsIds' => [$this->device->getChannels()[0]->getId(), $this->device->getChannels()[1]->getId()]]
        );
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('channelsIds', $content);
        $this->assertEquals([1, 2], $content['channelsIds']);
    }

    public function testCreatingChannelGroupV24() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', '/api/channel-groups', ['channelsIds' => [$this->device->getChannels()[0]->getId()]]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayNotHasKey('channelsIds', $content);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertEquals(1, $content['relationsCount']['channels']);
        return $content;
    }

    /** @depends testCreatingChannelGroupV24 */
    public function testUpdatingChannelGroupV24(array $cgData) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24(
            'PUT',
            '/api/channel-groups/' . $cgData['id'],
            ['channelsIds' => [$this->device->getChannels()[0]->getId(), $this->device->getChannels()[1]->getId()]]
        );
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayNotHasKey('channelsIds', $content);
        $this->assertArrayHasKey('ownSubjectType', $content);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertEquals(2, $content['relationsCount']['channels']);
        $this->assertEquals(ActionableSubjectType::CHANNEL_GROUP, $content['ownSubjectType']);
        return $content;
    }

    /** @depends testUpdatingChannelGroupV24 */
    public function testUpdatingChannelGroupV24OnlyCaption(array $cgData) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24(
            'PUT',
            '/api/channel-groups/' . $cgData['id'],
            ['caption' => 'Nowy caption']
        );
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('ownSubjectType', $content);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertEquals(2, $content['relationsCount']['channels']);
        $this->assertEquals('Nowy caption', $content['caption']);
        $this->assertFalse($content['hidden']);
        $this->assertEquals(ActionableSubjectType::CHANNEL_GROUP, $content['ownSubjectType']);
    }

    /** @depends testCreatingChannelGroupV24 */
    public function testCantUpdateToNoChannels(array $cgData) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channel-groups/' . $cgData['id'], ['channelsIds' => []]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testGettingChannelGroupsV24() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channel-groups');
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertGreaterThan(1, count($content));
        $this->assertArrayNotHasKey('channelsIds', $content[0]);
        $this->assertArrayHasKey('relationsCount', $content[0]);
    }

    public function testGettingChannelGroupsForChannel() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', sprintf('/api/channels/%d/channel-groups', $this->device->getChannels()[2]->getId()));
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(1, $content);
        $this->assertArrayNotHasKey('channelsIds', $content[0]);
        $this->assertArrayHasKey('relationsCount', $content[0]);
        $this->assertEquals(2, $content[0]['relationsCount']['channels']);
    }

    public function testChannelsIncludeForbiddenOnListV24() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channel-groups?include=channels');
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
    }

    public function testGettingChannelGroupState() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channel-groups/1?include=state');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $this->assertSuplaCommandExecuted('GET-RELAY-VALUE:1,1,1');
        $this->assertSuplaCommandExecuted('GET-RELAY-VALUE:1,1,2');
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('state', $content);
        $this->assertCount(2, $content['state']);
        $this->assertArrayHasKey(1, $content['state']);
        $this->assertArrayHasKey('on', $content['state'][1]);
        $this->assertArrayHasKey(2, $content['state']);
        $this->assertArrayHasKey('on', $content['state'][2]);
    }

    public function testGettingChannelGroupWithLocationAndChannels() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV22('GET', '/api/channel-groups/1?include=location,channels');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('location', $content);
        $this->assertArrayNotHasKey('channels', $content['location']);
        $this->assertArrayHasKey('channels', $content);
        $this->assertArrayHasKey('channelsIds', $content['location']);
    }

    public function testGettingChannelGroupWithLocationAndChannelsV24() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channel-groups/1?include=location,channels');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('location', $content);
        $this->assertArrayNotHasKey('channels', $content['location']);
        $this->assertArrayHasKey('channels', $content);
        $this->assertArrayNotHasKey('channelsIds', $content['location']);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertArrayHasKey('relationsCount', $content['location']);
    }

    public function testGettingChannelGroupWithExplicitLocationAndChannelsNamespacesV24() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channel-groups/1?include=channelGroup.location,channelGroup.channels');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('location', $content);
        $this->assertArrayNotHasKey('channels', $content['location']);
        $this->assertArrayHasKey('channels', $content);
        $this->assertArrayNotHasKey('channelsIds', $content['location']);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertArrayHasKey('relationsCount', $content['location']);
    }

    public function testGettingNotExistingChannelGroup() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channel-groups/12345');
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
    }

    public function testGettingValidRelationsCountV24() {
        $cg = $this->getDoctrine()->getRepository(IODeviceChannelGroup::class)->find(1);
        $dl = new DirectLink($cg);
        $dl->generateSlug(new PlaintextPasswordEncoder());
        $this->getEntityManager()->persist($dl);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', '/api/channel-groups/1');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertEquals(1, $content['relationsCount']['directLinks']);
        $this->assertEquals(2, $content['relationsCount']['channels']);
        $this->assertEquals(0, $content['relationsCount']['schedules']);
    }

    public function testSettingConfigForActionTrigger() {
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $location = $this->user->getLocations()[0];
        $anotherDevice = $this->createDeviceSonoff($location);
        $group = new IODeviceChannelGroup($this->user, $location, [$anotherDevice->getChannels()[0]]);
        $this->getEntityManager()->persist($group);
        $this->getEntityManager()->flush();
        $trigger = $anotherDevice->getChannels()[2];
        $this->setActionTriggerForChannelGroup($group, $trigger);
        $trigger = $this->getEntityManager()->find(IODeviceChannel::class, $trigger->getId());
        $this->assertArrayHasKey('actions', $trigger->getUserConfig());
        $this->assertCount(1, $trigger->getUserConfig()['actions']);
        return [$trigger->getId(), $group->getId()];
    }

    private function setActionTriggerForChannelGroup(IODeviceChannelGroup $group, IODeviceChannel $trigger): void {
        $actions = ['TURN_ON' => [
            'subjectId' => $group->getId(), 'subjectType' => ActionableSubjectType::CHANNEL_GROUP,
            'action' => ['id' => $group->getPossibleActions()[0]->getId()]]];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', '/api/channels/' . $trigger->getId(), ['config' => ['actions' => $actions]]);
        $this->assertStatusCode(200, $client->getResponse());
    }

    /** @depends testSettingConfigForActionTrigger */
    public function testDeletingChannelGroupTriesToClearRelatedActionTriggers(array $params) {
        [$triggerId, $groupId] = $params;
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('DELETE', '/api/channel-groups/' . $groupId . '?safe=1');
        $this->assertStatusCode(409, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('actionTriggers', $content['dependencies']);
        $this->assertCount(1, $content['dependencies']['actionTriggers']);
        $this->assertEquals($triggerId, $content['dependencies']['actionTriggers'][0]['id']);
        return [$triggerId, $groupId];
    }

    /** @depends testDeletingChannelGroupTriesToClearRelatedActionTriggers */
    public function testDeletingChannelGroupClearsRelatedActionTriggers(array $params) {
        [$triggerId, $groupId] = $params;
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('DELETE', '/api/channel-groups/' . $groupId);
        $this->assertStatusCode(204, $client->getResponse());
        $trigger = $this->getEntityManager()->find(IODeviceChannel::class, $triggerId);
        $this->assertEmpty($trigger->getUserConfig()['actions']);
        $this->assertNull($this->getEntityManager()->find(IODeviceChannelGroup::class, $groupId));
    }

    public function testDeletingIoDeviceWithChannelDeletesAlsoTheChannelGroupThatContainedThisChannelOnlyAndClearsItsActionTriggers() {
        [$triggerInTheSameDeviceId, $groupId] = $this->testSettingConfigForActionTrigger();
        [$triggerInAnotherDeviceId,] = $this->testSettingConfigForActionTrigger();
        $triggerInAnotherDevice = $this->getEntityManager()->find(IODeviceChannel::class, $triggerInAnotherDeviceId);
        $group = $this->getEntityManager()->find(IODeviceChannelGroup::class, $groupId);
        $ioDevice = $group->getChannels()[0]->getIoDevice();
        $this->setActionTriggerForChannelGroup($group, $triggerInAnotherDevice);
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('DELETE', '/api/iodevices/' . $ioDevice->getId());
        $this->assertStatusCode(204, $client->getResponse());
        $triggerInTheSameDevice = $this->getEntityManager()->find(IODeviceChannel::class, $triggerInTheSameDeviceId);
        $triggerInAnotherDevice = $this->getEntityManager()->find(IODeviceChannel::class, $triggerInAnotherDeviceId);
        $this->assertNull($triggerInTheSameDevice);
        $this->assertNull($this->getEntityManager()->find(IODeviceChannelGroup::class, $group->getId()));
        $this->assertEmpty($triggerInAnotherDevice->getUserConfig()['actions']);
    }

    public function testDeletingChannelGroupTriesToClearRelatedReactions() {
        $this->user = $this->getEntityManager()->find(User::class, $this->user->getId());
        $location = $this->user->getLocations()[0];
        $anotherDevice = $this->createDeviceSonoff($location);
        $group = new IODeviceChannelGroup($this->user, $location, [$anotherDevice->getChannels()[0]]);
        $this->getEntityManager()->persist($group);
        $this->getEntityManager()->flush();
        $thermometer = $anotherDevice->getChannels()[1];
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('POST', "/api/channels/{$thermometer->getId()}/reactions", [
            'subjectId' => $group->getId(), 'subjectType' => $group->getOwnSubjectType(),
            'actionId' => ChannelFunctionAction::TURN_ON,
            'trigger' => ['on_change_to' => ['lt' => 20, 'name' => 'temperature', 'resume' => ['ge' => 20]]],
        ]);
        $this->assertStatusCode(201, $client->getResponse());
        $client->apiRequestV24('DELETE', '/api/channel-groups/' . $group->getId() . '?safe=1');
        $this->assertStatusCode(409, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('reactions', $content['dependencies']);
        $this->assertCount(1, $content['dependencies']['reactions']);
        return [$thermometer->getId(), $group->getId()];
    }

    /** @depends testDeletingChannelGroupTriesToClearRelatedReactions */
    public function testDeletingChannelGroupDeletesRelatedReactions(array $params) {
        [$thermometerId, $groupId] = $params;
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('DELETE', '/api/channel-groups/' . $groupId);
        $this->assertStatusCode(204, $client->getResponse());
        $client->apiRequestV24('GET', "/api/channels/{$thermometerId}/reactions");
        $this->assertStatusCode(200, $client->getResponse());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEmpty($content);
        $this->assertContains('USER-ON-VBT-CHANGED:1', SuplaServerMock::$executedCommands);
    }
}

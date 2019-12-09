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
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;

/** @small */
class ChannelGroupControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
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
        $client->enableProfiler();
        $request = array_merge(['action' => $action], $additionalRequest);
        $client->apiRequestV22('PATCH', '/api/channel-groups/' . $channelGroupId, $request);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains($expectedCommand, $commands);
    }

    public function changingChannelGroupStateDataProvider() {
        return [
            [1, 'turn-on', 'SET-CG-CHAR-VALUE:1,1,1'],
            [1, 'turn-off', 'SET-CG-CHAR-VALUE:1,1,0'],
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
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertEquals(2, $content['relationsCount']['channels']);
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
        $client->apiRequestV24('GET', '/api/channel-groups?channelId=' . $this->device->getChannels()[2]->getId());
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
        $client->enableProfiler();
        $client->apiRequestV22('GET', '/api/channel-groups/1?include=state');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains('GET-CHAR-VALUE:1,1,1', $commands);
        $this->assertContains('GET-CHAR-VALUE:1,1,2', $commands);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('state', $content);
        $this->assertCount(2, $content['state']);
        $this->assertArrayHasKey(1, $content['state']);
        $this->assertArrayHasKey('on', $content['state'][1]);
        $this->assertArrayHasKey(2, $content['state']);
        $this->assertArrayHasKey('on', $content['state'][2]);
    }

    public function testGettingChannelGroupWithLocationAndChannels() {
        $client = $this->createAuthenticatedClientDebug($this->user);
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
        $client = $this->createAuthenticatedClientDebug($this->user);
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
        $client = $this->createAuthenticatedClientDebug($this->user);
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
        $client = $this->createAuthenticatedClientDebug($this->user);
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
        $client = $this->createAuthenticatedClientDebug($this->user);
        $client->apiRequestV24('GET', '/api/channel-groups/1');
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertEquals(1, $content['relationsCount']['directLinks']);
        $this->assertEquals(2, $content['relationsCount']['channels']);
        $this->assertEquals(0, $content['relationsCount']['schedules']);
    }
}

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
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

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

    /**
     * @dataProvider changingChannelGroupStateDataProvider
     */
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
}

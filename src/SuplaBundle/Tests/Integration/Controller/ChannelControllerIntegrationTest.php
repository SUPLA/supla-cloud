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

use SuplaApiBundle\Model\ApiVersions;
use SuplaApiBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;

class ChannelControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $this->device = $this->createDevice($location, [
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
            [4, 'shut', 'SET-CHAR-VALUE:1,1,4,10'],
            [4, 'reveal', 'SET-CHAR-VALUE:1,1,4,110'],
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
}

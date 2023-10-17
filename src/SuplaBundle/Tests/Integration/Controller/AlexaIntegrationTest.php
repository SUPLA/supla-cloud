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

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpFoundation\Response;

/** @small */
class AlexaIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var \SuplaBundle\Entity\Main\Location */
    private $location;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannelGroup */
    private $channelGroup;
    /** @var Scene */
    private $scene;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
        $this->device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER],
            [ChannelType::DIMMERANDRGBLED, ChannelFunction::DIMMERANDRGBLIGHTING],
        ]);
        $this->channelGroup = new IODeviceChannelGroup($this->user, $this->location, [$this->device->getChannels()[0]]);
        $this->persist($this->channelGroup);
        $this->scene = new Scene($this->device->getLocation());
        $this->scene->setOpeartions([new SceneOperation($this->channelGroup, ChannelFunctionAction::TURN_ON())]);
        $this->persist($this->scene);
    }

    public function testChangingChannelStateWithAlexa() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'turn-on',
            'alexaCorrelationToken' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'ACTION-TURN-ON:1,1,1,ALEXA-CORRELATION-TOKEN=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testChangingChannelStateWithAlexaAndParams() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/channels/2', json_encode([
            'action' => ChannelFunctionAction::SHUT_PARTIALLY,
            'percentage' => 45,
            'alexaCorrelationToken' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'SET-CHAR-VALUE:1,1,2,55,ALEXA-CORRELATION-TOKEN=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testChangingChannelGroupStateWithAlexa() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/channel-groups/1', json_encode([
            'action' => 'turn-on',
            'alexaCorrelationToken' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'ACTION-CG-TURN-ON:1,1,ALEXA-CORRELATION-TOKEN=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testExecutingSceneWithAlexa() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/scenes/1', json_encode([
            'action' => 'execute',
            'alexaCorrelationToken' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'EXECUTE-SCENE:1,1,ALEXA-CORRELATION-TOKEN=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testSettingAlexaConfigDisabled() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/1', [
            'config' => ['alexa' => ['alexaDisabled' => true]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $channel = $this->freshEntity($this->device->getChannels()[0]);
        $channelConfig = $channel->getUserConfig();
        $this->assertArrayHasKey('alexa', $channelConfig);
        $this->assertTrue($channelConfig['alexa']['alexaDisabled']);
    }

    /** @depends testSettingAlexaConfigDisabled */
    public function testExecutingActionWithAlexaDisabledChannel() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'turn-on',
            'alexaCorrelationToken' => 'unicorn',
        ]));
        $this->assertStatusCode(Response::HTTP_CONFLICT, $client);
    }

    /** @depends testSettingAlexaConfigDisabled */
    public function testExecutingActionWithAlexaDisabledChannelButWithoutAlexa() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'turn-on',
        ]));
        $this->assertStatusCode(Response::HTTP_ACCEPTED, $client);
    }

    public function testFetchingChannelsForAlexaIntegration() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/1', [
            'config' => ['alexa' => ['alexaDisabled' => false]],
        ]);
        $client->apiRequestV24('GET', '/api/channels?forIntegration=alexa');
        $this->assertCount(3, $client->getResponseBody());
        $client->apiRequestV24('PUT', '/api/channels/1', [
            'config' => ['alexa' => ['alexaDisabled' => true]],
        ]);
        $client->apiRequestV24('GET', '/api/channels?forIntegration=alexa');
        $fetchedChannels = $client->getResponseBody();
        $this->assertCount(2, $fetchedChannels);
        $this->assertNotContains(1, array_column($fetchedChannels, 'id'));
    }

    public function testSettingAlexaConfigDisabledForScene() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/1', [
            'config' => ['alexa' => ['alexaDisabled' => true]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $scene = $this->freshEntity($this->scene);
        $sceneConfig = $scene->getUserConfig();
        $this->assertArrayHasKey('alexa', $sceneConfig);
        $this->assertTrue($sceneConfig['alexa']['alexaDisabled']);
    }

    public function testFetchingScenesForAlexaIntegration() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/1', ['config' => ['alexa' => ['alexaDisabled' => false]]]);
        $client->apiRequestV24('GET', '/api/scenes?forIntegration=alexa');
        $this->assertCount(1, $client->getResponseBody());
        $client->apiRequestV24('PUT', '/api/scenes/1', ['config' => ['alexa' => ['alexaDisabled' => true]],]);
        $client->apiRequestV24('GET', '/api/scenes?forIntegration=alexa');
        $fetchedChannels = $client->getResponseBody();
        $this->assertCount(0, $fetchedChannels);
    }
}

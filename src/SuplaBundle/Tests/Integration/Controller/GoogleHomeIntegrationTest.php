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
use SuplaBundle\Entity\Location;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Entity\SceneOperation;
use SuplaBundle\Entity\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\HttpFoundation\Response;

/** @small */
class GoogleHomeIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;
    /** @var IODeviceChannelGroup */
    private $channelGroup;
    /** @var Scene */
    private $scene;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
        $this->device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEGATE],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEROLLERSHUTTER],
            [ChannelType::RELAY, ChannelFunction::CONTROLLINGTHEDOORLOCK],
        ]);
        $this->channelGroup = new IODeviceChannelGroup($this->user, $this->location, [$this->device->getChannels()[0]]);
        $this->persist($this->channelGroup);
        $this->scene = new Scene($this->device->getLocation());
        $this->scene->setOpeartions([new SceneOperation($this->channelGroup, ChannelFunctionAction::TURN_ON())]);
        $this->persist($this->scene);
    }

    public function testChangingChannelStateWithGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'SET-CHAR-VALUE:1,1,1,1,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testChangingChannelStateWithGoogleAndParams() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/channels/2', json_encode([
            'action' => ChannelFunctionAction::SHUT_PARTIALLY,
            'percentage' => 45,
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'SET-CHAR-VALUE:1,1,2,55,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testChangingChannelGroupStateWithGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/channel-groups/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'SET-CG-CHAR-VALUE:1,1,1,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testExecutingSceneWithGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->enableProfiler();
        $client->apiRequestV24('PATCH', '/api/scenes/1', json_encode([
            'action' => 'execute',
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $commands = $this->getSuplaServerCommands($client);
        $this->assertContains(
            'EXECUTE-SCENE:1,1,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'),
            $commands,
            implode(PHP_EOL, $commands)
        );
    }

    public function testSettingGoogleHomeConfigDisabledForRollerShutter() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/2', [
            'config' => ['googleHome' => ['googleHomeDisabled' => true]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $channel = $this->freshEntity($this->device->getChannels()[1]);
        $channelConfig = $channel->getUserConfig();
        $this->assertArrayHasKey('googleHome', $channelConfig);
        $this->assertTrue($channelConfig['googleHome']['googleHomeDisabled']);
        $this->assertArrayNotHasKey('needsUserConfirmation', $channelConfig['googleHome']);
    }

    public function testSettingGoogleHomeConfigDisabledForUnsupportedDoor() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/3', [
            'config' => ['googleHome' => ['googleHomeDisabled' => true]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $channel = $this->freshEntity($this->device->getChannels()[2]);
        $channelConfig = $channel->getUserConfig();
        $this->assertArrayNotHasKey('googleHome', $channelConfig);
    }

    public function testSettingGoogleHomeConfigDisabled() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/1', [
            'config' => ['googleHome' => ['googleHomeDisabled' => true]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $channel = $this->freshEntity($this->device->getChannels()[0]);
        $channelConfig = $channel->getUserConfig();
        $this->assertArrayHasKey('googleHome', $channelConfig);
        $this->assertTrue($channelConfig['googleHome']['googleHomeDisabled']);
        $this->assertFalse($channelConfig['googleHome']['needsUserConfirmation']);
    }

    /** @depends testSettingGoogleHomeConfigDisabled */
    public function testExecutingActionWithGoogleDisabledChannel() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
        ]));
        $this->assertStatusCode(Response::HTTP_CONFLICT, $client);
    }

    /** @depends testSettingGoogleHomeConfigDisabled */
    public function testExecutingActionWithGoogleDisabledChannelButWithoutGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
        ]));
        $this->assertStatusCode(Response::HTTP_ACCEPTED, $client);
    }

    public function testSettingGoogleHomeConfigNeedsUserConfirmation() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/1', [
            'config' => ['googleHome' => [
                'googleHomeDisabled' => false,
                'needsUserConfirmation' => true,
                'pin' => null,
            ]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $channel = $this->freshEntity($this->device->getChannels()[0]);
        $channelConfig = $channel->getUserConfig();
        $this->assertArrayHasKey('googleHome', $channelConfig);
        $this->assertTrue($channelConfig['googleHome']['needsUserConfirmation']);
    }

    /** @depends testSettingGoogleHomeConfigNeedsUserConfirmation */
    public function testExecutingActionWithoutConfirmation() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
        ]));
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);
        $body = $client->getResponseBody();
        $this->assertArrayHasKey('details', $body);
        $this->assertTrue($body['details']['needsUserConfirmation']);
    }

    /** @depends testSettingGoogleHomeConfigNeedsUserConfirmation */
    public function testExecutingActionWithConfirmation() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
            'googleUserConfirmation' => true,
        ]));
        $this->assertStatusCode(Response::HTTP_ACCEPTED, $client);
    }

    public function testSettingGoogleHomeConfigPin() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/1', [
            'config' => ['googleHome' => [
                'googleHomeDisabled' => false,
                'needsUserConfirmation' => false,
                'pin' => '1234',
            ]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $channel = $this->freshEntity($this->device->getChannels()[0]);
        $channelConfig = $channel->getUserConfig();
        $this->assertArrayHasKey('googleHome', $channelConfig);
        $this->assertNotNull($channelConfig['googleHome']['pin']);
        $this->assertEquals(40, strlen($channelConfig['googleHome']['pin']));
        $configFromBody = $client->getResponseBody()['config']['googleHome'];
        $this->assertArrayHasKey('pinSet', $configFromBody);
        $this->assertArrayNotHasKey('pin', $configFromBody);
        $this->assertTrue($configFromBody['pinSet']);
    }

    /** @depends testSettingGoogleHomeConfigPin */
    public function testExecutingActionWithoutPinWithoutGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
        ]));
        $this->assertStatusCode(Response::HTTP_ACCEPTED, $client);
    }

    /** @depends testSettingGoogleHomeConfigPin */
    public function testExecutingActionWithoutPin() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
        ]));
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);
        $body = $client->getResponseBody();
        $this->assertArrayHasKey('details', $body);
        $this->assertTrue($body['details']['needsPin']);
    }

    /** @depends testSettingGoogleHomeConfigPin */
    public function testExecutingActionWithInvalidPin() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
            'googlePin' => '1222',
        ]));
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);
        $body = $client->getResponseBody();
        $this->assertArrayHasKey('details', $body);
        $this->assertTrue($body['details']['invalidPin']);
    }

    /** @depends testSettingGoogleHomeConfigPin */
    public function testExecutingActionWithPin() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
            'googlePin' => '1234',
        ]));
        $this->assertStatusCode(Response::HTTP_ACCEPTED, $client);
    }

    public function testFetchingChannelsForGoogleIntegration() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/1', ['config' => ['googleHome' => ['googleHomeDisabled' => false]]]);
        $client->apiRequestV24('PUT', '/api/channels/2', ['config' => ['googleHome' => ['googleHomeDisabled' => false]]]);
        $client->apiRequestV24('GET', '/api/channels?forIntegration=google-home');
        $this->assertCount(3, $client->getResponseBody());
        $client->apiRequestV24('PUT', '/api/channels/1', [
            'config' => ['googleHome' => ['googleHomeDisabled' => true]],
        ]);
        $client->apiRequestV24('GET', '/api/channels?forIntegration=google-home');
        $fetchedChannels = $client->getResponseBody();
        $this->assertCount(2, $fetchedChannels);
        $this->assertNotContains(1, array_column($fetchedChannels, 'id'));
    }
}

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

use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Entity\Main\SceneOperation;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\SuplaAssertions;
use Symfony\Component\HttpFoundation\Response;

/** @small */
class GoogleHomeIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;
    use SuplaAssertions;

    /** @var User */
    private $user;
    /** @var \SuplaBundle\Entity\Main\IODevice */
    private $device;
    /** @var \SuplaBundle\Entity\Main\Location */
    private $location;
    /** @var \SuplaBundle\Entity\Main\IODeviceChannelGroup */
    private $channelGroup;
    /** @var \SuplaBundle\Entity\Main\Scene */
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
        $client->apiRequestV24('PATCH', '/api/channels/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $this->assertSuplaCommandExecuted('SET-CHAR-VALUE:1,1,1,1,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'));
    }

    public function testChangingChannelStateWithGoogleAndParams() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channels/2', json_encode([
            'action' => ChannelFunctionAction::SHUT_PARTIALLY,
            'percentage' => 45,
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $this->assertSuplaCommandExecuted('ACTION-SHUT-PARTIALLY:1,1,2,45,0,-1,0,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'));
    }

    public function testChangingChannelGroupStateWithGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/channel-groups/1', json_encode([
            'action' => 'open-close',
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $this->assertSuplaCommandExecuted('SET-CG-CHAR-VALUE:1,1,1,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'));
    }

    public function testExecutingSceneWithGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/1', json_encode([
            'action' => 'execute',
            'googleRequestId' => 'unicorn',
        ]));
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $this->assertSuplaCommandExecuted('EXECUTE-SCENE:1,1,GOOGLE-REQUEST-ID=' . base64_encode('unicorn'));
    }

    public function testSettingGoogleHomeConfigDisabledForRollerShutter() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/channels/2', [
            'config' => ['googleHome' => ['googleHomeDisabled' => true]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $channel = $this->freshEntity($this->device->getChannels()[1]);
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($channel);
        $this->assertArrayHasKey('googleHome', $channelConfig);
        $this->assertTrue($channelConfig['googleHome']['googleHomeDisabled']);
        $this->assertArrayHasKey('needsUserConfirmation', $channelConfig['googleHome']);
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
        $channelParamConfigTranslator = self::$container->get(SubjectConfigTranslator::class);
        $channelConfig = $channelParamConfigTranslator->getConfig($channel);
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

    /** @depends testSettingGoogleHomeConfigPin */
    public function testClearingGoogleHomeConfigPin() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV3('GET', '/api/channels/1');
        $channelData = json_decode($client->getResponse()->getContent(), true);
        $client->apiRequestV3('PUT', '/api/channels/1?safe=1', [
            'config' => ['googleHome' => [
                'googleHomeDisabled' => false,
                'needsUserConfirmation' => false,
                'pin' => null,
                'pinSet' => true,
            ]],
            'configBefore' => $channelData['config'],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $channel = $this->freshEntity($this->device->getChannels()[0]);
        $channelConfig = $channel->getUserConfig();
        $this->assertArrayHasKey('googleHome', $channelConfig);
        $this->assertNull($channelConfig['googleHome']['pin']);
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

    public function testSettingGoogleHomeConfigDisabledForScene() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/1', [
            'config' => ['googleHome' => ['googleHomeDisabled' => true]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $scene = $this->freshEntity($this->scene);
        $sceneConfig = $scene->getUserConfig();
        $this->assertArrayHasKey('googleHome', $sceneConfig);
        $this->assertTrue($sceneConfig['googleHome']['googleHomeDisabled']);
    }

    /** @depends testSettingGoogleHomeConfigDisabledForScene */
    public function testExecutingActionWithGoogleDisabledScene() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/1', json_encode([
            'action' => 'execute',
            'googleRequestId' => 'unicorn',
        ]));
        $this->assertStatusCode(Response::HTTP_CONFLICT, $client);
    }

    public function testSettingGoogleHomeConfigPinForScene() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/1', [
            'config' => ['googleHome' => [
                'googleHomeDisabled' => false,
                'needsUserConfirmation' => false,
                'pin' => '1234',
            ]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('2xx', $response);
        $scene = $this->freshEntity($this->scene);
        $sceneConfig = $scene->getUserConfig();
        $this->assertArrayHasKey('googleHome', $sceneConfig);
        $this->assertNotNull($sceneConfig['googleHome']['pin']);
        $this->assertEquals(40, strlen($sceneConfig['googleHome']['pin']));
        $configFromBody = $client->getResponseBody()['config']['googleHome'];
        $this->assertArrayHasKey('pinSet', $configFromBody);
        $this->assertArrayNotHasKey('pin', $configFromBody);
        $this->assertTrue($configFromBody['pinSet']);
    }

    /** @depends testSettingGoogleHomeConfigPinForScene */
    public function testExecutingSceneWithoutPinWithoutGoogle() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/1', json_encode([
            'action' => 'execute',
        ]));
        $this->assertStatusCode(Response::HTTP_ACCEPTED, $client);
    }

    /** @depends testSettingGoogleHomeConfigPinForScene */
    public function testExecutingSceneWithoutPin() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/1', json_encode([
            'action' => 'execute',
            'googleRequestId' => 'unicorn',
        ]));
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);
        $body = $client->getResponseBody();
        $this->assertArrayHasKey('details', $body);
        $this->assertTrue($body['details']['needsPin']);
    }

    /** @depends testSettingGoogleHomeConfigPinForScene */
    public function testExecutingSceneWithInvalidPin() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/1', json_encode([
            'action' => 'execute',
            'googleRequestId' => 'unicorn',
            'googlePin' => '1222',
        ]));
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);
        $body = $client->getResponseBody();
        $this->assertArrayHasKey('details', $body);
        $this->assertTrue($body['details']['invalidPin']);
    }

    /** @depends testSettingGoogleHomeConfigPinForScene */
    public function testExecutingSceneWithPin() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/scenes/1', json_encode([
            'action' => 'execute',
            'googleRequestId' => 'unicorn',
            'googlePin' => '1234',
        ]));
        $this->assertStatusCode(Response::HTTP_ACCEPTED, $client);
    }

    public function testFetchingScenesForGoogleIntegration() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', '/api/scenes/1', ['config' => ['googleHome' => ['googleHomeDisabled' => true]]]);
        $this->createScene($this->location, $this->channelGroup);
        $client->apiRequestV24('GET', '/api/scenes?forIntegration=google-home');
        $this->assertCount(1, $client->getResponseBody());
        $this->assertEquals(2, $client->getResponseBody()[0]['id']);
        $client->apiRequestV24('PUT', '/api/scenes/1', ['config' => ['googleHome' => ['googleHomeDisabled' => false]]]);
        $client->apiRequestV24('GET', '/api/scenes?forIntegration=google-home');
        $fetchedScenes = $client->getResponseBody();
        $this->assertContains(1, array_column($fetchedScenes, 'id'));
    }
}

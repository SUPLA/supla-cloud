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
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Entity\Main\Location;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Entity\Main\ValueBasedTrigger;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

/** @small */
class ReactionControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var Location */
    private $location;
    /** @var IODeviceChannel */
    private $thermometer;
    /** @var IODeviceChannel */
    private $lightswitch;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $this->location = $this->createLocation($this->user);
        $this->device = $this->createDevice($this->location, [
            [ChannelType::RELAY, ChannelFunction::LIGHTSWITCH],
            [ChannelType::THERMOMETERDS18B20, ChannelFunction::THERMOMETER],
            [ChannelType::HUMIDITYANDTEMPSENSOR, ChannelFunction::HUMIDITYANDTEMPERATURE],
        ]);
        $this->lightswitch = $this->device->getChannels()[0];
        $this->thermometer = $this->device->getChannels()[1];
    }

    public function testCreatingReaction() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', "/api/channels/{$this->thermometer->getId()}/reactions", [
            'subjectId' => $this->lightswitch->getId(), 'subjectType' => $this->lightswitch->getOwnSubjectType(),
            'actionId' => ChannelFunctionAction::TURN_ON,
            'trigger' => ['on_change_to' => ['lt' => 20, 'name' => 'temperature', 'resume' => ['ge' => 20]]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('subjectId', $content);
        $this->assertArrayHasKey('trigger', $content);
        $this->assertEquals(ActionableSubjectType::CHANNEL, $content['subjectType']);
        $this->assertContains('USER-ON-VBT-CHANGED:1', SuplaServerMock::$executedCommands);
        return $content['id'];
    }

    /** @depends testCreatingReaction */
    public function testGettingReaction(int $id) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$this->thermometer->getId()}/reactions/{$id}");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(ChannelFunctionAction::TURN_ON, $content['actionId']);
        $this->assertEquals(20, $content['trigger']['on_change_to']['lt']);
    }

    /** @depends testCreatingReaction */
    public function testCantGetReactionFromNotOwnedChannel(int $id) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$this->lightswitch->getId()}/reactions/{$id}");
        $response = $client->getResponse();
        $this->assertStatusCode(404, $response);
    }

    /** @depends testCreatingReaction */
    public function testGettingChannelReactions() {
        $this->testCreatingReaction();
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$this->thermometer->getId()}/reactions");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content);
        $reaction = $content[0];
        $this->assertEquals(ChannelFunctionAction::TURN_ON, $reaction['actionId']);
        $this->assertEquals(20, $reaction['trigger']['on_change_to']['lt']);
    }

    /** @depends testGettingChannelReactions */
    public function testGettingAllReactions() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/reactions");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content);
        $reaction = $content[0];
        $this->assertEquals(ChannelFunctionAction::TURN_ON, $reaction['actionId']);
        $this->assertEquals(20, $reaction['trigger']['on_change_to']['lt']);
    }

    /** @depends testGettingAllReactions */
    public function testGettingReactionsRelationsCount() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('GET', "/api/channels/{$this->thermometer->getId()}");
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('relationsCount', $content);
        $this->assertArrayHasKey('ownReactions', $content['relationsCount']);
        $this->assertEquals(2, $content['relationsCount']['ownReactions']);
    }

    /** @depends testCreatingReaction */
    public function testUpdatingReaction(int $id) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', "/api/channels/{$this->thermometer->getId()}/reactions/{$id}", [
            'subjectId' => $this->lightswitch->getId(), 'subjectType' => $this->lightswitch->getOwnSubjectType(),
            'actionId' => ChannelFunctionAction::TURN_OFF,
            'trigger' => ['on_change_to' => ['lt' => 30, 'name' => 'temperature', 'resume' => ['ge' => 30]]],
            'activityConditions' => null,
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(ChannelFunctionAction::TURN_OFF, $content['actionId']);
        $this->assertEquals(30, $content['trigger']['on_change_to']['lt']);
        $this->assertContains('USER-ON-VBT-CHANGED:1', SuplaServerMock::$executedCommands);
    }

    /** @depends testCreatingReaction */
    public function testDeletingReaction(int $id) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('DELETE', "/api/channels/{$this->thermometer->getId()}/reactions/{$id}");
        $response = $client->getResponse();
        $this->assertStatusCode(204, $response);
        $this->assertNull($this->getEntityManager()->find(ValueBasedTrigger::class, $id));
        $this->assertContains('USER-ON-VBT-CHANGED:1', SuplaServerMock::$executedCommands);
    }

    public function testCreatingReactionWithInvalidTrigger() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', "/api/channels/{$this->thermometer->getId()}/reactions", [
            'subjectId' => $this->lightswitch->getId(), 'subjectType' => $this->lightswitch->getOwnSubjectType(),
            'actionId' => ChannelFunctionAction::TURN_ON,
            'trigger' => ['on_change_to' => ['lt' => 20, 'name' => 'unicorn', 'resume' => ['ge' => 20]]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Unsupported field name', $content['message']);
        $this->assertNotContains('USER-ON-VBT-CHANGED:1', SuplaServerMock::$executedCommands);
    }

    public function testCreatingReactionWithNotification() {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('POST', "/api/channels/{$this->thermometer->getId()}/reactions", [
            'subjectType' => ActionableSubjectType::NOTIFICATION,
            'actionId' => ChannelFunctionAction::SEND,
            'actionParam' => ['body' => 'Test', 'accessIds' => [1]],
            'trigger' => ['on_change_to' => ['lt' => 20, 'name' => 'temperature', 'resume' => ['ge' => 20]]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(201, $response);
        $content = json_decode($response->getContent(), true);
        $notificationId = $content['subjectId'];
        $notification = $this->getEntityManager()->find(PushNotification::class, $notificationId);
        $this->assertNotNull($notification);
        $this->assertEquals('Test', $notification->getBody());
        $this->assertEquals($this->thermometer->getId(), $notification->getChannel()->getId());
        return $content;
    }

    /** @depends testCreatingReactionWithNotification */
    public function testEditingReactionWithNotification(array $vbt) {
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PUT', "/api/channels/{$this->thermometer->getId()}/reactions/{$vbt['id']}", [
            'subjectType' => ActionableSubjectType::NOTIFICATION,
            'actionId' => ChannelFunctionAction::SEND,
            'actionParam' => ['body' => 'Test 2', 'accessIds' => [1]],
            'trigger' => ['on_change_to' => ['lt' => 20, 'name' => 'temperature', 'resume' => ['ge' => 20]]],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $notificationId = $content['subjectId'];
        $this->assertNotEquals($notificationId, $vbt['subjectId']);
        $notification = $this->getEntityManager()->find(PushNotification::class, $notificationId);
        $this->assertNotNull($notification);
        $this->assertEquals('Test 2', $notification->getBody());
        $this->assertEquals($this->thermometer->getId(), $notification->getChannel()->getId());
        $this->assertNull($this->getEntityManager()->find(PushNotification::class, $vbt['subjectId']));
    }
}

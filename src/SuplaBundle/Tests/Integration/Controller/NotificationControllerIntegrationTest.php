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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaDeveloperBundle\DataFixtures\ORM\NotificationsFixture;

/** @small */
class NotificationControllerIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    public function testSendingPushNotification() {
        $client = $this->createAuthenticatedClient($this->user);
        $aidId = $this->user->getAccessIDS()[0]->getId();
        $client->apiRequestV24('PATCH', '/api/notifications', [
            'title' => 'Notification title',
            'body' => 'Notification test',
            'accessIds' => [$aidId],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $this->assertCount(1, SuplaServerMock::$executedCommands);
        $this->assertStringStartsWith("SEND-PUSH:{$this->user->getId()},", SuplaServerMock::$executedCommands[0]);
        $commandParts = explode(',', SuplaServerMock::$executedCommands[0]);
        $payload = base64_decode(end($commandParts));
        $this->assertIsString($payload);
        $payload = json_decode($payload, true);
        $this->assertIsArray($payload);
        $this->assertEquals('Notification test', $payload['body']);
        $this->assertEquals('Notification title', $payload['title']);
        $this->assertEquals($aidId, $payload['recipients']['aids'][0]);
    }

    public function testSendingPushNotificationToNotMineAid() {
        $anotherUser = $this->createConfirmedUser('another@supla.org');
        $client = $this->createAuthenticatedClient($this->user);
        $client->apiRequestV24('PATCH', '/api/notifications', [
            'body' => 'Notification test',
            'accessIds' => [$anotherUser->getAccessIDS()[0]->getId()],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode('4XX', $response);
        $this->assertEmpty(SuplaServerMock::$executedCommands);
    }

    public function testSendingPushNotificationWithoutBody() {
        $client = $this->createAuthenticatedClient($this->user);
        $aidId = $this->user->getAccessIDS()[0]->getId();
        $client->apiRequestV24('PATCH', '/api/notifications', [
            'title' => 'Notification title',
            'accessIds' => [$aidId],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(400, $response);
        $this->assertEmpty(SuplaServerMock::$executedCommands);
    }

    public function testSendingPushNotificationWithoutTitle() {
        $client = $this->createAuthenticatedClient($this->user);
        $aidId = $this->user->getAccessIDS()[0]->getId();
        $client->apiRequestV24('PATCH', '/api/notifications', [
            'body' => 'Notification title',
            'accessIds' => [$aidId],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(202, $response);
        $this->assertCount(1, SuplaServerMock::$executedCommands);
    }

    public function testEditingManagedNotification() {
        $device = $this->createDeviceSonoff($this->user->getLocations()[0]);
        $channel = $device->getChannels()[0];
        $notification = (new NotificationsFixture())->createChannelNotification($this->getEntityManager(), $channel, 'A', 'A');
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', "/api/notifications/{$notification->getId()}?include=accessIds", [
            'title' => 'New title',
            'body' => 'New body',
            'accessIds' => [$device->getUser()->getAccessIDS()[0]->getId()],
        ]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('New title', $content['title']);
        $this->assertEquals('New body', $content['body']);
        $this->assertCount(1, $content['accessIds']);
        return $notification->getId();
    }

    /** @depends testEditingManagedNotification */
    public function testClearingAccessIdsOfManagedNotification(int $notificationId) {
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', "/api/notifications/{$notificationId}?include=accessIds", ['accessIds' => []]);
        $response = $client->getResponse();
        $this->assertStatusCode(200, $response);
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('New title', $content['title']);
        $this->assertEquals('New body', $content['body']);
        $this->assertEmpty($content['accessIds']);
    }

    public function testCantEditTitleIfNullFromDevice() {
        $device = $this->createDeviceSonoff($this->user->getLocations()[0]);
        $channel = $device->getChannels()[0];
        $notification = (new NotificationsFixture())->createChannelNotification($this->getEntityManager(), $channel, 'A', null);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', "/api/notifications/{$notification->getId()}?include=accessIds", [
            'title' => 'New title',
            'body' => 'New body',
            'accessIds' => [$device->getUser()->getAccessIDS()[0]->getId()],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testCantEditBodyIfNullFromDevice() {
        $device = $this->createDeviceSonoff($this->user->getLocations()[0]);
        $channel = $device->getChannels()[0];
        $notification = (new NotificationsFixture())->createChannelNotification($this->getEntityManager(), $channel, null, 'A');
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', "/api/notifications/{$notification->getId()}?include=accessIds", [
            'title' => 'New title',
            'body' => 'New body',
            'accessIds' => [$device->getUser()->getAccessIDS()[0]->getId()],
        ]);
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testCantEditNotificationNotManagedByDevice() {
        $device = $this->createDeviceSonoff($this->user->getLocations()[0]);
        $channel = $device->getChannels()[0];
        $notification = (new NotificationsFixture())->createChannelNotification($this->getEntityManager(), $channel, 'A', 'A');
        EntityUtils::setField($notification, 'managedByDevice', false);
        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('PUT', "/api/notifications/{$notification->getId()}?include=accessIds", [
            'title' => 'New title',
            'body' => 'New body',
            'accessIds' => [$device->getUser()->getAccessIDS()[0]->getId()],
        ]);
        $this->assertStatusCode(404, $client->getResponse());
    }

    public function testFetchingChannelNotifications() {
        $device = $this->createDeviceSonoff($this->user->getLocations()[0]);
        $channel = $device->getChannels()[0];
        (new NotificationsFixture())->createChannelNotification($this->getEntityManager(), $channel, 'A', 'A');
        (new NotificationsFixture())->createChannelNotification($this->getEntityManager(), $channel);
        (new NotificationsFixture())->createDeviceNotification($this->getEntityManager(), $device);
        $notManaged = (new NotificationsFixture())->createChannelNotification($this->getEntityManager(), $channel);
        EntityUtils::setField($notManaged, 'managedByDevice', false);
        $this->getEntityManager()->persist($notManaged);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('GET', "/api/channels/{$channel->getId()}/notifications");
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertCount(3, json_decode($client->getResponse()->getContent(), true));
        $client->apiRequestV24('GET', "/api/channels/{$channel->getId()}/notifications?onlyManaged=true");
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertCount(2, json_decode($client->getResponse()->getContent(), true));
        $client->apiRequestV24('GET', "/api/channels/{$channel->getId()}/notifications?onlyManaged=false");
        $this->assertStatusCode(400, $client->getResponse());
    }

    public function testFetchingIoDeviceNotifications() {
        $device = $this->createDeviceSonoff($this->user->getLocations()[0]);
        $channel = $device->getChannels()[0];
        (new NotificationsFixture())->createChannelNotification($this->getEntityManager(), $channel);
        (new NotificationsFixture())->createDeviceNotification($this->getEntityManager(), $device);
        $this->getEntityManager()->flush();
        $client = $this->createAuthenticatedClient();
        $client->apiRequestV24('GET', "/api/iodevices/{$device->getId()}/notifications");
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertCount(1, json_decode($client->getResponse()->getContent(), true));
    }
}

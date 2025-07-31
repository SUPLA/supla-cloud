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

namespace SuplaBundle\Tests\Integration\Messages;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Enums\UserPreferences;
use SuplaBundle\Message\Common\FailedAuthAttemptNotification;
use SuplaBundle\Message\CommonMessageHandler;
use SuplaBundle\Message\UserOptOutNotifications;
use SuplaBundle\Supla\SuplaServerMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestMailerTransport;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

/** @small */
class CommonMessageHandlerIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    private ?User $user = null;
    private ?CommonMessageHandler $handler = null;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    /** @before */
    public function initServices() {
        $this->handler = self::$container->get(CommonMessageHandler::class);
    }

    public function testSendingFailedAuthAttemptToEmail() {
        $handler = $this->handler;
        $handler(new FailedAuthAttemptNotification($this->user, '1.2.3.4'));
        $this->flushMessagesQueue();
        $this->assertCount(1, TestMailerTransport::getMessages());
        $message = TestMailerTransport::getMessages()[0];
        $this->assertStringContainsString('<b>1.2.3.4</b>', $message->getHtmlBody());
        $this->assertStringContainsString('<a href="mailto:security', $message->getHtmlBody());
        $this->assertStringContainsString('The incident was detected at ' . date('n/j/y'), $message->getHtmlBody());
        $this->assertStringContainsString('account?optOutNotification=failed_auth_attempt', $message->getHtmlBody());
    }

    public function testDoesNotSendFailedAuthAttemptToPushIfNoRecipients() {
        $handler = $this->handler;
        $handler(new FailedAuthAttemptNotification($this->user, '1.2.3.4'));
        $this->flushMessagesQueue();
        $this->assertCount(0, $this->getSentPushNotifications());
    }

    public function testSendingFailedAuthAttemptToPush() {
        $this->user = $this->freshEntity($this->user);
        $this->user->setPreference(UserPreferences::ACCOUNT_PUSH_NOTIFICATIONS_ACCESS_IDS_IDS, [$this->user->getAccessIDS()[0]->getId()]);
        $this->persist($this->user);
        $handler = $this->handler;
        $handler(new FailedAuthAttemptNotification($this->user, '1.2.3.4'));
        $this->flushMessagesQueue();
        $this->assertCount(1, $this->getSentPushNotifications());
        $push = $this->getSentPushNotifications()[0];
        $this->assertStringContainsString('unsuccessful', $push['title']);
        $this->assertStringContainsString('Unsuccessful login', $push['body']);
        $this->assertStringContainsString('cloud.supla.org', $push['body']);
        $this->assertEquals([$this->user->getAccessIDS()[0]->getId()], $push['recipients']['aids']);
    }

    public function testNotSendingFailedAuthAttemptEmailIfUserOptOut() {
        $this->user = $this->freshEntity($this->user);
        $this->user->setPreference(UserPreferences::OPT_OUT_NOTIFICATIONS_EMAIL, [UserOptOutNotifications::FAILED_AUTH_ATTEMPT]);
        $this->persist($this->user);
        $handler = $this->handler;
        $handler(new FailedAuthAttemptNotification($this->user, '1.2.3.4'));
        $this->flushMessagesQueue();
        $this->assertCount(0, TestMailerTransport::getMessages());
    }

    public function testNotSendingFailedAuthAttemptToPushIfUserOptOut() {
        $this->user = $this->freshEntity($this->user);
        $this->user->setPreference(UserPreferences::ACCOUNT_PUSH_NOTIFICATIONS_ACCESS_IDS_IDS, [$this->user->getAccessIDS()[0]->getId()]);
        $this->user->setPreference(UserPreferences::OPT_OUT_NOTIFICATIONS_EMAIL, []);
        $this->user->setPreference(UserPreferences::OPT_OUT_NOTIFICATIONS_PUSH, [UserOptOutNotifications::FAILED_AUTH_ATTEMPT]);
        $this->persist($this->user);
        $handler = $this->handler;
        $handler(new FailedAuthAttemptNotification($this->user, '1.2.3.4'));
        $this->flushMessagesQueue();
        $this->assertCount(0, $this->getSentPushNotifications());
        $this->assertCount(1, TestMailerTransport::getMessages());
    }

    private function getSentPushNotifications(): array {
        $sentCommands = array_filter(SuplaServerMock::$executedCommands, fn(string $cmd) => str_starts_with($cmd, 'SEND-PUSH:'));
        $sentCommands = array_map(fn(string $cmd) => explode(',', $cmd)[1], $sentCommands);
        $sentCommands = array_map(fn(string $cmd) => base64_decode($cmd), $sentCommands);
        $sentCommands = array_map(fn(string $cmd) => json_decode($cmd, true), $sentCommands);
        return $sentCommands;
    }
}

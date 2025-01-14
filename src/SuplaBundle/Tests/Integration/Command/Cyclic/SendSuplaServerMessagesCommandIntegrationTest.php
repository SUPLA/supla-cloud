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

namespace SuplaBundle\Tests\Integration\Command\Cyclic;

use SuplaBundle\Entity\Main\ClientApp;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Message\UserOptOutNotifications;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestMailer;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

/**
 * @small
 */
class SendSuplaServerMessagesCommandIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    public function testSendingSuplaServerEmail() {
        $body = json_encode([
            'template' => UserOptOutNotifications::FAILED_AUTH_ATTEMPT,
            'userId' => $this->user->getId(),
            'data' => ['ip' => '12.23.34.45'],
        ]);
        $this->getEntityManager()->getConnection()->executeQuery(
            'INSERT INTO supla_email_notifications (body, headers, queue_name, created_at, available_at) ' .
            "VALUES('$body', '[]', 'supla-server', NOW(), NOW())"
        );
        $this->flushMessagesQueue();
        $this->assertCount(0, TestMailer::getMessages());
        $this->executeCommand('supla:cyclic:send-server-messages');
        $this->assertCount(0, TestMailer::getMessages());
        $this->flushMessagesQueue();
        $this->assertCount(1, TestMailer::getMessages());
        $message = TestMailer::getMessages()[0];
        $this->assertStringContainsString('<b>12.23.34.45</b>', $message->getHtmlBody());
        $this->assertStringContainsString('<a href="mailto:security', $message->getHtmlBody());
    }

    public function testNewIoDeviceNotification() {
        $parameters = [
            $this->user->getLocations()[0]->getId(),
            $this->user->getId(),
            "'abc'",
            "'ZAMEL-PNW-CHOINKA'",
            "INET_ATON('1.1.2.2')",
            "'3.33'",
            10,
            'NULL',
            'NULL',
            'NULL',
            'NULL',
            'NULL',
            '@outId',
        ];
        $query = 'CALL supla_add_iodevice(' . implode(', ', $parameters) . ')';
        $this->getEntityManager()->getConnection()->executeQuery($query);
        $this->assertEquals('ZAMEL-PNW-CHOINKA', $this->getEntityManager()->find(IODevice::class, 1)->getName());
        $this->executeCommand('supla:cyclic:send-server-messages');
        $this->flushMessagesQueue();
        $this->assertCount(1, TestMailer::getMessages());
        $message = TestMailer::getMessages()[0];
        $this->assertStringContainsString('new device has been added', $message->getSubject());
        $this->assertStringContainsString('ZAMEL-PNW-CHOINKA', $message->getHtmlBody());
        $this->assertStringContainsString('3.33', $message->getHtmlBody());
    }

    /** @depends testNewIoDeviceNotification */
    public function testAddDeviceWithEmojiInName() {
        $parameters = [
            $this->user->getLocations()[0]->getId(),
            $this->user->getId(),
            "'abd'",
            "'ZAMEL-❤️-CHOINKA'",
            "INET_ATON('1.1.2.2')",
            "'3.33'",
            10,
            'NULL',
            'NULL',
            'NULL',
            'NULL',
            'NULL',
            '@outId',
        ];
        $query = 'CALL supla_add_iodevice(' . implode(', ', $parameters) . ')';
        $this->getEntityManager()->getConnection()->executeQuery($query);
        $this->assertEquals('ZAMEL-❤️-CHOINKA', $this->getEntityManager()->find(IODevice::class, 2)->getName());
    }

    public function testNoNewIoDeviceNotificationWhenOptOut() {
        $this->user = $this->freshEntity($this->user);
        $this->user->setPreference('optOutNotifications', [UserOptOutNotifications::NEW_IO_DEVICE]);
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
        $parameters = [
            $this->user->getLocations()[0]->getId(),
            $this->user->getId(),
            "'abcdef'",
            "'ZAMEL-PNW-CHOINKA'",
            "INET_ATON('1.1.2.2')",
            "'3.33'",
            10,
            'NULL',
            'NULL',
            'NULL',
            'NULL',
            'NULL',
            '@outId',
        ];
        $query = 'CALL supla_add_iodevice(' . implode(', ', $parameters) . ')';
        $this->getEntityManager()->getConnection()->executeQuery($query);
        $this->assertEquals('ZAMEL-PNW-CHOINKA', $this->getEntityManager()->find(IODevice::class, 1)->getName());
        $this->executeCommand('supla:cyclic:send-server-messages');
        $this->flushMessagesQueue();
        $this->assertCount(0, TestMailer::getMessages());
    }

    public function testNewClientAppNotification() {
        $parameters = [
            'NULL',
            "'abcdef'",
            "'My New Ajfon'",
            "INET_ATON('1.1.2.2')",
            "'2.22'",
            10,
            $this->user->getId(),
            'NULL',
            '@outId',
        ];
        $query = 'CALL supla_add_client(' . implode(', ', $parameters) . ')';
        $this->getEntityManager()->getConnection()->executeQuery($query);
        $this->assertEquals('My New Ajfon', $this->getEntityManager()->find(ClientApp::class, 1)->getName());
        $this->executeCommand('supla:cyclic:send-server-messages');
        $this->flushMessagesQueue();
        $this->assertCount(1, TestMailer::getMessages());
        $message = TestMailer::getMessages()[0];
        $this->assertStringContainsString('new client app has been added', $message->getSubject());
        $this->assertStringContainsString('My New Ajfon', $message->getHtmlBody());
        $this->assertStringContainsString('2.22', $message->getHtmlBody());
    }

    public function testNewClientAppNotificationBurningInQueue() {
        $parameters = [
            'NULL',
            "'abc'",
            "'My New Ajfon'",
            "INET_ATON('1.1.2.2')",
            "'2.22'",
            10,
            $this->user->getId(),
            'NULL',
            '@outId',
        ];
        $query = 'CALL supla_add_client(' . implode(', ', $parameters) . ')';
        $this->getEntityManager()->getConnection()->executeQuery($query);
        $this->assertEquals('My New Ajfon', $this->getEntityManager()->find(ClientApp::class, 1)->getName());
        $this->executeCommand('supla:cyclic:send-server-messages');
        TestTimeProvider::setTime('+1 hour'); // waiting long to process the queue for some reason
        $this->flushMessagesQueue();
        $this->assertEmpty(TestMailer::getMessages());
    }

    public function testNewIoDeviceNotificationBurningInSuplaServerProcessing() {
        $ioDevice = $this->createDeviceSonoff($this->user->getLocations()[0]);
        $body = json_encode([
            'template' => UserOptOutNotifications::NEW_IO_DEVICE,
            'userId' => $this->user->getId(),
            'data' => ['ioDeviceId' => $ioDevice->getId()],
        ]);
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour')); // waiting long to process the supla server messages for some reason
        $this->getEntityManager()->getConnection()->executeQuery(
            'INSERT INTO supla_email_notifications (body, headers, queue_name, created_at, available_at) ' .
            "VALUES('$body', '[]', 'supla-server', '$oneHourAgo', '$oneHourAgo')"
        );
        $this->flushMessagesQueue();
        $this->assertCount(0, TestMailer::getMessages());
        $this->executeCommand('supla:cyclic:send-server-messages');
        $this->assertCount(0, TestMailer::getMessages());
        $this->flushMessagesQueue();
        $this->assertCount(0, TestMailer::getMessages());
    }

    public function testSendingSuplaServerEmailInvalid() {
        $body = json_encode([
            'template' => UserOptOutNotifications::FAILED_AUTH_ATTEMPT,
            'data' => ['ip' => '12.23.34.45'],
        ]);
        $this->getEntityManager()->getConnection()->executeQuery(
            'INSERT INTO supla_email_notifications (body, headers, queue_name, created_at, available_at) ' .
            "VALUES('$body', '[]', 'supla-server', NOW(), NOW())"
        );
        $this->flushMessagesQueue();
        $this->assertCount(0, TestMailer::getMessages());
        $this->executeCommand('supla:cyclic:send-server-messages');
        $this->assertCount(0, TestMailer::getMessages());
        $this->flushMessagesQueue();
        $this->assertCount(0, TestMailer::getMessages());
    }

    public function testSendingSuplaServerEmailToInvalidUserId() {
        $body = json_encode([
            'template' => UserOptOutNotifications::FAILED_AUTH_ATTEMPT,
            'userId' => 666,
            'data' => ['ip' => '12.23.34.45'],
        ]);
        $this->getEntityManager()->getConnection()->executeQuery(
            'INSERT INTO supla_email_notifications (body, headers, queue_name, created_at, available_at) ' .
            "VALUES('$body', '[]', 'supla-server', NOW(), NOW())"
        );
        $this->flushMessagesQueue();
        $this->assertCount(0, TestMailer::getMessages());
        $this->executeCommand('supla:cyclic:send-server-messages');
        $this->assertCount(0, TestMailer::getMessages());
        $this->flushMessagesQueue();
        $this->assertCount(0, TestMailer::getMessages());
    }
}

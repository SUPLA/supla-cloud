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

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Message\EmailFromTemplateHandler;
use SuplaBundle\Message\Emails\FailedAuthAttemptEmailNotification;
use SuplaBundle\Message\Emails\ResetPasswordEmailNotification;
use SuplaBundle\Message\UserOptOutNotifications;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestMailer;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

/** @small */
class EmailFromTemplateHandlerIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    /** @var User */
    private $user;
    /** @var IODevice */
    private $device;
    /** @var \SuplaBundle\Entity\Main\Location */
    private $location;
    /** @var EmailFromTemplateHandler */
    private $handler;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    /** @before */
    public function initServices() {
        $this->handler = self::$container->get(EmailFromTemplateHandler::class);
    }

    public function testSendingFailedAuthAttempt() {
        $handler = $this->handler;
        $handler(new FailedAuthAttemptEmailNotification($this->user, '1.2.3.4'));
        $this->assertCount(1, TestMailer::getMessages());
        $message = TestMailer::getMessages()[0];
        $this->assertStringContainsString('<b>1.2.3.4</b>', $message->getHtmlBody());
        $this->assertStringContainsString('<a href="mailto:security', $message->getHtmlBody());
        $this->assertStringContainsString('The incident was detected at ' . date('n/j/y'), $message->getHtmlBody());
        $this->assertStringContainsString('account?optOutNotification=failed_auth_attempt', $message->getHtmlBody());
    }

    public function testSendingFailedAuthAttemptInPolish() {
        $this->user->setLocale('pl_PL');
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
        $handler = $this->handler;
        $handler(new FailedAuthAttemptEmailNotification($this->user, '1.2.3.4'));
        $this->assertCount(1, TestMailer::getMessages());
        $message = TestMailer::getMessages()[0];
        $this->assertStringContainsString('<b>1.2.3.4</b>', $message->getHtmlBody());
        $this->assertStringContainsString('<a href="mailto:security', $message->getHtmlBody());
        $this->assertStringContainsString('Zdarzenie miało miejsce ' . date('j.m.Y'), $message->getHtmlBody());
        $this->assertStringContainsString('account?optOutNotification=failed_auth_attempt', $message->getHtmlBody());
    }

    public function testNotSendingFailedAuthAttemptIfUserOptOut() {
        $this->user = $this->freshEntity($this->user);
        $this->user->setPreference('optOutNotifications', [UserOptOutNotifications::FAILED_AUTH_ATTEMPT]);
        $this->getEntityManager()->persist($this->user);
        $this->getEntityManager()->flush();
        $handler = $this->handler;
        $handler(new FailedAuthAttemptEmailNotification($this->user, '1.2.3.4'));
        $this->assertCount(0, TestMailer::getMessages());
    }

    public function testSendingResetPasswordLink() {
        $handler = $this->handler;
        $handler(new ResetPasswordEmailNotification($this->user));
        $this->assertCount(1, TestMailer::getMessages());
        $message = TestMailer::getMessages()[0];
        $this->assertStringContainsString('https://supla.local', $message->getHtmlBody());
    }
}

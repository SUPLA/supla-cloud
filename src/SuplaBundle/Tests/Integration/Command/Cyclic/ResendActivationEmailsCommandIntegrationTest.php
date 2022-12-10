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

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\TestMailer;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;

/**
 * @small
 */
class ResendActivationEmailsCommandIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    protected function initializeDatabaseForTests() {
        $userManager = self::$container->get(UserManager::class);
        $user = new User();
        $user->setEmail('janusz@supla.org');
        $user->setPlainPassword('januszowe');
        $userManager->create($user);
        $userManager->sendConfirmationEmailMessage($user);
        TestMailer::reset();
        $this->user = $user;
    }

    public function testNotResendImmediately() {
        $this->executeCommand('supla:user:resend-activation-emails');
        $this->assertCount(0, TestMailer::getMessages());
    }

    public function testNotResendAfter15Minutes() {
        TestTimeProvider::setTime('+15 minutes');
        $this->executeCommand('supla:user:resend-activation-emails');
        $this->assertCount(0, TestMailer::getMessages());
    }

    public function testResendAfter45Minutes() {
        TestTimeProvider::setTime('+45 minutes');
        $this->executeCommand('supla:user:resend-activation-emails');
        $this->assertCount(1, TestMailer::getMessages());
    }

    public function testNotResendAfter65Minutes() {
        TestTimeProvider::setTime('+65 minutes');
        $this->executeCommand('supla:user:resend-activation-emails');
        $this->assertCount(0, TestMailer::getMessages());
    }

    public function testDoNotResendIfAlreadySentTwice() {
        TestTimeProvider::setTime('+55 minutes');
        $this->executeCommand('supla:user:resend-activation-emails');
        $this->assertCount(0, TestMailer::getMessages());
    }

    /** @large */
    public function testDoNotResendIfConfirmed() {
        self::$container->get(UserManager::class)->confirm($this->user->getToken());
        TestTimeProvider::setTime('+45 minutes');
        $this->executeCommand('supla:user:resend-activation-emails');
        $this->assertCount(0, TestMailer::getMessages());
    }
}

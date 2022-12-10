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

namespace SuplaBundle\Tests\Integration\Command;

use SuplaBundle\Entity\Main\StateWebhook;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class UserWebhookCommandIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;
    use ResponseAssertions;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
    }

    public function testCreatingWebhook() {
        $command = $this->application->find('supla:user:webhook');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['http://local-app/local-webhook', 'abc123']);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername()]);
        $this->assertEquals(0, $exitCode);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Webhook has been saved', $output);
        $this->assertStringNotContainsString('Current webhook URL', $output);
        $webhook = $this->getDoctrine()->getRepository(StateWebhook::class)->findOrCreateForApiClientAndUser(null, $this->user);
        $this->assertEquals('http://local-app/local-webhook', $webhook->getUrl());
    }

    /** @depends testCreatingWebhook */
    public function testUpdatingWebhook() {
        $command = $this->application->find('supla:user:webhook');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['http://local-app2/local-webhook', 'abc123']);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername()]);
        $this->assertEquals(0, $exitCode);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Webhook has been saved', $output);
        $this->assertStringContainsString('Current webhook URL', $output);
        $webhook = $this->getDoctrine()->getRepository(StateWebhook::class)->findOrCreateForApiClientAndUser(null, $this->user);
        $this->assertEquals('http://local-app2/local-webhook', $webhook->getUrl());
    }

    /** @depends testCreatingWebhook */
    public function testDeletingWebhook() {
        $command = $this->application->find('supla:user:webhook');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['']);
        $exitCode = $commandTester->execute(['username' => $this->user->getUsername()]);
        $this->assertEquals(0, $exitCode);
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Webhook has been deleted', $output);
        $this->assertStringContainsString('Current webhook URL', $output);
        $webhook = $this->getDoctrine()->getRepository(StateWebhook::class)->findOrCreateForApiClientAndUser(null, $this->user);
        $this->assertNull($webhook->getId());
    }
}

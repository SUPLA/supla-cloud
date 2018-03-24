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

namespace SuplaBundle\Tests\Integration\User;

use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\ResponseAssertions;
use Symfony\Bundle\FrameworkBundle\Client;

class AuthenticationIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;

    /** @var User */
    private $user;

    protected function setUp() {
        $userManager = $this->container->get('user_manager');
        $this->user = new User();
        $this->user->setEmail('supler@supla.org');
        $userManager->create($this->user);
        $userManager->setPassword('supla123', $this->user, true);
        $userManager->confirm($this->user->getToken());
        $this->container->get('location_manager')->createLocation($this->user);
    }

    public function testAuthSuccess() {
        $client = $this->authenticate('supler@supla.org', 'supla123');
        $user = $this->getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('supler@supla.org', $user->getEmail());
        $this->assertStatusCode(200, $client->getResponse());
        $this->assertCount(0, $client->getCrawler()->filter('#login-page'));
    }

    public function testAuthFailure() {
        $client = $this->authenticate('supler@supla.org', 'supla321');
        $user = $this->getAuthenticatedUser($client);
        $this->assertNull($user);
        $this->assertCount(1, $client->getCrawler()->filter('#login-page'));
    }

    private function authenticate(string $username, string $password): Client {
        $client = self::createClient([], ['HTTPS' => true]);
        $client->followRedirects();
        $client->request('POST', '/auth/login', [
            '_username' => $username,
            '_password' => $password,
        ]);
        return $client;
    }

    private function getAuthenticatedUser(Client $client) {
        if (!$client->getContainer()->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }
        if (null === $token = $client->getContainer()->get('security.token_storage')->getToken()) {
            return;
        }
        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }
        return $user;
    }
}

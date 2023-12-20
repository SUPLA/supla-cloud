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

namespace SuplaBundle\Tests\Integration\Traits;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Tests\Integration\TestClient;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait SuplaApiHelper {
    use UserFixtures;

    protected function authenticate($username = 'supler@supla.org', string $password = 'supla123') {
        self::ensureKernelShutdown();
        $client = self::createClient([], ['HTTPS' => true]);
        $client->request('POST', '/api/webapp-tokens', [
            'username' => $username,
            'password' => $password,
        ]);
        $response = $client->getResponse();
        return json_decode($response->getContent(), true)['access_token'] ?? false;
    }

    protected function createAuthenticatedClient($username = 'supler@supla.org', $debug = false): TestClient {
        self::ensureKernelShutdown();
        $username = $username instanceof User ? $username->getUsername() : $username;
        /** @var Client $client */
        $client = self::createClient(['debug' => $debug], ['HTTP_AUTHORIZATION' => 'Bearer ' . base64_encode($username), 'HTTPS' => true]);
        return $client;
    }

    protected function createAuthenticatedClientDebug($username = 'supler@supla.org'): TestClient {
        return $this->createAuthenticatedClient($username, true);
    }

    protected function simulateAuthentication(User $user) {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        self::$container->get('security.token_storage')->setToken($token);
        self::$container->get(TokenStorageInterface::class)->setToken($token);
    }

    public function versionHeader(ApiVersions $version): array {
        return ['HTTP_X_ACCEPT_VERSION' => $version->getValue()];
    }
}

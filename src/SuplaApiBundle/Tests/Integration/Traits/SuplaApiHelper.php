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

namespace SuplaApiBundle\Tests\Integration\Traits;

use Assert\Assertion;
use Psr\Container\ContainerInterface;
use SuplaApiBundle\Entity\ApiUser;
use SuplaApiBundle\Model\ApiVersions;
use SuplaBundle\Entity\User;
use SuplaBundle\Supla\SuplaServerMockCommandsCollector;
use SuplaBundle\Tests\Integration\TestClient;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @property ContainerInterface $container
 */
trait SuplaApiHelper {
    use UserFixtures;

    protected function createConfirmedUserWithApiAccess($password = '123') {
        $user = $this->createConfirmedUser();
        $apiManager = $this->container->get('api_manager');
        $apiUser = $apiManager->getAPIUser($user);
        $apiUser->setEnabled(true);
        $apiManager->setPassword($password, $apiUser, true);
        $apiManager->getClient($user);
        return $user;
    }

    protected function simulateAuthentication(User $user) {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }

    protected function authenticateApiUser(User $user, string $password = '123') {
        $apiManager = $this->container->get('api_manager');
        $apiUser = $this->getApiUser($user);
        $apiClient = $apiManager->getClient($user);
        $client = self::createClient([], ['HTTPS' => true]);
        $client->request('POST', '/oauth/v2/token', [
            'client_id' => $apiClient->getPublicId(),
            'client_secret' => $apiClient->getSecret(),
            'grant_type' => 'password',
            'username' => $apiUser->getUsername(),
            'password' => $password,
        ]);
        $response = $client->getResponse();
        return $response->getStatusCode() == 200 ? json_decode($response->getContent()) : null;
    }

    protected function getApiUser(User $user): ApiUser {
        $apiManager = $this->container->get('api_manager');
        return $apiManager->getAPIUser($user);
    }

    protected function createAuthenticatedApiClient(User $user, string $password = '123'): TestClient {
        $token = $this->authenticateApiUser($user, $password);
        /** @var Client $client */
        $client = self::createClient(['debug' => false], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token->access_token, 'HTTPS' => true]);
        $client->followRedirects();
        return $client;
    }

    public function getSuplaServerCommands(Client $client): array {
        $profile = $client->getProfile();
        Assertion::isObject($profile, 'There is no profile available. Have you enabled it with $client->enableProfiler()?');
        /** @var SuplaServerMockCommandsCollector $suplaCommandsCollector */
        $suplaCommandsCollector = $profile->getCollector(SuplaServerMockCommandsCollector::NAME);
        return $suplaCommandsCollector->getCommands();
    }

    public function versionHeader(ApiVersions $version): array {
        return ['HTTP_X_ACCEPT_VERSION' => $version->getValue()];
    }
}

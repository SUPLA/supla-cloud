<?php

namespace SuplaApiBundle\Tests\Integration\Traits;

use Assert\Assertion;
use Psr\Container\ContainerInterface;
use SuplaApiBundle\Entity\ApiUser;
use SuplaBundle\Entity\User;
use SuplaBundle\Supla\SuplaServerMockCommandsCollector;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;
use Symfony\Bundle\FrameworkBundle\Client;

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

    protected function authenticateApiUser(User $user, string $password = '123') {
        $apiManager = $this->container->get('api_manager');
        $apiUser = $this->getApiUser($user);
        $apiClient = $apiManager->getClient($user);
        $client = self::createClient();
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

    protected function createAuthenticatedClient(User $user, string $password = '123'): Client {
        $token = $this->authenticateApiUser($user, $password);
        /** @var Client $client */
        $client = self::createClient([], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token->access_token, 'HTTPS' => true]);
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
}

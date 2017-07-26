<?php

namespace SuplaApiBundle\Tests\Integration\Traits;

use Psr\Container\ContainerInterface;
use SuplaApiBundle\Entity\ApiUser;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

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
}

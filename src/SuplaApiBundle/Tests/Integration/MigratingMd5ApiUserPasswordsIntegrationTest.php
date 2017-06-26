<?php

namespace SuplaApiBundle\Tests\Integration;

use SuplaApiBundle\Entity\ApiUser;
use SuplaApiBundle\Entity\Client;
use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\IntegrationTestCase;

class MigratingMd5ApiUserPasswordsIntegrationTest extends IntegrationTestCase {
    /** @var \SuplaApiBundle\Entity\ApiUser */
    private $apiUser;
    /** @var Client */
    private $apiClient;

    protected function setUp() {
        $userManager = $this->container->get('user_manager');
        $user = new User();
        $user->setEmail('john@supla.org');
        $userManager->create($user);
        $apiManager = $this->container->get('api_manager');
        $this->apiUser = $apiManager->getAPIUser($user);
        $this->apiUser->setEnabled(true);
        $apiManager->setPassword('123', $this->apiUser, true);
        $this->apiClient = $apiManager->getClient($user);
    }

    public function testNewPasswordIsEncryptedWithNewEncoder() {
        $newEncoder = $this->container->get('security.encoder_factory')->getEncoder($this->apiUser);
        $this->assertTrue($newEncoder->isPasswordValid($this->apiUser->getPassword(), '123', $this->apiUser->getSalt()));
    }

    public function testMigratingMd5PasswordAfterSuccessfulLogin() {
        $this->setLegacyPasswordToApiUser('1234');
        $token = $this->authenticateApiUser($this->apiUser->getUsername(), '1234');
        $this->assertNotNull($token);
        $apiUser = $this->container->get('doctrine')->getRepository('SuplaApiBundle:ApiUser')->find($this->apiUser->getId());
        $legacyEncoder = $this->container->get('security.encoder_factory')->getEncoder('legacy_encoder');
        $newEncoder = $this->container->get('security.encoder_factory')->getEncoder($this->apiUser);
        $this->assertFalse($legacyEncoder->isPasswordValid($apiUser->getPassword(), '1234', $apiUser->getSalt()));
        $this->assertTrue($newEncoder->isPasswordValid($apiUser->getPassword(), '1234', $apiUser->getSalt()));
    }

    public function testNotMigratingMd5PasswordAfterFailedLogin() {
        $this->setLegacyPasswordToApiUser('1234');
        $token = $this->authenticateApiUser($this->apiUser->getUsername(), '12345');
        $this->assertNull($token);
        $apiUser = $this->container->get('doctrine')->getRepository('SuplaApiBundle:ApiUser')->find($this->apiUser->getId());
        $legacyEncoder = $this->container->get('security.encoder_factory')->getEncoder('legacy_encoder');
        $newEncoder = $this->container->get('security.encoder_factory')->getEncoder($this->apiUser);
        $this->assertTrue($legacyEncoder->isPasswordValid($apiUser->getPassword(), '1234', $apiUser->getSalt()));
        $this->assertFalse($newEncoder->isPasswordValid($apiUser->getPassword(), '1234', $apiUser->getSalt()));
    }

    private function authenticateApiUser(string $username, string $password) {
        $client = self::createClient();
        $client->request('POST', '/oauth/v2/token', [
            'client_id' => $this->apiClient->getPublicId(),
            'client_secret' => $this->apiClient->getSecret(),
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
        ]);
        $response = $client->getResponse();
        return $response->getStatusCode() == 200 ? json_decode($response->getContent()) : null;
    }

    private function setLegacyPasswordToApiUser(string $password) {
        $legacyEncoder = $this->container->get('security.encoder_factory')->getEncoder('legacy_encoder');
        $this->apiUser->setPassword($legacyEncoder->encodePassword($password, $this->apiUser->getSalt()));
        $this->container->get('doctrine')->getManager()->persist($this->apiUser);
        $this->container->get('doctrine')->getManager()->flush();
    }
}

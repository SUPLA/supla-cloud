<?php

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
        $this->assertCount(1, $client->getCrawler()->filter('h1:contains("Start Here")'));
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

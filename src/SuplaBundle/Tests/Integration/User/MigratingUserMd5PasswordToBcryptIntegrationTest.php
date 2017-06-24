<?php

namespace SuplaBundle\Tests\Integration\User;

use SuplaBundle\Entity\User;
use SuplaBundle\Tests\Integration\Assertions\ResponseAssertions;
use SuplaBundle\Tests\Integration\Fixtures\UserFixtures;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class MigratingUserMd5PasswordToBcryptIntegrationTest extends IntegrationTestCase {
    use ResponseAssertions;
    use UserFixtures;

    /** @var User */
    private $user;
    /** @var EncoderFactoryInterface */
    private $encoderFactory;

    protected function setUp() {
        $this->user = $this->createConfirmedUser();
        $this->encoderFactory = $this->container->get('security.encoder_factory');
        $encoderFactory = $this->encoderFactory;
        $legacyPasswordSetter = function ($password) use ($encoderFactory) {
            $this->password = null;
            $this->legacyPassword = $encoderFactory->getEncoder('legacy_encoder')->encodePassword($password, $this->getSalt());
        };
        $legacyPasswordSetter->call($this->user, 'supla123');
        $this->assertTrue($this->user->hasLegacyPassword());
        $this->container->get('doctrine.orm.entity_manager')->persist($this->user);
        $this->container->get('doctrine.orm.entity_manager')->flush();
    }

    public function testMigratePasswordOnAuthSuccess() {
        $client = $this->authenticate('supler@supla.org', 'supla123');
        $user = $this->getAuthenticatedUser($client);
        $this->assertNotNull($user);
        $this->assertEquals('supler@supla.org', $user->getEmail());
        $this->assertFalse($user->hasLegacyPassword());
        $this->assertTrue($this->encoderFactory->getEncoder(User::class)->isPasswordValid($user->getPassword(), 'supla123', $user->getSalt()));
        $this->assertFalse($this->encoderFactory->getEncoder('legacy_encoder')->isPasswordValid($user->getPassword(), 'supla123', $user->getSalt()));
    }

    public function testDoNotMigratePasswordOnAuthFailure() {
        $client = $this->authenticate('supler@supla.org', 'supla321');
        $user = $this->getAuthenticatedUser($client);
        $this->assertNull($user);
        $user = $this->container->get('doctrine')->getRepository('SuplaBundle:User')->find($this->user->getId());
        $this->assertTrue($user->hasLegacyPassword());
        $this->assertFalse($this->encoderFactory->getEncoder(User::class)->isPasswordValid($user->getPassword(), 'supla123', $user->getSalt()));
        $this->assertTrue($this->encoderFactory->getEncoder('legacy_encoder')->isPasswordValid($user->getPassword(), 'supla123', $user->getSalt()));
    }

    private function authenticate(string $username, string $password): Client {
        $client = self::createClient();
        $client->followRedirects();
        $client->request('GET', '/auth/login');
        $client->request('POST', '/auth/login_check', [
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

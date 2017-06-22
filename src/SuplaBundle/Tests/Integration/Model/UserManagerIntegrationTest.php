<?php
namespace SuplaBundle\Tests\Integration\Model;

use SuplaBundle\Entity\User;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Tests\Integration\IntegrationTestCase;

class UserManagerIntegrationTest extends IntegrationTestCase {
    /** @var UserManager */
    private $userManager;

    protected function setUp() {
        $this->userManager = $this->container->get('user_manager');
    }

    public function testCanGetUserManagerFromIoc() {
        $this->assertNotNull($this->userManager);
    }

    public function testCreatingUser() {
        $user = new User();
        $user->setEmail('test@supla.org');
        $this->userManager->create($user);
        $this->assertNotNull($user);
        $this->assertGreaterThan(0, $user->getId());
    }
}

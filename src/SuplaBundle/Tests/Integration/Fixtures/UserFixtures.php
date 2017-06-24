<?php
namespace SuplaBundle\Tests\Integration\Fixtures;

use SuplaBundle\Entity\User;

trait UserFixtures {
    protected function createConfirmedUser($email = 'supler@supla.org', $passwrod = 'supla123'): User {
        $userManager = $this->container->get('user_manager');
        $user = new User();
        $user->setEmail($email);
        $userManager->create($user);
        $userManager->setPassword($passwrod, $user, true);
        $userManager->confirm($user->getToken());
        $this->container->get('location_manager')->createLocation($user);
        return $user;
    }
}

<?php

namespace SuplaDeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use SuplaBundle\Entity\User;
use SuplaBundle\Model\UserManager;

class UsersFixture extends SuplaFixture {
    const ORDER = 0;

    const USER = 'user';

    public function load(ObjectManager $manager) {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('user_manager');
        $user = new User();
        $user->setEmail('user@supla.org');
        $userManager->create($user);
        $userManager->setPassword('pass', $user, true);
        $userManager->confirm($user->getToken());
        $this->addReference(self::USER, $user);
    }
}

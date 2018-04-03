<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null findOneByEmail(string $email)
 */
class UserRepository extends EntityRepository implements UserLoaderInterface {
    public function loadUserByUsername($username) {
        $user = $this->findOneByEmail($username);
        $limitExceeded = true; // TODO
        if ($limitExceeded) {
            if (!$user) {
                $user = new User();
                $user->setEmail($username);
            }
            $user->lock();
        }
        return $user;
    }
}

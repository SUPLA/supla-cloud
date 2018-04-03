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
        return $this->findOneByEmail($username);
    }
}

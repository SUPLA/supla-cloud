<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\User;

/**
 * @method User|null findOneByEmail(string $email)
 */
class UserRepository extends EntityRepository {
}

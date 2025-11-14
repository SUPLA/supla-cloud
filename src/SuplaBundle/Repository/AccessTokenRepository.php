<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\Main\User;

class AccessTokenRepository extends EntityRepository {
    public function findPersonalTokens(User $user) {
        return $this->findBy(['user' => $user, 'expiresAt' => null], ['id' => 'DESC']);
    }
}

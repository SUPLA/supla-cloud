<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use SuplaBundle\Entity\Main\User;

class AccessTokenRepository extends EntityRepository {
    public function findPersonalTokens(User $user) {
        $qb = $this->createQueryBuilder('t');
        $qb->where('t.user = :user')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('t.expiresAt'),
                $qb->expr()->eq('t.expiresAt', ':zero')
            ))
            ->setParameter('user', $user)
            ->setParameter('zero', 0)
            ->orderBy('t.id', 'DESC');
        return $qb->getQuery()->getResult();
    }
}

<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\User;

/**
 * @method User|null findOneByEmail(string $email)
 */
class UserRepository extends EntityWithRelationsRepository {
    protected $alias = 'u';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('u entity')
            ->addSelect('COUNT(DISTINCT cg) channelGroups')
            ->addSelect('COUNT(DISTINCT aid) accessIds')
            ->addSelect('COUNT(DISTINCT l) locations')
            ->addSelect('COUNT(DISTINCT s) schedules')
            ->addSelect('COUNT(DISTINCT dl) directLinks')
            ->addSelect('COUNT(DISTINCT ac) apiClients')
            ->from(User::class, 'u')
            ->leftJoin('u.channelGroups', 'cg')
            ->leftJoin('u.accessids', 'aid')
            ->leftJoin('u.locations', 'l')
            ->leftJoin('u.schedules', 's')
            ->leftJoin('u.directLinks', 'dl')
            ->leftJoin('u.apiClients', 'ac')
            ->groupBy('u');
    }
}

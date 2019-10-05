<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\AccessID;

class AccessIdRepository extends AbstractRepository {
    protected $alias = 'aid';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('aid entity')
            ->addSelect('COUNT(l) locations')
            ->addSelect('COUNT(ca) clientApps')
            ->from(AccessID::class, 'aid')
            ->leftJoin('aid.locations', 'l')
            ->leftJoin('aid.clientApps', 'ca')
            ->groupBy('aid');
    }
}

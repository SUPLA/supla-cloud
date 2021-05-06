<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\Main\AccessID;
use SuplaBundle\Entity\Main\ClientApp;

class AccessIdRepository extends EntityWithRelationsRepository {
    protected $alias = 'aid';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('aid entity')
            ->addSelect('COUNT(DISTINCT l) locations')
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s ca WHERE ca.accessId = aid) clientApps', ClientApp::class))
            ->from(AccessID::class, 'aid')
            ->leftJoin('aid.locations', 'l')
            ->groupBy('aid.id');
    }
}

<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\IODeviceChannelGroup;

class ChannelGroupRepository extends AbstractRepository {
    protected $alias = 'cg';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('cg entity')
            ->addSelect('COUNT(DISTINCT c) channels')
            ->addSelect('COUNT(DISTINCT dl) directLinks')
            ->addSelect('COUNT(DISTINCT s) schedules')
            ->from(IODeviceChannelGroup::class, 'cg')
            ->leftJoin('cg.channels', 'c')
            ->leftJoin('cg.directLinks', 'dl')
            ->leftJoin('cg.schedules', 's')
            ->groupBy('cg.id');
    }
}

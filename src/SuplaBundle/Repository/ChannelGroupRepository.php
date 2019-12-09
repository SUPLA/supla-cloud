<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\IODeviceChannelGroup;

class ChannelGroupRepository extends EntityWithRelationsRepository {
    protected $alias = 'cg';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('cg entity')
            ->addSelect('COUNT(DISTINCT c) channels')
            ->addSelect('COUNT(DISTINCT dl) directLinks')
            ->addSelect('COUNT(DISTINCT s) schedules')
            ->addSelect('COUNT(DISTINCT so) sceneOperations')
            ->addSelect('COUNT(DISTINCT sc) scenes')
            ->from(IODeviceChannelGroup::class, 'cg')
            ->leftJoin('cg.channels', 'c')
            ->leftJoin('cg.directLinks', 'dl')
            ->leftJoin('cg.schedules', 's')
            ->leftJoin('cg.sceneOperations', 'so')
            ->leftJoin('so.owningScene', 'sc')
            ->groupBy('cg.id');
    }
}

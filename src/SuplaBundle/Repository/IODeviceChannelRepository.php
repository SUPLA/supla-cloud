<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\IODeviceChannel;

class IODeviceChannelRepository extends EntityWithRelationsRepository {
    protected $alias = 'c';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('c entity')
            ->addSelect('COUNT(DISTINCT cg) channelGroups')
            ->addSelect('COUNT(DISTINCT dl) directLinks')
            ->addSelect('COUNT(DISTINCT s) schedules')
            ->addSelect('COUNT(DISTINCT so) sceneOperations')
            ->addSelect('COUNT(DISTINCT sc) scenes')
            ->from(IODeviceChannel::class, 'c')
            ->leftJoin('c.channelGroups', 'cg')
            ->leftJoin('c.directLinks', 'dl')
            ->leftJoin('c.schedules', 's')
            ->leftJoin('c.sceneOperations', 'so')
            ->leftJoin('so.owningScene', 'sc')
            ->groupBy('c');
    }
}

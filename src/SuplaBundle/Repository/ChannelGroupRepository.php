<?php
namespace SuplaBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Entity\Schedule;

class ChannelGroupRepository extends EntityWithRelationsRepository {
    protected $alias = 'cg';

    protected function getEntityWithRelationsCountQuery(): QueryBuilder {
        return $this->_em->createQueryBuilder()
            ->addSelect('cg entity')
            ->addSelect('COUNT(DISTINCT c) channels')
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s dl WHERE dl.channelGroup = cg) directLinks', DirectLink::class))
            ->addSelect(sprintf('(SELECT COUNT(1) FROM %s s WHERE s.channelGroup = cg) schedules', Schedule::class))
            ->from(IODeviceChannelGroup::class, 'cg')
            ->leftJoin('cg.channels', 'c')
            ->groupBy('cg.id');
    }
}
